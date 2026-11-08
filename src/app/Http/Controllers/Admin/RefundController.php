<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Exceptions\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PaystackGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Money goes back out through the same door it came in. A refund is recorded
 * before the gateway is called, so a failed call still leaves an audit trail,
 * and a repeated click cannot refund the same amount twice.
 */
class RefundController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly PaystackGateway $gateway,
    ) {}

    public function store(RefundRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        $payment = $order->payments()->findOrFail($data['payment_id']);

        if ($payment->status !== PaymentStatus::Success) {
            return back()->with('error', __('admin.refunds.not_captured'));
        }

        $amount = round((float) $data['amount'], 2);
        $refundable = $this->payments->refundable($payment);

        if ($refundable <= 0.0) {
            return back()->with('info', __('admin.refunds.nothing_left'));
        }

        if ($amount > $refundable) {
            throw ValidationException::withMessages([
                'amount' => __('admin.refunds.exceeds', ['amount' => money($refundable)]),
            ]);
        }

        // Idempotency: the same amount still waiting on the gateway is not sent twice.
        $duplicate = $payment->refunds()
            ->where('status', RefundStatus::Pending->value)
            ->where('amount', $amount)
            ->exists();

        if ($duplicate) {
            return back()->with('info', __('admin.refunds.already_pending'));
        }

        $refund = $payment->refunds()->create([
            'amount' => $amount,
            'reason' => $data['reason'] ?? null,
            'processed_by' => $request->user()->id,
        ]);

        // Cash and transfers were collected by hand, so they are paid back by hand.
        if ($payment->provider !== OrderService::PAYSTACK) {
            $refund->forceFill(['status' => RefundStatus::Success])->save();

            return $this->settle($order, $payment);
        }

        try {
            $result = $this->gateway->refund(
                (string) ($payment->provider_reference ?: $payment->reference),
                $amount,
            );
        } catch (PaymentGatewayException $exception) {
            $refund->forceFill(['status' => RefundStatus::Failed])->save();

            return back()->with('error', __('admin.refunds.gateway_failed', [
                'reason' => $exception->getMessage(),
            ]));
        }

        $refund->forceFill([
            'status' => RefundStatus::Success,
            'provider_reference' => (string) ($result['id'] ?? $result['reference'] ?? ''),
        ])->save();

        return $this->settle($order, $payment);
    }

    /**
     * Re-reads what has actually gone back and mirrors it onto the order, so the
     * payment badge never claims "paid" once part of the money has left.
     */
    private function settle(Order $order, Payment $payment): RedirectResponse
    {
        $refunded = (float) $payment->refunds()
            ->where('status', RefundStatus::Success->value)
            ->sum('amount');

        $remaining = round((float) $payment->amount - $refunded, 2);

        $status = match (true) {
            $remaining <= 0.0 => OrderPaymentStatus::Refunded,
            $refunded > 0.0 => OrderPaymentStatus::PartiallyRefunded,
            default => $order->payment_status,
        };

        DB::transaction(function () use ($order, $status) {
            $order->forceFill(['payment_status' => $status])->save();
        });

        return back()->with('success', __('admin.refunds.recorded', [
            'amount' => money($refunded),
            'number' => $order->order_number,
        ]));
    }
}
