<?php

namespace Database\Factories;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $number = sprintf(
            '%s-%d-%06d',
            config('saraki.order_number_prefix'),
            now()->year,
            ++static::$seq
        );

        return [
            'user_id' => UserFactory::new(),
            'order_number' => $number,
            'status' => OrderStatus::Pending,
            'payment_status' => OrderPaymentStatus::Unpaid,
            'payment_method' => 'paystack',
            'subtotal' => 0,
            'shipping_fee' => 0,
            'total' => 0,
            'currency' => config('saraki.currency'),
            'delivery_name' => fake()->name(),
            'delivery_phone' => fake()->numerify('+233## #### ###'),
            'delivery_email' => fake()->unique()->safeEmail(),
            'delivery_address' => fake()->streetAddress(),
            'delivery_city' => 'Accra',
            'notes' => null,
            'placed_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            if ($order->items()->exists()) {
                return;
            }

            OrderItemFactory::new()->for($order)->count(2)->create();

            $subtotal = $order->items()->sum('line_total');

            $order->forceFill([
                'subtotal' => $subtotal,
                'total' => $subtotal + $order->shipping_fee,
            ])->save();
        });
    }
}
