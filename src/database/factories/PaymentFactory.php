<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'provider' => 'paystack',
            'reference' => sprintf('SRK-PAY-%08d', ++static::$seq),
            'provider_reference' => null,
            'amount' => 0,
            'currency' => config('saraki.currency'),
            'channel' => null,
            'status' => PaymentStatus::Initialized,
            'paid_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Payment $payment) {
            $payment->amount = $payment->order->total;
        });
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Success,
            'provider_reference' => 'PS-'.substr(md5($attributes['reference']), 0, 12),
            'channel' => 'card',
            'paid_at' => now(),
        ]);
    }
}
