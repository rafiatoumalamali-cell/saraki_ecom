<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * The catalogue is small enough to render on demand, but the sitemap is
     * crawled far more often than it changes, so a short cache absorbs bot
     * traffic without serving stale URLs for long.
     */
    private const CACHE_KEY = 'sitemap.xml';

    private const CACHE_TTL = 3600;

    public function __invoke(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => view('sitemap', [
            'locales' => config('saraki.supported_locales'),
            'entries' => $this->entries(),
        ])->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * One entry per public URL, each carrying every locale alternate so crawlers
     * can consolidate the fr/en pairs instead of treating them as duplicates.
     *
     * @return array<int, array{loc: string, alternates: array<string, string>, lastmod: ?string}>
     */
    private function entries(): array
    {
        $locales = config('saraki.supported_locales');
        $entries = [];

        $add = function (callable $urlFor, ?string $lastmod = null) use (&$entries, $locales): void {
            $entries[] = [
                'loc' => $urlFor($locales[0]),
                'alternates' => collect($locales)
                    ->mapWithKeys(fn (string $locale) => [$locale => $urlFor($locale)])
                    ->all(),
                'lastmod' => $lastmod,
            ];
        };

        // Static storefront pages.
        foreach (['home', 'shop.index', 'gallery.index', 'journal.index', 'appointments.index', 'faq.index', 'contact.show'] as $name) {
            $add(fn (string $locale) => route($name, $locale));
        }

        // Collection landing pages — the category chip is a query parameter on
        // the catalogue route, so the canonical lives there too.
        Category::query()->active()->get(['slug'])->each(function (Category $category) use ($add): void {
            $add(
                fn (string $locale) => route('shop.index', ['locale' => $locale, 'category' => $category->slug]),
            );
        });

        Product::query()->active()->get(['slug', 'updated_at'])->each(function (Product $product) use ($add): void {
            $add(
                fn (string $locale) => route('shop.show', ['locale' => $locale, 'product' => $product->slug]),
                $product->updated_at?->toDateString(),
            );
        });

        BlogPost::query()->published()->get(['slug', 'updated_at'])->each(function (BlogPost $post) use ($add): void {
            $add(
                fn (string $locale) => route('journal.show', ['locale' => $locale, 'post' => $post->slug]),
                $post->updated_at?->toDateString(),
            );
        });

        Page::query()->published()->get(['slug', 'updated_at'])->each(function (Page $page) use ($add): void {
            $add(
                fn (string $locale) => route('pages.show', ['locale' => $locale, 'page' => $page->slug]),
                $page->updated_at?->toDateString(),
            );
        });

        return $entries;
    }
}
