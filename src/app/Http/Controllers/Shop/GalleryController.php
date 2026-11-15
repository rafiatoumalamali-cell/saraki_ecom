<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Enums\GalleryCategory;
use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request, string $locale): View
    {
        $requested = (string) $request->query('category');
        $category = $requested === '' ? null : GalleryCategory::tryFrom($requested);

        // A hand-typed category that does not exist is a dead end, not a silent
        // fallback to "everything".
        abort_if($requested !== '' && $category === null, 404);

        $items = GalleryItem::query()
            ->published()
            ->when($category !== null, fn ($query) => $query->where('category', $category))
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->get();

        // One grouped count for every filter chip instead of a query per category.
        $counts = GalleryItem::query()
            ->published()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('shop.gallery', [
            'items' => $items,
            'category' => $category,
            'categories' => collect(GalleryCategory::cases())
                ->map(fn (GalleryCategory $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'count' => (int) ($counts[$case->value] ?? 0),
                ])
                ->all(),
            'seo' => [
                'title' => $category?->label() ?? __('shop.gallery.title'),
                'description' => __('shop.gallery.description'),
                'canonical' => $this->url($locale, $category),
                'image' => $items->first()?->url(),
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => $this->url($target, $category)])
                    ->all(),
            ],
        ]);
    }

    private function url(string $locale, ?GalleryCategory $category): string
    {
        return $category === null
            ? route('gallery.index', $locale)
            : route('gallery.index', ['locale' => $locale, 'category' => $category->value]);
    }
}
