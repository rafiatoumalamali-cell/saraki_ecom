@php
    $locale = app()->getLocale();
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots'] ?? null"
        :alternates="$seo['alternates']"
    />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home', $locale) }}" class="link">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><span aria-current="page">{{ __('shop.wishlist.title') }}</span></li>
        </ol>
    </nav>

    <header class="mt-8 max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.wishlist.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md md:text-display-lg">{{ __('shop.wishlist.title') }}</h1>
        <p class="mt-4 text-body-md text-ink-soft">{{ __('shop.wishlist.description') }}</p>
    </header>

    <p class="label-upper mt-8 text-ink-muted" role="status">
        {{ trans_choice('shop.wishlist.count', $products->count()) }}
    </p>

    @if ($products->isEmpty())
        <div class="mt-6 rounded-sm border border-line bg-canvas p-10 text-center">
            <p class="font-display text-heading-sm">{{ __('shop.wishlist.empty') }}</p>
            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.wishlist.empty_hint') }}</p>

            <x-ui.button :href="route('shop.index', $locale)" variant="secondary" size="compact" class="mt-6">
                {{ __('shop.wishlist.empty_cta') }}
            </x-ui.button>
        </div>
    @else
        <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <li class="flex flex-col rounded-sm border border-line bg-canvas">
                    <a href="{{ $product->url($locale) }}" aria-label="{{ $product->translate('name') }}" class="block overflow-hidden rounded-t-sm bg-beige-50">
                        <x-ui.product-media :product="$product" decorative class="aspect-[3/4] w-full" />
                    </a>

                    <div class="flex flex-1 flex-col p-4">
                        <p class="label-upper text-ink-muted">{{ $product->category?->translate('name') }}</p>

                        <h2 class="mt-1 font-display text-heading-sm">
                            <a href="{{ $product->url($locale) }}" class="link">{{ $product->translate('name') }}</a>
                        </h2>

                        <p class="mt-2 text-price tabular text-ink">
                            @if ($product->hasVariablePricing())
                                {{ __('shop.card.from', ['price' => money($product->priceFrom())]) }}
                            @else
                                {{ money($product->priceFrom()) }}
                            @endif
                        </p>

                        <form method="POST" action="{{ route('wishlist.destroy', ['locale' => $locale, 'product' => $product]) }}" class="mt-auto pt-4">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="label-upper text-danger underline decoration-gold underline-offset-4 hover:text-ink">
                                {{ __('shop.wishlist.remove') }}
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-app-layout>
