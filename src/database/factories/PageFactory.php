<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $n = ++static::$seq;
        $title = 'Page '.$n;

        return [
            'slug' => Str::slug($title).'-'.$n,
            'title_fr' => $title,
            'title_en' => 'Page '.$n,
            'body_fr' => fake()->paragraphs(3, true),
            'body_en' => fake()->paragraphs(3, true),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}
