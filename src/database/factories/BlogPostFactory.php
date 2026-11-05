<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $n = ++static::$seq;
        $title = 'Article '.$n;

        return [
            'author_id' => UserFactory::new(),
            'title_fr' => $title,
            'title_en' => 'Article '.$n,
            'slug' => Str::slug($title).'-'.$n,
            'excerpt_fr' => fake()->sentence(12),
            'excerpt_en' => fake()->sentence(12),
            'body_fr' => fake()->paragraphs(3, true),
            'body_en' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'status' => PostStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);
    }
}
