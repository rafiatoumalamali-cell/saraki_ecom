<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PaymentGatewayException;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin, testable wrapper around the Paystack REST API. Amounts cross this boundary
 * in minor units (pesewas for GHS) and everything else stays in cedis, so the
 * gateway detail never leaks into the rest of the shop.
 */
class PaystackGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.paystack.secret_key'));
    }

    /**
     * Opens a hosted checkout for a payment attempt.
     *
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initialize(Payment $payment, string $email, ?string $callbackUrl = null): array
    {
        $payload = array_filter([
            'email' => $email,
            'amount' => $this->minorUnits((float) $payment->amount),
            'currency' => $payment->currency ?: config('saraki.currency'),
            'reference' => $payment->reference,
            'callback_url' => $callbackUrl,
            'channels' => array_values(config('services.paystack.channels') ?? []),
            'metadata' => [
                'order_number' => $payment->order?->order_number,
                'payment_reference' => $payment->reference,
            ],
        ], fn ($value) => $value !== null && $value !== []);

        $data = $this->send('post', '/transaction/initialize', $payload);

        if (! filled($data['authorization_url'] ?? null) || ! filled($data['reference'] ?? null)) {
            throw new PaymentGatewayException(__('shop.checkout.gateway_unavailable'));
        }

        return [
            'authorization_url' => (string) $data['authorization_url'],
            'access_code' => (string) ($data['access_code'] ?? ''),
            'reference' => (string) $data['reference'],
        ];
    }

    /**
     * Reads the transaction back from Paystack. A missing or unreadable transaction
     * returns null rather than throwing: the caller treats it as "not paid yet".
     *
     * @return array<string, mixed>|null
     */
    public function verify(string $reference): ?array
    {
        try {
            $data = $this->send('get', '/transaction/verify/'.rawurlencode($reference));
        } catch (PaymentGatewayException $exception) {
            Log::warning('Paystack verification failed', [
                'reference' => $reference,
                'reason' => $exception->getMessage(),
            ]);

            return null;
        }

        return is_array($data) ? $data : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function refund(string $transactionReference, ?float $amount = null): array
    {
        $payload = array_filter([
            'transaction' => $transactionReference,
            'amount' => $amount === null ? null : $this->minorUnits($amount),
        ], fn ($value) => $value !== null);

        return $this->send('post', '/refund', $payload);
    }

    /**
     * Paystack signs the raw body with HMAC SHA512 using the secret key.
     */
    public function hasValidSignature(string $payload, ?string $signature): bool
    {
        $secret = (string) config('services.paystack.secret_key');

        if ($secret === '' || ! filled($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $secret), (string) $signature);
    }

    public function minorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws PaymentGatewayException
     */
    private function send(string $method, string $path, array $payload = []): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException(__('shop.checkout.gateway_unavailable'));
        }

        try {
            $response = Http::withToken((string) config('services.paystack.secret_key'))
                ->acceptJson()
                ->timeout(20)
                ->baseUrl(rtrim((string) config('services.paystack.base_url'), '/'))
                ->{$method}($path, $payload);
        } catch (ConnectionException $exception) {
            Log::error('Paystack unreachable', ['path' => $path, 'reason' => $exception->getMessage()]);

            throw new PaymentGatewayException(__('shop.checkout.gateway_unavailable'));
        }

        return $this->unwrap($response);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PaymentGatewayException
     */
    private function unwrap(Response $response): array
    {
        $body = $response->json();

        if (! $response->successful() || ! is_array($body) || ($body['status'] ?? false) !== true) {
            $message = is_array($body) ? (string) ($body['message'] ?? '') : '';

            // The gateway speaks English; the customer is shown a translated line and
            // the operator keeps the original in the log.
            Log::warning('Paystack refused a request', [
                'status' => $response->status(),
                'message' => $message,
            ]);

            throw new PaymentGatewayException(__('shop.checkout.gateway_unavailable'));
        }

        return is_array($body['data'] ?? null) ? $body['data'] : $body;
    }
}
