<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();

        $featured = $this->showcase(fn ($query) => $query->featured());

        if ($featured->isEmpty()) {
            // A brand-new store has no featured flags yet, so the hero shelf falls
            // back to the newest pieces instead of rendering empty.
            $featured = $this->showcase();
            $latest = collect();
        } else {
            $latest = $this->showcase(except: $featured->pluck('id')->all());
        }

        return view('home', [
            'featured' => $featured,
            'latest' => $latest,
            'collections' => Category::query()
                ->active()
                ->withCount(['products' => fn ($query) => $query->active()])
                ->ordered()
                ->limit(6)
                ->get(),
            'seo' => [
                'title' => null,
                'description' => __('shop.home.description'),
                'canonical' => route('home', $locale),
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => route('home', $target)])
                    ->all(),
            ],
        ]);
    }

    /**
     * @param  (callable(Builder): void)|null  $scope
     * @param  array<int, int>  $except
     * @return Collection<int, Product>
     */
    private function showcase(?callable $scope = null, array $except = []): Collection
    {
        return Product::query()
            ->active()
            ->when($scope !== null, fn ($query) => $scope($query))
            ->when($except !== [], fn ($query) => $query->whereKeyNot($except))
            ->with(['images', 'variants', 'category'])
            ->latest()
            ->limit(4)
            ->get();
    }
}
