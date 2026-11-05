<?php

namespace Database\Factories;

use App\Models\BlockedDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDate>
 */
class BlockedDateFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        return [
            'blocked_date' => now()->addDays(++static::$seq)->toDateString(),
            'reason' => fake()->sentence(4),
        ];
    }
}
