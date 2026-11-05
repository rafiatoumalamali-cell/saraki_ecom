<?php

namespace Database\Factories;

use App\Enums\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $n = ++static::$seq;

        return [
            'title_fr' => 'Pièce '.$n,
            'title_en' => 'Piece '.$n,
            'category' => GalleryCategory::Collection,
            'image_path' => 'gallery/piece-'.$n.'.jpg',
            'sort_order' => $n,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}
