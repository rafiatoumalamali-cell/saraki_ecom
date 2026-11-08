<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\ShippingZones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly ShippingZones $zones,
    ) {}

    public function index(): View
    {
        $cart = $this->carts->current();

        return view('shop.cart', [
            'cart' => $cart,
            'items' => $cart?->items()->with('variant.product')->get() ?? collect(),
            'totals' => $this->carts->totals($cart),
            'soldOut' => $this->carts->soldOutLines($cart),
            'zones' => $this->zones->all(),
            'seo' => [
                'title' => __('ui.cart'),
                'canonical' => route('cart.index', app()->getLocale()),
                'robots' => 'noindex,follow',
                'alternates' => [],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $variant = ProductVariant::query()->with('product')->findOrFail($data['variant_id']);

        $item = $this->carts->add($variant, (int) ($data['quantity'] ?? 1));

        return back()->with('success', $item->quantity > 1
            ? __('shop.cart.added_many', ['label' => $this->label($item), 'count' => $item->quantity])
            : __('shop.cart.added', ['label' => $this->label($item)]));
    }

    public function update(Request $request, string $locale, CartItem $item): RedirectResponse
    {
        $this->guardOwnership($item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->carts->updateQuantity($item, (int) $data['quantity']);

        return redirect()
            ->route('cart.index', $locale)
            ->with('success', __('shop.cart.updated', ['label' => $this->label($item)]));
    }

    public function destroy(string $locale, CartItem $item): RedirectResponse
    {
        $this->guardOwnership($item);

        $label = $this->label($item);
        $this->carts->remove($item);

        return redirect()
            ->route('cart.index', $locale)
            ->with('success', __('shop.cart.removed', ['label' => $label]));
    }

    /**
     * A line is only editable by the basket it belongs to.
     */
    private function guardOwnership(CartItem $item): void
    {
        $cart = $this->carts->current();

        abort_unless($cart !== null && $item->cart_id === $cart->id, 404);
    }

    private function label(CartItem $item): string
    {
        $product = $item->variant?->product;
        $name = $product?->translate('name') ?? '';
        $variant = $item->variant?->label;

        return filled($variant) ? $name.' — '.$variant : $name;
    }
}
