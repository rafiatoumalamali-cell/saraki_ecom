<?php

namespace Database\Factories;

use App\Enums\ContactStatus;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+233## #### ###'),
            'subject' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'status' => ContactStatus::New,
        ];
    }
}
