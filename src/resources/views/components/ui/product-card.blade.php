@props(['product'])

@php
    $url = $product->url();
    $name = $product->translate('name');
    $price = $product->priceFrom();
    $variable = $product->hasVariablePricing();
    $soldOut = $product->isSoldOut();

    $images = $product->relationLoaded('images') ? $product->images : $product->images()->get();
    $slides = $images->map(fn ($image) => $image->thumbUrl())->filter()->values();
    $slideCount = $slides->count();
@endphp

{{-- The title link stretches over the whole card, so there is one tab stop per piece.
     Hovering the card cycles through every photo like the big marketplaces do. --}}
<article
    {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-md border border-line bg-canvas shadow-card transition-all duration-300 ease-brand hover:-translate-y-1 hover:border-line-strong hover:shadow-lift']) }}
    x-data="{ photo: 0, timer: null, slides: {{ $slideCount }} }"
    @mouseenter="slides > 1 && (timer = setInterval(() => { photo = (photo + 1) % slides }, 1400))"
    @mouseleave="clearInterval(timer); photo = 0"
>
    <div class="relative overflow-hidden bg-beige-100 p-3 sm:p-4">
        @if ($slideCount > 1)
            <div class="relative">
                @foreach ($slides as $index => $src)
                    <img
                        src="{{ $src }}"
                        alt="{{ $index === 0 ? $name : '' }}"
                        loading="lazy"
                        decoding="async"
                        x-show="photo === {{ $index }}"
                        @if ($index > 0) x-cloak @endif
                        class="animate-fade-in aspect-[3/4] w-full rounded-md object-cover transition-transform duration-700 ease-brand group-hover:scale-[1.05] {{ $soldOut ? 'opacity-70 saturate-50' : '' }}"
                    >
                @endforeach
            </div>
        @else
            <x-ui.product-media
                :product="$product"
                decorative
                class="aspect-[3/4] w-full rounded-md transition-transform duration-700 ease-brand group-hover:scale-[1.04] {{ $soldOut ? 'opacity-70 saturate-50' : '' }}"
            />
        @endif

        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-charcoal/25 to-transparent opacity-0 transition-opacity duration-300 ease-brand group-hover:opacity-100"></div>

        @if ($product->is_featured || $soldOut || $product->isMadeToMeasure())
            <div class="absolute start-3 top-3 flex flex-col items-start gap-2">
                @if ($product->isMadeToMeasure())
                    <x-ui.badge tone="gold">{{ __('shop.badge.made_to_measure') }}</x-ui.badge>
                @endif

                @if ($product->is_featured)
                    <x-ui.badge tone="gold">{{ __('shop.badge.featured') }}</x-ui.badge>
                @endif

                @if ($soldOut)
                    <x-ui.badge tone="neutral">{{ __('shop.badge.sold_out') }}</x-ui.badge>
                @endif
            </div>
        @endif

        <span aria-hidden="true" class="absolute bottom-3 end-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-canvas text-charcoal opacity-0 shadow-md transition-all duration-300 ease-brand group-hover:translate-y-0 group-hover:opacity-100">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </span>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <p class="label-upper text-ink-muted">{{ $product->category?->translate('name') }}</p>

        <h3 class="mt-2 font-display text-heading-sm leading-snug">
            <a
                href="{{ $url }}"
                class="text-ink transition-colors duration-200 ease-brand after:absolute after:inset-0 after:content-[''] group-hover:text-gold-700"
            >{{ $name }}</a>
        </h3>

        <p class="mt-3 text-price tabular font-semibold text-ink">
            @if ($variable)
                {{ __('shop.card.from', ['price' => money($price)]) }}
            @else
                {{ money($price) }}
            @endif
        </p>
    </div>
</article>
