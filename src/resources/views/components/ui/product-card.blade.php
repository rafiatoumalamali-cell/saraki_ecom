@props(['product'])

@php
    $url = $product->url();
    $name = $product->translate('name');
    $price = $product->priceFrom();
    $variable = $product->hasVariablePricing();
    $soldOut = $product->isSoldOut();
@endphp

{{-- The title link stretches over the whole card, so there is one tab stop per piece. --}}
<article class="group relative flex flex-col">
    <div class="relative overflow-hidden rounded-sm bg-beige-50">
        <x-ui.product-media :product="$product" class="aspect-[3/4] w-full transition-transform duration-500 ease-brand group-hover:scale-[1.02]" />

        @if ($product->is_featured || $soldOut)
            <div class="absolute start-3 top-3 flex flex-col items-start gap-2">
                @if ($product->is_featured)
                    <x-ui.badge tone="outline">{{ __('shop.badge.featured') }}</x-ui.badge>
                @endif

                @if ($soldOut)
                    <x-ui.badge tone="neutral">{{ __('shop.badge.sold_out') }}</x-ui.badge>
                @endif
            </div>
        @endif
    </div>

    <div class="mt-4 flex flex-1 flex-col">
        <p class="label-upper text-ink-muted">{{ $product->category?->translate('name') }}</p>

        <h3 class="mt-2 font-display text-heading-sm">
            <a
                href="{{ $url }}"
                class="text-ink transition-colors duration-150 ease-brand after:absolute after:inset-0 after:content-[''] hover:text-gold-700"
            >{{ $name }}</a>
        </h3>

        <p class="mt-2 text-price tabular text-ink">
            @if ($variable)
                {{ __('shop.card.from', ['price' => money($price)]) }}
            @else
                {{ money($price) }}
            @endif
        </p>
    </div>
</article>
