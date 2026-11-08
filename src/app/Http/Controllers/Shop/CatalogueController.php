<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    private const SORTS = ['newest', 'price_asc', 'price_desc', 'name'];

    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $term = trim((string) $request->query('q'));
        $sort = in_array($request->query('sort'), self::SORTS, true) ? (string) $request->query('sort') : 'newest';

        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->ordered()
            ->get();

        $categorySlug = (string) $request->query('category');
        $category = $categorySlug === '' ? null : $categories->firstWhere('slug', $categorySlug);

        // An unknown or hidden collection is a dead end rather than a silent fallback.
        abort_if($categorySlug !== '' && $category === null, 404);

        $products = Product::query()
            ->active()
            ->with(['images', 'variants', 'category'])
            ->when($category !== null, fn ($query) => $query->where('category_id', $category->id))
            ->search($term)
            ->orderBy(...$this->sortColumn($sort, $locale))
            ->orderBy('products.id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('shop.catalogue', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'term' => $term,
            'sort' => $sort,
            'sorts' => $this->sortOptions(),
            'seo' => [
                'title' => $this->listingTitle($category, $term),
                'description' => $this->listingDescription($category, $term),
                'canonical' => $this->listingUrl($locale, $category),
                'alternates' => $this->alternates($category),
                // Search result pages are not useful in an index; collections are.
                'robots' => $term === '' ? null : 'noindex,follow',
            ],
        ]);
    }

    /**
     * The locale is declared so it lines up with the {locale} prefix of the route;
     * Laravel passes route parameters positionally.
     */
    public function show(Request $request, string $locale, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category',
            'images',
            'variants' => fn ($query) => $query->where('is_active', true)->orderBy('size')->orderBy('color'),
        ]);

        $selected = $product->variants->firstWhere('id', (int) $request->query('variant'))
            ?? $product->variants->first(fn ($variant) => $variant->isInStock())
            ?? $product->variants->first();

        return view('shop.product', [
            'product' => $product,
            'selected' => $selected,
            'related' => $this->related($product),
            'breadcrumbs' => $this->breadcrumbs($product, $locale),
            'seo' => [
                'title' => $product->translate('name'),
                'description' => $product->summary(),
                'canonical' => $product->url($locale),
                'image' => $product->imageUrl(),
                'type' => 'product',
                'alternates' => $this->productAlternates($product),
            ],
        ]);
    }

    /**
     * @return Collection<int, Product>
     */
    private function related(Product $product): Collection
    {
        return Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['images', 'variants', 'category'])
            ->latest()
            ->limit(4)
            ->get();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function sortColumn(string $sort, string $locale): array
    {
        return match ($sort) {
            'price_asc' => ['base_price', 'asc'],
            'price_desc' => ['base_price', 'desc'],
            'name' => [$locale === 'en' ? 'name_en' : 'name_fr', 'asc'],
            default => ['products.created_at', 'desc'],
        };
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return collect(self::SORTS)
            ->mapWithKeys(fn (string $key) => [$key => __('shop.sort.'.$key)])
            ->all();
    }

    private function listingUrl(string $locale, ?Category $category): string
    {
        return $category === null
            ? route('shop.index', $locale)
            : route('shop.index', ['locale' => $locale, 'category' => $category->slug]);
    }

    /**
     * @return array<string, string>
     */
    private function alternates(?Category $category): array
    {
        return collect(config('saraki.supported_locales'))
            ->mapWithKeys(fn (string $target) => [$target => $this->listingUrl($target, $category)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function productAlternates(Product $product): array
    {
        return collect(config('saraki.supported_locales'))
            ->mapWithKeys(fn (string $target) => [$target => $product->url($target)])
            ->all();
    }

    private function listingTitle(?Category $category, string $term): string
    {
        if ($term !== '') {
            return __('shop.search.title', ['term' => $term]);
        }

        return $category?->translate('name') ?? __('shop.catalogue.title');
    }

    private function listingDescription(?Category $category, string $term): string
    {
        if ($term !== '') {
            return __('shop.search.description', ['term' => $term]);
        }

        return $category === null
            ? __('shop.catalogue.description')
            : __('shop.catalogue.category_description', ['name' => $category->translate('name')]);
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    private function breadcrumbs(Product $product, string $locale): array
    {
        return [
            ['label' => __('ui.home'), 'url' => route('home', $locale)],
            ['label' => __('ui.catalogue'), 'url' => route('shop.index', $locale)],
            [
                'label' => $product->category->translate('name'),
                'url' => route('shop.index', ['locale' => $locale, 'category' => $product->category->slug]),
            ],
            ['label' => $product->translate('name'), 'url' => null],
        ];
    }
}
