<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Exceptions\PaymentGatewayException;
use App\Mail\PaymentFailed;
use App\Mail\PaymentReceived;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Owns the money. Every path that can mark an order paid — the callback, the
 * webhook, an admin's manual reconciliation — comes through here so the amount,
 * the currency and the reference are checked the same way every time.
 */
class PaymentService
{
    public function __construct(
        private readonly PaystackGateway $gateway,
        private readonly OrderService $orders,
    ) {}

    /**
     * Opens a hosted checkout for the next attempt at paying an order.
     *
     * @return array{payment: Payment, url: string}
     *
     * @throws PaymentGatewayException
     */
    public function start(Order $order, string $email, ?string $callbackUrl = null): array
    {
        $payment = $order->payments()
            ->where('status', PaymentStatus::Initialized->value)
            ->whereNull('provider_reference')
            ->latest('id')
            ->first()
            ?? $this->orders->createPaymentAttempt($order);

        try {
            $result = $this->gateway->initialize($payment, $email, $callbackUrl);

            $payment->update([
                'provider_reference' => $result['reference'],
                'status' => PaymentStatus::Pending,
                'gateway_response' => ['initialize' => $result],
            ]);
        } catch (PaymentGatewayException $exception) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'gateway_response' => ['initialize_error' => $exception->getMessage()],
            ]);

            throw $exception;
        }

        return ['payment' => $payment->refresh(), 'url' => $result['authorization_url']];
    }

    /**
     * The browser coming back from Paystack. The query string is never trusted;
     * the transaction is read back from the gateway first.
     */
    public function completeFromCallback(string $reference): ?Payment
    {
        $payment = Payment::query()->where('reference', $reference)->first();

        if ($payment === null) {
            return null;
        }

        $transaction = $this->gateway->verify($reference);

        if ($transaction === null) {
            return $payment;
        }

        $this->apply($payment, $transaction);

        return $payment->refresh();
    }

    /**
     * A signed webhook. Always returns a status code: 200 means "recorded, stop
     * retrying", which is what keeps Paystack from replaying a valid event.
     */
    public function handleWebhook(string $rawBody, ?string $signature): int
    {
        if (! $this->gateway->hasValidSignature($rawBody, $signature)) {
            Log::warning('Rejected a Paystack webhook with an invalid signature');

            return 400;
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            return 400;
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $eventType = (string) ($payload['event'] ?? 'unknown');
        $reference = (string) ($data['reference'] ?? '');
        $payment = $reference === '' ? null : Payment::query()->where('reference', $reference)->first();

        $event = $this->recordEvent($payload, $payment, valid: true);

        if ($event === null) {
            // Same event id twice: already processed, acknowledged without repeating
            // the work (and without sending the receipt twice).
            return 200;
        }

        if ($payment === null) {
            Log::warning('Paystack event for an unknown reference', ['reference' => $reference, 'event' => $eventType]);

            $event->update(['processed_at' => now()]);

            return 200;
        }

        if (in_array($eventType, ['charge.success', 'charge.failed'], true)) {
            // Confirm what the webhook claims against the gateway itself; ERD §
            // "Payments" step 3 requires the server-side check before marking paid.
            $transaction = $this->gateway->verify($reference) ?? $data;

            $this->apply($payment, $transaction);
        }

        $event->update(['processed_at' => now()]);

        return 200;
    }

    /**
     * Compares a gateway transaction with our own record and, when it all lines up,
     * marks the payment successful exactly once.
     *
     * @param  array<string, mixed>  $transaction
     */
    public function apply(Payment $payment, array $transaction): bool
    {
        $status = (string) ($transaction['status'] ?? '');

        if ($status !== 'success') {
            if (in_array($status, ['failed', 'abandoned', 'reversed'], true)) {
                $this->markFailed($payment, $transaction);
            }

            return false;
        }

        if (! $this->matchesPayment($payment, $transaction)) {
            return false;
        }

        if ($payment->status === PaymentStatus::Success) {
            return true;
        }

        $confirmed = DB::transaction(function () use ($payment, $transaction) {
            /** @var Payment $locked */
            $locked = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === PaymentStatus::Success) {
                return false;
            }

            $alreadyPaid = Payment::query()
                ->where('order_id', $locked->order_id)
                ->where('status', PaymentStatus::Success->value)
                ->whereKeyNot($locked->id)
                ->exists();

            if ($alreadyPaid) {
                $locked->update([
                    'status' => PaymentStatus::Failed,
                    'gateway_response' => $transaction,
                ]);

                Log::warning('A second successful payment was refused for an order', [
                    'order_id' => $locked->order_id,
                    'reference' => $locked->reference,
                ]);

                return false;
            }

            $locked->update([
                'status' => PaymentStatus::Success,
                'provider_reference' => (string) ($transaction['id'] ?? $transaction['reference'] ?? $locked->provider_reference),
                'channel' => $this->channelOf($transaction, $locked),
                'paid_at' => now(),
                'gateway_response' => $transaction,
            ]);

            $order = $locked->order;

            $order->forceFill(['payment_status' => OrderPaymentStatus::Paid])->save();

            // ERD: an online order becomes confirmed once the payment is verified.
            if ($order->status === OrderStatus::Pending) {
                $this->orders->recordStatus($order, OrderStatus::Confirmed, null, __('shop.orders.note_paid'));
            }

            return true;
        });

        if ($confirmed) {
            $this->sendReceipt($payment->refresh());
        }

        return $confirmed;
    }

    /**
     * Cash on delivery and bank transfer are confirmed by an admin from the order
     * screen; the payment is recorded as an offline success so the books balance.
     */
    public function reconcileOffline(Order $order, User $admin, string $channel = 'transfer'): Payment
    {
        return DB::transaction(function () use ($order, $admin, $channel) {
            $payment = $order->payments()->create([
                'provider' => 'offline',
                'reference' => $order->order_number.'-OFFLINE-'.Str::upper(Str::random(6)),
                'provider_reference' => $admin->id.'-'.$order->order_number,
                'amount' => $order->total,
                'currency' => $order->currency ?: config('saraki.currency'),
                'channel' => $channel,
                'status' => PaymentStatus::Success,
                'paid_at' => now(),
                'gateway_response' => ['reconciled_by' => $admin->id, 'channel' => $channel],
            ]);

            $order->forceFill(['payment_status' => OrderPaymentStatus::Paid])->save();

            if ($order->status === OrderStatus::Pending) {
                $this->orders->recordStatus($order, OrderStatus::Confirmed, $admin, __('admin.orders.note_reconciled'));
            }

            return $payment;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordEvent(array $payload, ?Payment $payment, bool $valid): ?PaymentEvent
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        $eventId = (string) ($payload['event'] ?? 'unknown').':'.(string) (
            $data['id'] ?? $data['reference'] ?? Str::random(12)
        );

        try {
            return PaymentEvent::create([
                'payment_id' => $payment?->id,
                'provider' => 'paystack',
                'event_id' => $eventId,
                'event_type' => (string) ($payload['event'] ?? 'unknown'),
                'reference' => (string) ($data['reference'] ?? ''),
                'payload' => $payload,
                'signature_valid' => $valid,
            ]);
        } catch (QueryException) {
            // Unique event_id: this exact event was already stored.
            return null;
        }
    }

    /**
     * Full refunds already recorded against a payment, so the admin form can cap
     * the next one.
     */
    public function refundable(Payment $payment): float
    {
        $refunded = (float) $payment->refunds()
            ->where('status', RefundStatus::Success->value)
            ->sum('amount');

        return max(0.0, round((float) $payment->amount - $refunded, 2));
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function matchesPayment(Payment $payment, array $transaction): bool
    {
        $expectedReference = (string) ($transaction['reference'] ?? '');
        $expectedAmount = $this->gateway->minorUnits((float) $payment->amount);
        $receivedAmount = (int) ($transaction['amount'] ?? 0);
        $expectedCurrency = strtoupper((string) ($payment->currency ?: config('saraki.currency')));
        $receivedCurrency = strtoupper((string) ($transaction['currency'] ?? ''));

        if ($expectedReference !== '' && $expectedReference !== $payment->reference) {
            Log::warning('Paystack reference mismatch', ['expected' => $payment->reference, 'got' => $expectedReference]);

            return false;
        }

        if ($receivedAmount !== $expectedAmount) {
            Log::warning('Paystack amount mismatch', [
                'reference' => $payment->reference,
                'expected' => $expectedAmount,
                'got' => $receivedAmount,
            ]);

            return false;
        }

        if ($receivedCurrency !== '' && $receivedCurrency !== $expectedCurrency) {
            Log::warning('Paystack currency mismatch', [
                'reference' => $payment->reference,
                'expected' => $expectedCurrency,
                'got' => $receivedCurrency,
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function markFailed(Payment $payment, array $transaction): void
    {
        if ($payment->status === PaymentStatus::Success) {
            // A late failure for money we already banked is not ours to undo here;
            // a refund is the documented path.
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'gateway_response' => $transaction,
        ]);

        $order = $payment->order;

        if ($order->payment_status === OrderPaymentStatus::Pending) {
            $order->forceFill(['payment_status' => OrderPaymentStatus::Failed])->save();
        }

        $this->sendFailureNotice($order);
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function channelOf(array $transaction, Payment $payment): ?string
    {
        $channel = $transaction['channel'] ?? ($transaction['authorization']['channel'] ?? null);

        return is_string($channel) && in_array($channel, ['card', 'mobile_money', 'bank_transfer', 'ussd', 'cash', 'transfer'], true)
            ? $channel
            : ($payment->channel ?? 'card');
    }

    private function sendReceipt(Payment $payment): void
    {
        $order = $payment->order;

        try {
            Mail::to($order->delivery_email ?: $order->user->email)
                ->locale($order->user->locale ?? config('app.locale'))
                ->send(new PaymentReceived($order, $payment));
        } catch (Throwable $exception) {
            Log::error('Payment receipt email failed', [
                'order' => $order->order_number,
                'reason' => $exception->getMessage(),
            ]);
        }
    }

    private function sendFailureNotice(Order $order): void
    {
        try {
            Mail::to($order->delivery_email ?: $order->user->email)
                ->locale($order->user->locale ?? config('app.locale'))
                ->send(new PaymentFailed($order));
        } catch (Throwable $exception) {
            Log::error('Payment failure email failed', [
                'order' => $order->order_number,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
