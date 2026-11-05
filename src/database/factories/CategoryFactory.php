<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $label = 'Collection '.(++static::$seq);

        return [
            'parent_id' => null,
            'name_fr' => $label,
            'name_en' => $label,
            'slug' => Str::slug($label).'-'.static::$seq,
            'sort_order' => static::$seq,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
