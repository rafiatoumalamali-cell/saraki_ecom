<?php

namespace Database\Factories;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cart_id' => CartFactory::new(),
            'variant_id' => ProductVariantFactory::new(),
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 120, 2500),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (CartItem $item) {
            $item->product_id = $item->variant->product_id;
        });
    }
}
