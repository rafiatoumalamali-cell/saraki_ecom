<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'variant_id' => ProductVariantFactory::new(),
            'unit_price' => fake()->randomFloat(2, 120, 2500),
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (OrderItem $item) {
            $variant = $item->variant;
            $product = $variant->product;

            $item->product()->associate($product);
            $item->product_name = $product->name_fr;
            $item->variant_label = $variant->label;
            $item->line_total = round($item->unit_price * $item->quantity, 2);
        });
    }
}
