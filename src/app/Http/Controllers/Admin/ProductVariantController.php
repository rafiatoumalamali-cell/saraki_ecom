<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariantRequest;
use App\Http\Requests\Admin\VariantBatchUpdateRequest;
use App\Http\Requests\Admin\VariantGenerationRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function index(Product $product): View
    {
        return view('admin.catalogue.products.variants', [
            'product' => $product,
            'variants' => $product->variants()->orderBy('size')->orderBy('color')->get(),
        ]);
    }

    public function store(ProductVariantRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $size = $data['size'] ?? ProductVariant::DEFAULT;
        $color = $data['color'] ?? ProductVariant::DEFAULT;

        $this->ensureCombinationIsFree($product, $size, $color);

        $product->variants()->create([
            ...$data,
            'size' => $size,
            'color' => $color,
            'sku' => $data['sku'] ?: $this->uniqueSku($product, $size, $color),
        ]);

        return back()->with('success', __('admin.variants.created', ['label' => $this->label($size, $color)]));
    }

    /**
     * The grid posts every row in one request so a whole stock take can be saved
     * with a single submit. Rows flagged for removal are deleted in the same
     * transaction.
     */
    public function batchUpdate(VariantBatchUpdateRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $rows = $validated['variants'];
        $removed = $validated['removed'] ?? [];

        $variants = $product->variants()->whereIn('id', array_keys($rows))->get()->keyBy('id');

        abort_unless($variants->count() === count($rows), 404);

        $this->ensureCombinationsAreUnique($product, $rows);

        DB::transaction(function () use ($rows, $removed, $variants) {
            if ($removed !== []) {
                ProductVariant::query()->whereIn('id', $removed)->delete();
            }

            foreach ($rows as $id => $row) {
                $variant = $variants->get((int) $id);

                if ($variant === null || in_array($variant->id, $removed, true)) {
                    continue;
                }

                $variant->update([
                    'size' => $row['size'] ?? ProductVariant::DEFAULT,
                    'color' => $row['color'] ?? ProductVariant::DEFAULT,
                    'sku' => $row['sku'],
                    'stock_qty' => $row['stock_qty'],
                    'price_delta' => $row['price_delta'],
                    'is_active' => (bool) $row['is_active'],
                ]);
            }
        });

        return back()->with('success', __('admin.variants.updated', [
            'count' => count($rows) - count($removed),
        ]));
    }

    /**
     * Turns "S, M, L" × "Or, Ivoire" into the full matrix, skipping combinations
     * that already exist so the action can be repeated safely.
     */
    public function generate(VariantGenerationRequest $request, Product $product): RedirectResponse
    {
        $existing = $product->variants()->get()
            ->mapWithKeys(fn (ProductVariant $variant) => [$this->combinationKey($variant->size, $variant->color) => true]);

        $created = 0;

        DB::transaction(function () use ($request, $product, $existing, &$created) {
            foreach ($request->sizes() as $size) {
                foreach ($request->colors() as $color) {
                    if ($existing->has($this->combinationKey($size, $color))) {
                        continue;
                    }

                    $product->variants()->create([
                        'size' => $size,
                        'color' => $color,
                        'sku' => $this->uniqueSku($product, $size, $color),
                        'stock_qty' => $request->integer('stock_qty'),
                        'price_delta' => $request->input('price_delta'),
                        'is_active' => true,
                    ]);

                    $created++;
                }
            }
        });

        return back()->with('success', trans_choice('admin.variants.generated', $created, ['count' => $created]));
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $variant->delete();

        return back()->with('success', __('admin.variants.deleted'));
    }

    /**
     * MySQL compares the unique index case-insensitively, so the duplicate check
     * has to as well or the insert fails with a driver error instead of a message.
     */
    private function ensureCombinationIsFree(Product $product, string $size, string $color): void
    {
        $taken = $product->variants()->get()
            ->contains(fn (ProductVariant $variant) => $this->combinationKey($variant->size, $variant->color) === $this->combinationKey($size, $color));

        if ($taken) {
            throw ValidationException::withMessages([
                'size' => __('admin.variants.duplicate_combination', ['label' => $this->label($size, $color)]),
            ]);
        }
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $rows
     */
    private function ensureCombinationsAreUnique(Product $product, array $rows): void
    {
        $seen = [];

        foreach ($rows as $id => $row) {
            $key = $this->combinationKey(
                $row['size'] ?? ProductVariant::DEFAULT,
                $row['color'] ?? ProductVariant::DEFAULT,
            );

            if (isset($seen[$key]) && $seen[$key] !== (int) $id) {
                throw ValidationException::withMessages([
                    'variants' => __('admin.variants.duplicate_combination', ['label' => $this->label(
                        $row['size'] ?? ProductVariant::DEFAULT,
                        $row['color'] ?? ProductVariant::DEFAULT,
                    )]),
                ]);
            }

            $seen[$key] = (int) $id;
        }

        $outside = $product->variants()->whereNotIn('id', array_keys($rows))->get()
            ->first(fn (ProductVariant $variant) => isset($seen[$this->combinationKey($variant->size, $variant->color)]));

        if ($outside !== null) {
            throw ValidationException::withMessages([
                'variants' => __('admin.variants.duplicate_combination', ['label' => $outside->label]),
            ]);
        }
    }

    private function combinationKey(string $size, string $color): string
    {
        return Str::lower($size.'|'.$color);
    }

    private function label(string $size, string $color): string
    {
        return collect([$size, $color])
            ->reject(fn (string $part) => $part === ProductVariant::DEFAULT)
            ->implode(' / ');
    }

    private function uniqueSku(Product $product, string $size, string $color): string
    {
        $base = $product->base_sku.'-'.Str::upper(Str::slug($this->label($size, $color) ?: 'default'));

        $sku = $base;
        $suffix = 2;

        while (ProductVariant::query()->where('sku', $sku)->exists()) {
            $sku = $base.'-'.$suffix++;
        }

        return $sku;
    }
}
