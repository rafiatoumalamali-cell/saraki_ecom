<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        $label = 'Création '.(++static::$seq);

        return [
            'category_id' => CategoryFactory::new(),
            'name_fr' => $label,
            'name_en' => 'Piece '.static::$seq,
            'slug' => Str::slug($label).'-'.static::$seq,
            'description_fr' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'base_price' => fake()->randomFloat(2, 120, 2500),
            'base_sku' => 'SRK-BASE-'.static::$seq,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            if (! $product->variants()->exists()) {
                ProductVariant::factory()->for($product)->defaultVariant()->create();
            }
        });
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
