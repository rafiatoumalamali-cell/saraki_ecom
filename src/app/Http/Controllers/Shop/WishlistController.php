<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $products = $request->user()
            ->wishlist()
            ->with(['images', 'category'])
            ->orderByDesc('wishlists.created_at')
            ->get();

        return view('shop.wishlist', [
            'products' => $products,
            'seo' => [
                'title' => __('shop.wishlist.title'),
                'description' => __('shop.wishlist.description'),
                'canonical' => route('wishlist.index', $locale),
                // A private page: there is nothing for a crawler to index.
                'robots' => 'noindex,follow',
                'alternates' => [],
            ],
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
        ], [
            // A piece that left the shelf is the only reason `exists` fails here.
            'product_id.exists' => __('shop.product.unavailable'),
        ], [
            'product_id' => __('shop.wishlist.piece'),
        ]);

        $product = Product::query()->findOrFail((int) $data['product_id']);

        // Saving twice is not an error; the customer simply already wanted it.
        $added = $request->user()->wishlist()->syncWithoutDetaching([$product->id]);

        $message = $added['attached'] === []
            ? __('shop.wishlist.already_saved', ['name' => $product->translate('name')])
            : __('shop.wishlist.saved', ['name' => $product->translate('name')]);

        return back()->with('success', $message);
    }

    public function destroy(Request $request, string $locale, Product $product): RedirectResponse
    {
        $request->user()->wishlist()->detach($product->id);

        return back()->with('success', __('shop.wishlist.removed', ['name' => $product->translate('name')]));
    }
}
