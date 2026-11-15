@php
    $name = $product->translate('name');
    $description = $product->translate('description');
    $images = $product->images;
    $lowStock = 3;
    $locale = app()->getLocale();

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

    $productSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $name,
        'description' => $product->summary(),
        'image' => $product->imageUrl(),
        'url' => $seo['canonical'],
        'sku' => $product->base_sku,
        'offers' => [
            '@type' => 'Offer',
            'url' => $seo['canonical'],
            'priceCurrency' => config('saraki.currency'),
            'price' => $product->priceFrom(),
            'availability' => 'https://schema.org/'.($product->isSoldOut() ? 'OutOfStock' : 'InStock'),
        ],
    ]);
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
    <x-ui.json-ld :data="$productSchema" />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach ($breadcrumbs as $crumb)
                <li>
                    @if ($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="transition-colors duration-200 ease-brand hover:text-gold-700">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-ink" aria-current="page">{{ $crumb['label'] }}</span>
                    @endif
                </li>
                @unless ($loop->last)
                    <li aria-hidden="true">
                        <svg class="h-3.5 w-3.5 opacity-50 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </li>
                @endunless
            @endforeach
        </ol>
    </nav>

    <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-16">
        {{-- Gallery --}}
        <section aria-label="{{ __('shop.product.gallery_label', ['name' => $name]) }}" x-data="{ photo: 0 }" class="lg:sticky lg:top-28 lg:self-start">
            <div class="overflow-hidden rounded-lg border border-line bg-beige-50 shadow-card">
                @forelse ($images as $index => $image)
                    <img
                        x-show="photo === {{ $index }}"
                        @if ($index > 0) x-cloak @endif
                        src="{{ $image->url() }}"
                        alt="{{ $image->altText() }}"
                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        decoding="async"
                        class="aspect-[3/4] w-full animate-fade-in object-cover"
                    >
                @empty
                    <x-ui.product-media :product="$product" :thumb="false" class="aspect-[3/4] w-full" loading="eager" />
                @endforelse
            </div>

            @if ($images->count() > 1)
                <ul class="mt-4 grid grid-cols-5 gap-3">
                    @foreach ($images as $index => $image)
                        <li>
                            <button
                                type="button"
                                @click="photo = {{ $index }}"
                                class="block w-full overflow-hidden rounded-sm border-2 bg-canvas transition-all duration-200 ease-brand"
                                :class="photo === {{ $index }} ? 'border-gold shadow-ring' : 'border-line opacity-70 hover:opacity-100 hover:border-line-strong'"
                            >
                                <span class="sr-only">
                                    {{ __('shop.product.photo_label', ['current' => $index + 1, 'total' => $images->count()]) }}
                                </span>
                                <img
                                    src="{{ $image->thumbUrl() }}"
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
                <a href="{{ route('shop.index', ['locale' => $locale, 'category' => $product->category->slug]) }}" class="inline-flex items-center gap-2 transition-colors duration-200 ease-brand hover:text-charcoal">
                    <span aria-hidden="true" class="h-px w-6 bg-gold/60"></span>
                    {{ $product->category->translate('name') }}
                </a>
            </p>

            <h1 id="product-heading" class="mt-4 font-display text-display-md">{{ $name }}</h1>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                @if ($product->isMadeToMeasure() && $product->hasTailoringPrices())
                    <dl class="space-y-1.5">
                        @if ($product->fabric_price !== null)
                            <div class="flex items-baseline gap-2">
                                <dt class="label-upper text-ink-muted">{{ __('shop.tailoring.price_fabric') }}</dt>
                                <dd class="tabular font-display text-heading-md text-ink">
                                    {{ money($product->fabric_price) }}@if ($product->fabric_unit)<span class="ms-1 text-body-sm font-normal text-ink-muted">{{ __('shop.tailoring.per_'.$product->fabric_unit) }}</span>@endif
                                </dd>
                            </div>
                        @endif
                        @if ($product->confection_price !== null)
                            <div class="flex items-baseline gap-2">
                                <dt class="label-upper text-ink-muted">{{ __('shop.tailoring.price_confection') }}</dt>
                                <dd class="tabular font-display text-heading-md text-ink">{{ money($product->confection_price) }}</dd>
                            </div>
                        @endif
                    </dl>
                    <p class="w-full text-body-sm text-ink-muted">{{ __('shop.tailoring.price_note') }}</p>
                @else
                    <p class="tabular font-display text-heading-lg text-ink">{{ $selected ? money($selected->price) : $basePriceLabel }}</p>

                    @if ($product->hasVariablePricing())
                        <p class="text-body-sm text-ink-muted">
                            {{ __('shop.product.from', ['price' => money($product->priceFrom())]) }}
                        </p>
                    @endif
                @endif

                @if ($product->isSoldOut() && ! $product->isMadeToMeasure())
                    <x-ui.badge tone="neutral">{{ __('shop.badge.sold_out') }}</x-ui.badge>
                @endif
            </div>

            <p class="mt-6 max-w-prose text-body-md text-ink-soft">
                {{ $product->summary(220) ?? __('shop.product.summary_fallback') }}
            </p>

            @if ($product->isMadeToMeasure())
                {{-- Sur mesure: no cart — the client sends the atelier a request --}}
                <div class="mt-8 border-t border-line pt-8">
                    <h2 class="font-display text-heading-sm">{{ __('shop.tailoring.form_title') }}</h2>
                    <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.tailoring.form_hint') }}</p>

                    <form method="POST" action="{{ route('products.tailoring', ['locale' => $locale, 'product' => $product->slug]) }}" class="mt-6 space-y-5">
                        @csrf

                        {{-- Honeypot --}}
                        <div class="sr-only" aria-hidden="true">
                            <label for="website">{{ __('shop.contact.fields.website') }}</label>
                            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <fieldset>
                            <legend class="label-upper text-ink">{{ __('shop.tailoring.fields.fabric') }}</legend>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach (['saraki', 'own'] as $choice)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line bg-canvas p-4 transition-all duration-200 ease-brand has-[:checked]:border-charcoal has-[:checked]:bg-charcoal has-[:checked]:text-beige has-[:checked]:shadow-md hover:-translate-y-0.5 hover:border-line-strong">
                                        <input type="radio" name="fabric" value="{{ $choice }}" class="mt-1 sr-only" @checked(old('fabric', 'saraki') === $choice) required>
                                        <span>
                                            <span class="block text-body-sm font-semibold">{{ __('shop.tailoring.fabric_'.$choice) }}</span>
                                            <span class="mt-1 block text-body-sm opacity-70">{{ __('shop.tailoring.fabric_'.$choice.'_hint') }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('fabric')" />
                        </fieldset>

                        <div>
                            <x-input-label for="message" :value="__('shop.tailoring.fields.message')" />
                            <textarea id="message" name="message" rows="4" class="field mt-1.5" placeholder="{{ __('shop.tailoring.fields.message_placeholder') }}">{{ old('message') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('message')" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="name" :value="__('shop.tailoring.fields.name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1.5" :value="old('name', auth()->user()?->name)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>
                            <div>
                                <x-input-label for="email" :value="__('shop.tailoring.fields.email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email', auth()->user()?->email)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="phone" :value="__('shop.tailoring.fields.phone')" />
                                <x-text-input id="phone" name="phone" type="tel" class="mt-1.5" :value="old('phone', auth()->user()?->phone)" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>
                        </div>

                        <x-ui.button type="submit" class="w-full" size="checkout">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                            {{ __('shop.tailoring.submit') }}
                        </x-ui.button>
                    </form>

                    <div class="mt-6">
                        @auth
                            <form method="POST" action="{{ route('wishlist.store', $locale) }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <x-ui.button type="submit" variant="secondary" class="w-full">
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
            @else
            <div
                x-data="{
                    choice: {{ $selected?->id ?? 'null' }},
                    options: @js($variants),
                    get current() {
                        return this.options.find((option) => option.id === this.choice) || null;
                    },
                }"
                class="mt-8 border-t border-line pt-8"
            >
                @if ($product->variants->isNotEmpty())
                    <fieldset>
                        <legend class="label-upper text-ink">{{ __('shop.product.choose_variant') }}</legend>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($product->variants as $variant)
                                <label
                                    class="label-upper cursor-pointer rounded-full border px-5 py-3 transition-all duration-200 ease-brand"
                                    :class="choice === {{ $variant->id }}
                                        ? 'border-charcoal bg-charcoal text-beige shadow-md'
                                        : 'border-line bg-canvas text-ink hover:-translate-y-0.5 hover:border-charcoal hover:shadow-sm'"
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
                        class="mt-5 flex items-center gap-2 text-body-sm font-semibold"
                        :class="current && current.inStock ? 'text-success' : 'text-danger'"
                    >
                        <span aria-hidden="true" class="h-2 w-2 shrink-0 rounded-full bg-current"></span>
                        <span x-text="current ? current.stockLabel : @js(__('shop.product.stock_out'))">{{ $selected?->isInStock() ? __('shop.product.stock_in', ['count' => $selected->stock_qty]) : __('shop.product.stock_out') }}</span>
                    </p>

                    <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-body-sm text-ink-muted">
                        <div class="flex gap-2">
                            <dt class="label-upper">{{ __('shop.product.sku') }}</dt>
                            <dd class="tabular" x-text="current ? current.sku : @js($product->base_sku)">{{ $selected?->sku ?? $product->base_sku }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="flex items-center gap-2 text-body-sm font-semibold text-danger">
                        <span aria-hidden="true" class="h-2 w-2 shrink-0 rounded-full bg-current"></span>
                        {{ __('shop.product.stock_out') }}
                    </p>
                    <p class="mt-4 text-body-sm text-ink-muted">
                        <span class="label-upper">{{ __('shop.product.sku') }}</span>
                        <span class="tabular ms-2">{{ $product->base_sku }}</span>
                    </p>
                @endif

                @if ($product->variants->isNotEmpty())
                    <form method="POST" action="{{ route('cart.store', $locale) }}" class="mt-7">
                        @csrf
                        <input type="hidden" name="variant_id" :value="choice">
                        <input type="hidden" name="quantity" value="1">

                        <x-ui.button
                            type="submit"
                            class="w-full"
                            size="checkout"
                            x-bind:disabled="! current || ! current.inStock"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                            </svg>
                            {{ __('shop.product.add_to_cart') }}
                        </x-ui.button>
                    </form>
                @else
                    <x-ui.button class="mt-7 w-full" size="checkout" disabled>
                        {{ __('shop.product.add_to_cart') }}
                    </x-ui.button>
                @endif

                <div class="mt-3">
                    @auth
                        <form method="POST" action="{{ route('wishlist.store', $locale) }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <x-ui.button type="submit" variant="secondary" class="w-full">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
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
            @endif

            <div class="mt-8 flex items-start gap-3 rounded-md border border-gold/25 bg-tint-warning p-4">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" />
                </svg>
                <p class="text-body-sm text-ink-soft">{{ __('shop.product.measurements_note') }}</p>
            </div>
        </section>
    </div>

    <section class="card mt-16 p-6 md:p-8" aria-labelledby="details-heading" data-reveal>
        <h2 id="details-heading" class="font-display text-heading-md">{{ __('shop.product.details') }}</h2>
        <span aria-hidden="true" class="mt-4 block h-px w-16 bg-gold/60"></span>

        <div class="mt-5 max-w-prose space-y-4 text-body-md text-ink-soft">
            @if (filled($description))
                <p>{!! nl2br(e($description)) !!}</p>
            @else
                <p class="text-ink-muted">{{ __('shop.product.no_description') }}</p>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mt-16" aria-labelledby="related-heading">
            <div class="flex flex-wrap items-end justify-between gap-4" data-reveal>
                <h2 id="related-heading" class="font-display text-heading-lg">{{ __('shop.product.related') }}</h2>
                <a href="{{ route('shop.index', ['locale' => $locale, 'category' => $product->category->slug]) }}" class="link text-body-sm">
                    {{ __('shop.home.view_all') }}
                </a>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $item)
                    <div data-reveal style="--reveal-delay: {{ $loop->index * 70 }}ms">
                        <x-ui.product-card :product="$item" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>