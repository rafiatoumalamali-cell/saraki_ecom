@php
    $name = $product->translate('name');
    $description = $product->translate('description');
    $images = $product->images;
    $lowStock = 3;

    // Handed to Alpine so the price, stock line and SKU follow the chosen variant
    // without another round trip.
    $variants = $product->variants->map(fn ($variant) => [
        'id' => $variant->id,
        'label' => $variant->label ?: __('shop.product.single_variant'),
        'sku' => $variant->sku,
        'inStock' => $variant->isInStock(),
        'priceLabel' => money($variant->price),
        'stockLabel' => match (true) {
            $variant->stock_qty <= 0 => __('shop.product.stock_out'),
            $variant->stock_qty <= $lowStock => __('shop.product.stock_low', ['count' => $variant->stock_qty]),
            default => __('shop.product.stock_in', ['count' => $variant->stock_qty]),
        },
    ])->values()->all();

    $basePriceLabel = money($product->base_price);
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :image="$seo['image']"
        :type="$seo['type']"
        :alternates="$seo['alternates']"
    />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach ($breadcrumbs as $crumb)
                <li>
                    @if ($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="link">{{ $crumb['label'] }}</a>
                    @else
                        <span aria-current="page">{{ $crumb['label'] }}</span>
                    @endif
                </li>
                @unless ($loop->last)
                    <li aria-hidden="true">/</li>
                @endunless
            @endforeach
        </ol>
    </nav>

    <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-16">
        {{-- Gallery --}}
        <section aria-label="{{ $name }}" x-data="{ photo: 0 }">
            <div class="overflow-hidden rounded-sm bg-beige-50">
                @forelse ($images as $index => $image)
                    <img
                        x-show="photo === {{ $index }}"
                        @if ($index > 0) x-cloak @endif
                        src="{{ $image->url() }}"
                        alt="{{ $image->altText() }}"
                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        decoding="async"
                        class="aspect-[3/4] w-full object-cover"
                    >
                @empty
                    <x-ui.product-media :product="$product" class="aspect-[3/4] w-full" loading="eager" />
                @endforelse
            </div>

            @if ($images->count() > 1)
                <ul class="mt-4 grid grid-cols-5 gap-3">
                    @foreach ($images as $index => $image)
                        <li>
                            <button
                                type="button"
                                @click="photo = {{ $index }}"
                                class="block w-full overflow-hidden rounded-xs border transition-colors duration-150 ease-brand"
                                :class="photo === {{ $index }} ? 'border-charcoal' : 'border-line hover:border-line-strong'"
                            >
                                <span class="sr-only">
                                    {{ __('shop.product.photo_label', ['current' => $index + 1, 'total' => $images->count()]) }}
                                </span>
                                <img
                                    src="{{ $image->url() }}"
                                    alt=""
                                    loading="lazy"
                                    decoding="async"
                                    class="aspect-square w-full object-cover"
                                >
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Details --}}
        <section aria-labelledby="product-heading">
            <p class="label-upper text-gold-700">
                <a href="{{ route('shop.index', ['locale' => app()->getLocale(), 'category' => $product->category->slug]) }}" class="hover:underline">
                    {{ $product->category->translate('name') }}
                </a>
            </p>

            <h1 id="product-heading" class="mt-3 font-display text-display-md">{{ $name }}</h1>

            <div class="mt-4 flex flex-wrap items-baseline gap-3">
                <p class="text-price tabular text-ink">{{ $selected ? money($selected->price) : $basePriceLabel }}</p>

                @if ($product->hasVariablePricing())
                    <p class="text-body-sm text-ink-muted">
                        {{ __('shop.product.from', ['price' => money($product->priceFrom())]) }}
                    </p>
                @endif

                @if ($product->isSoldOut())
                    <x-ui.badge tone="neutral">{{ __('shop.badge.sold_out') }}</x-ui.badge>
                @endif
            </div>

            <p class="mt-6 max-w-prose text-body-md text-ink-soft">
                {{ $product->summary(220) ?? __('shop.product.summary_fallback') }}
            </p>

            <div
                x-data="{
                    choice: {{ $selected?->id ?? 'null' }},
                    options: @js($variants),
                    get current() {
                        return this.options.find((option) => option.id === this.choice) || null;
                    },
                }"
                class="mt-8"
            >
                @if ($product->variants->isNotEmpty())
                    <fieldset>
                        <legend class="label-upper text-ink">{{ __('shop.product.choose_variant') }}</legend>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($product->variants as $variant)
                                <label
                                    class="label-upper cursor-pointer rounded-xs border px-4 py-3 transition-colors duration-150 ease-brand"
                                    :class="choice === {{ $variant->id }}
                                        ? 'border-charcoal bg-charcoal text-beige'
                                        : 'border-line text-ink hover:border-charcoal'"
                                >
                                    <input
                                        type="radio"
                                        name="variant"
                                        value="{{ $variant->id }}"
                                        class="sr-only"
                                        x-model.number="choice"
                                        @checked($selected?->id === $variant->id)
                                    >
                                    {{ $variant->label ?: __('shop.product.single_variant') }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <p
                        class="mt-4 text-body-sm"
                        :class="current && current.inStock ? 'text-success' : 'text-danger'"
                        x-text="current ? current.stockLabel : @js(__('shop.product.stock_out'))"
                    >{{ $selected?->isInStock() ? __('shop.product.stock_in', ['count' => $selected->stock_qty]) : __('shop.product.stock_out') }}</p>

                    <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-body-sm text-ink-muted">
                        <div class="flex gap-2">
                            <dt class="label-upper">{{ __('shop.product.sku') }}</dt>
                            <dd class="tabular" x-text="current ? current.sku : @js($product->base_sku)">{{ $selected?->sku ?? $product->base_sku }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-body-sm text-danger">{{ __('shop.product.stock_out') }}</p>
                    <p class="mt-4 text-body-sm text-ink-muted">
                        <span class="label-upper">{{ __('shop.product.sku') }}</span>
                        <span class="tabular ms-2">{{ $product->base_sku }}</span>
                    </p>
                @endif

                @if ($product->variants->isNotEmpty())
                    <form method="POST" action="{{ route('cart.store', app()->getLocale()) }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="variant_id" :value="choice">
                        <input type="hidden" name="quantity" value="1">

                        <x-ui.button
                            type="submit"
                            class="w-full"
                            size="checkout"
                            x-bind:disabled="! current || ! current.inStock"
                        >
                            {{ __('shop.product.add_to_cart') }}
                        </x-ui.button>
                    </form>
                @else
                    <x-ui.button class="mt-6 w-full" size="checkout" disabled>
                        {{ __('shop.product.add_to_cart') }}
                    </x-ui.button>
                @endif

                <div class="mt-3">
                    @auth
                        <form method="POST" action="{{ route('wishlist.store', app()->getLocale()) }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <x-ui.button type="submit" variant="secondary" size="compact" class="w-full">
                                {{ __('shop.wishlist.add') }}
                            </x-ui.button>
                        </form>
                    @else
                        <p class="text-body-sm text-ink-muted">
                            <a href="{{ route('login') }}" class="link">{{ __('shop.wishlist.sign_in') }}</a>
                        </p>
                    @endauth
                </div>
            </div>

            <p class="mt-6 max-w-prose border-t border-line pt-6 text-body-sm text-ink-muted">
                {{ __('shop.product.measurements_note') }}
            </p>
        </section>
    </div>

    <section class="mt-16 border-t border-line pt-10" aria-labelledby="details-heading">
        <h2 id="details-heading" class="font-display text-heading-md">{{ __('shop.product.details') }}</h2>

        <div class="mt-4 max-w-prose space-y-4 text-body-md text-ink-soft">
            @if (filled($description))
                <p>{!! nl2br(e($description)) !!}</p>
            @else
                <p class="text-ink-muted">{{ __('shop.product.no_description') }}</p>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mt-16" aria-labelledby="related-heading">
            <h2 id="related-heading" class="font-display text-heading-lg">{{ __('shop.product.related') }}</h2>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)
                    <x-ui.product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
