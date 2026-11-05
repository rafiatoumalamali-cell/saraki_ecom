<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    private static int $seq = 0;

    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::new(),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->randomElement(['Noir', 'Or', 'Ivoire', 'Terracotta']),
            'sku' => 'SRK-VAR-'.(++static::$seq),
            'stock_qty' => fake()->numberBetween(5, 40),
            'price_delta' => 0,
            'is_active' => true,
        ];
    }

    public function defaultVariant(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => ProductVariant::DEFAULT,
            'color' => ProductVariant::DEFAULT,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['stock_qty' => 0]);
    }
}
