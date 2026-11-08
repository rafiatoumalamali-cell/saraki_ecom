<?php

namespace Database\Factories;

use App\Enums\RefundStatus;
use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_id' => PaymentFactory::new()->succeeded(),
            'amount' => fake()->randomFloat(2, 20, 400),
            'reason' => fake()->sentence(),
            'provider_reference' => 'RF-'.fake()->unique()->numerify('########'),
            'status' => RefundStatus::Success,
            'processed_by' => UserFactory::new()->admin(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RefundStatus::Pending,
            'provider_reference' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RefundStatus::Failed,
        ]);
    }
}
