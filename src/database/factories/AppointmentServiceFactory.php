<?php

namespace Database\Factories;

use App\Models\AppointmentService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentService>
 */
class AppointmentServiceFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $label = 'Service '.(++static::$seq);

        return [
            'name_fr' => $label,
            'name_en' => 'Service '.static::$seq,
            'duration_min' => fake()->randomElement([30, 45, 60]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
