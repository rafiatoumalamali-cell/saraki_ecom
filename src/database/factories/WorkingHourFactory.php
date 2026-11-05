<?php

namespace Database\Factories;

use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkingHour>
 */
class WorkingHourFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        return [
            'day_of_week' => ++static::$seq % 7,
            'open_time' => '09:00:00',
            'close_time' => '18:00:00',
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'open_time' => null,
            'close_time' => null,
            'is_closed' => true,
        ]);
    }
}
