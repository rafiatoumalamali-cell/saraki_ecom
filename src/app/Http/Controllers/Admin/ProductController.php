<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = (string) $request->query('status');
        $categoryId = $request->integer('category') ?: null;

        return view('admin.catalogue.products.index', [
            'products' => Product::query()
                ->with(['category', 'images', 'variants'])
                ->withCount('variants')
                ->keyword($search)
                ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
                ->when($status === 'active', fn ($query) => $query->where('is_active', true))
                ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
                ->orderByDesc('updated_at')
                ->paginate(20)
                ->withQueryString(),
            'categories' => Category::query()->ordered()->get(),
            'search' => $search,
            'status' => $status,
            'categoryId' => $categoryId,
        ]);
    }

    public function create(): View
    {
        return view('admin.catalogue.products.form', [
            'product' => new Product(['is_active' => true, 'is_featured' => false]),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::create([
            ...$data,
            'slug' => Slug::unique($data['slug'] ?? $data['name_fr'], 'products'),
        ]);

        return redirect()
            ->route('admin.catalogue.products.edit', $product)
            ->with('success', __('admin.products.created', ['name' => $product->name_fr]));
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'images', 'variants']);

        return view('admin.catalogue.products.form', [
            'product' => $product,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $product->update([
            ...$data,
            'slug' => Slug::unique($data['slug'] ?? $product->slug, 'products', $product->id),
        ]);

        return redirect()
            ->route('admin.catalogue.products.edit', $product)
            ->with('success', __('admin.products.updated', ['name' => $product->name_fr]));
    }

    /**
     * Images and variants cascade with the product row; the files on disk do not,
     * so they are removed here to keep the storage disk clean.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name_fr;

        DB::transaction(function () use ($product) {
            $product->images->each->deleteFiles();

            $product->delete();
        });

        return redirect()
            ->route('admin.catalogue.products.index')
            ->with('success', __('admin.products.deleted', ['name' => $name]));
    }
}
