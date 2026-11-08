<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * The basket lives in the database so a guest can fill it, sign in, and still find
 * it there. Everything that touches quantity goes through here, which is the one
 * place that knows stock is the ceiling.
 */
class CartService
{
    /**
     * The cart behind the current visitor, or null when nothing was added yet.
     */
    public function current(): ?Cart
    {
        $user = auth()->user();

        if ($user !== null) {
            return Cart::query()->where('user_id', $user->id)->first();
        }

        return Cart::query()
            ->whereNull('user_id')
            ->where('session_id', session()->getId())
            ->first();
    }

    /**
     * The cart for the current visitor, created on first use.
     */
    public function resolve(): Cart
    {
        $cart = $this->current();

        if ($cart !== null) {
            return $cart;
        }

        $user = auth()->user();

        return Cart::create($user !== null
            ? ['user_id' => $user->id]
            : ['session_id' => session()->getId()]);
    }

    /**
     * @throws ValidationException when the variant cannot be sold or stock is short
     */
    public function add(ProductVariant $variant, int $quantity = 1): CartItem
    {
        $quantity = max(1, $quantity);

        if (! $variant->is_active || $variant->product?->is_active === false) {
            throw ValidationException::withMessages([
                'variant_id' => __('shop.cart.unavailable'),
            ]);
        }

        $cart = $this->resolve();

        $item = $cart->items()->firstOrNew(['variant_id' => $variant->id]);
        $wanted = (int) $item->quantity + $quantity;

        $this->guardStock($variant, $wanted);

        $item->fill([
            'product_id' => $variant->product_id,
            'quantity' => $wanted,
            // The price the visitor saw is snapshotted; checkout re-reads it anyway.
            'unit_price' => $variant->price,
        ])->save();

        return $item->refresh();
    }

    /**
     * @throws ValidationException when stock is short
     */
    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $quantity = max(1, $quantity);

        $variant = $item->variant;

        if ($variant === null) {
            throw ValidationException::withMessages([
                'quantity' => __('shop.cart.unavailable'),
            ]);
        }

        $this->guardStock($variant, $quantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $variant->price,
        ]);

        return $item->refresh();
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * What the bag costs, and how many pieces are in it.
     *
     * @return array{count: int, subtotal: float}
     */
    public function totals(?Cart $cart): array
    {
        $items = $cart?->items()->get() ?? collect();

        return [
            'count' => (int) $items->sum('quantity'),
            'subtotal' => round((float) $items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity), 2),
        ];
    }

    /**
     * Lines whose variant went inactive or out of stock while the bag was waiting,
     * so the cart page can warn before checkout refuses the order.
     *
     * @return Collection<int, CartItem>
     */
    public function soldOutLines(?Cart $cart): Collection
    {
        $items = $cart?->items()->with('variant.product')->get() ?? collect();

        return $items->filter(fn (CartItem $item) => $item->variant === null
            || ! $item->variant->is_active
            || $item->variant->product?->is_active === false
            || $item->variant->stock_qty < $item->quantity);
    }

    /**
     * Signing in keeps the bag: the guest lines move onto the account cart, adding
     * to whatever was already there.
     */
    public function mergeGuestCart(User $user): void
    {
        $guest = Cart::query()
            ->whereNull('user_id')
            ->where('session_id', session()->getId())
            ->first();

        if ($guest === null) {
            return;
        }

        $owned = Cart::query()->firstOrCreate(['user_id' => $user->id]);

        foreach ($guest->items()->with('variant')->get() as $item) {
            $variant = $item->variant;

            if ($variant === null || ! $variant->is_active) {
                continue;
            }

            $existing = $owned->items()->firstOrNew(['variant_id' => $variant->id]);
            $wanted = (int) $existing->quantity + (int) $item->quantity;

            $existing->fill([
                'product_id' => $variant->product_id,
                'quantity' => min($wanted, max(1, $variant->stock_qty)),
                'unit_price' => $variant->price,
            ])->save();
        }

        $this->clear($guest);
        $guest->delete();
    }

    private function guardStock(ProductVariant $variant, int $wanted): void
    {
        if ($variant->stock_qty < $wanted) {
            throw ValidationException::withMessages([
                'quantity' => $variant->stock_qty === 0
                    ? __('shop.cart.sold_out', ['label' => $variant->label])
                    : __('shop.cart.only_available', ['label' => $variant->label, 'count' => $variant->stock_qty]),
            ]);
        }
    }
}
