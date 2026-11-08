<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImageRequest;
use App\Http\Requests\Admin\ProductImageUpdateRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductImageController extends Controller
{
    public function index(Product $product): View
    {
        return view('admin.catalogue.products.images', [
            'product' => $product,
            'images' => $product->images()->get(),
        ]);
    }

    /**
     * Uploads are accepted one or many at a time. The first photo of a product
     * becomes its primary image, which is what the catalogue card shows.
     */
    public function store(ProductImageRequest $request, Product $product): RedirectResponse
    {
        $sortOrder = (int) $product->images()->max('sort_order');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($request->file('images', []) as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'alt_fr' => $request->input('alt_fr') ?: $product->name_fr,
                'alt_en' => $request->input('alt_en') ?: $product->name_en,
                'sort_order' => ++$sortOrder,
                'is_primary' => ! $hasPrimary,
            ]);

            $hasPrimary = true;
        }

        return back()->with('success', __('admin.images.uploaded', ['name' => $product->name_fr]));
    }

    public function update(ProductImageUpdateRequest $request, Product $product, ProductImage $image): RedirectResponse
    {
        $this->ensureBelongsTo($product, $image);

        $image->update([
            'alt_fr' => $request->input('alt_fr'),
            'alt_en' => $request->input('alt_en'),
            'sort_order' => $request->integer('sort_order', $image->sort_order),
        ]);

        return back()->with('success', __('admin.images.updated'));
    }

    /**
     * Exactly one image carries the primary flag, so it is cleared everywhere else
     * in the same transaction.
     */
    public function primary(Product $product, ProductImage $image): RedirectResponse
    {
        $this->ensureBelongsTo($product, $image);

        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);
        });

        return back()->with('success', __('admin.images.primary_set'));
    }

    public function order(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'min:0', 'max:9999'],
        ]);

        DB::transaction(function () use ($validated, $product) {
            foreach ($validated['order'] as $id => $position) {
                $product->images()->whereKey($id)->update(['sort_order' => (int) $position]);
            }
        });

        return back()->with('success', __('admin.images.reordered'));
    }

    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        $this->ensureBelongsTo($product, $image);

        $wasPrimary = $image->is_primary;

        $image->deleteFiles();
        $image->delete();

        if ($wasPrimary) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return back()->with('success', __('admin.images.deleted'));
    }

    /**
     * Nested route parameters arrive as plain ids, so the pairing is checked here
     * instead of trusting the URL.
     */
    private function ensureBelongsTo(Product $product, ProductImage $image): void
    {
        abort_unless($image->product_id === $product->id, 404);
    }
}
