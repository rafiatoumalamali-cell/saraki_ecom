<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots']"
        :alternates="$seo['alternates']"
    />

    <header class="max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.cart.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md">{{ __('shop.cart.title') }}</h1>
        <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.cart.description') }}</p>
    </header>

    @if ($items->isEmpty())
        <div class="mt-10 rounded-sm border border-line bg-canvas p-10 text-center">
            <p class="font-display text-heading-md">{{ __('shop.cart.empty') }}</p>
            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.cart.empty_hint') }}</p>

            <x-ui.button :href="route('shop.index', app()->getLocale())" class="mt-6">
                {{ __('shop.cart.empty_cta') }}
            </x-ui.button>
        </div>
    @else
        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-14">
            <section aria-labelledby="cart-lines">
                <h2 id="cart-lines" class="sr-only">{{ __('shop.cart.title') }}</h2>

                @if ($soldOut->isNotEmpty())
                    <x-ui.alert type="warning" class="mb-6">{{ __('shop.cart.fix_first') }}</x-ui.alert>
                @endif

                <ul class="divide-y divide-line border-y border-line">
                    @foreach ($items as $item)
                        @php
                            $variant = $item->variant;
                            $product = $variant?->product;
                            $name = $product?->translate('name') ?? '';
                            $unavailable = $soldOut->contains('id', $item->id);
                            $lineTotal = (float) $item->unit_price * $item->quantity;
                        @endphp

                        <li class="grid grid-cols-[88px_minmax(0,1fr)] gap-5 py-6">
                            <div class="overflow-hidden rounded-sm bg-beige-50">
                                @if ($product)
                                    <x-ui.product-media :product="$product" decorative class="aspect-[3/4] w-full" />
                                @else
                                    <span class="flex aspect-[3/4] w-full items-center justify-center bg-beige-200 text-body-sm text-ink-muted">
                                        {{ __('shop.card.no_image') }}
                                    </span>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-display text-heading-sm">
                                            @if ($product)
                                                <a href="{{ $product->url() }}" class="text-ink transition-colors duration-150 ease-brand hover:text-gold-700">
                                                    {{ $name }}
                                                </a>
                                            @else
                                                {{ $name }}
                                            @endif
                                        </h3>

                                        @if (filled($variant?->label))
                                            <p class="mt-1 text-body-sm text-ink-muted">{{ $variant->label }}</p>
                                        @endif

                                        <p class="mt-1 text-body-sm text-ink-muted">{{ money($item->unit_price) }}</p>
                                    </div>

                                    <p class="text-price tabular text-ink">{{ money($lineTotal) }}</p>
                                </div>

                                @if ($unavailable)
                                    <p class="mt-3 text-body-sm text-danger">{{ __('shop.cart.unavailable_line') }}</p>
                                @elseif ($variant && $variant->stock_qty <= 3)
                                    <p class="mt-3 text-body-sm text-ink-muted">
                                        {{ __('shop.cart.stock_note', ['count' => $variant->stock_qty]) }}
                                    </p>
                                @endif

                                <div class="mt-4 flex flex-wrap items-end gap-4">
                                    <form
                                        method="POST"
                                        action="{{ route('cart.update', ['locale' => app()->getLocale(), 'item' => $item->id]) }}"
                                        class="flex items-end gap-3"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <label for="qty-{{ $item->id }}" class="label-upper text-ink-muted">
                                                {{ __('shop.cart.quantity') }}
                                            </label>
                                            <input
                                                id="qty-{{ $item->id }}"
                                                name="quantity"
                                                type="number"
                                                min="1"
                                                max="99"
                                                value="{{ $item->quantity }}"
                                                inputmode="numeric"
                                                class="field mt-1 h-10 w-20 tabular"
                                            >
                                        </div>

                                        <x-ui.button type="submit" variant="secondary" size="compact">
                                            {{ __('shop.cart.update') }}
                                        </x-ui.button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('cart.destroy', ['locale' => app()->getLocale(), 'item' => $item->id]) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <x-ui.button type="submit" variant="link" size="link">
                                            {{ __('shop.cart.remove') }}
                                        </x-ui.button>
                                    </form>
                                </div>

                                @error('quantity')
                                    <p class="mt-3 text-body-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <aside aria-labelledby="cart-summary">
                <div class="rounded-sm border border-line bg-canvas p-6 lg:sticky lg:top-24">
                    <h2 id="cart-summary" class="font-display text-heading-sm">{{ __('shop.checkout.summary_section') }}</h2>

                    <dl class="mt-5 space-y-3 text-body-sm">
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-ink-muted">{{ __('shop.cart.subtotal') }}</dt>
                            <dd class="tabular text-ink">{{ money($totals['subtotal']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-ink-muted">{{ __('shop.cart.shipping_pending') }}</dt>
                            <dd class="text-ink-muted">{{ __('shop.cart.shipping_note') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex items-baseline justify-between gap-4 border-t border-ink pt-4">
                        <span class="label-upper">{{ __('shop.cart.total') }}</span>
                        <span class="text-price tabular text-ink">{{ money($totals['subtotal']) }}</span>
                    </div>

                    @if ($soldOut->isNotEmpty())
                        <x-ui.button class="mt-6 w-full" size="checkout" disabled>
                            {{ __('shop.cart.checkout') }}
                        </x-ui.button>
                    @else
                        <x-ui.button :href="route('checkout.show', app()->getLocale())" class="mt-6 w-full" size="checkout">
                            {{ __('shop.cart.checkout') }}
                        </x-ui.button>
                    @endif

                    <a href="{{ route('shop.index', app()->getLocale()) }}" class="link mt-4 block text-center text-body-sm">
                        {{ __('shop.cart.continue') }}
                    </a>

                    <p class="mt-4 text-body-sm text-ink-muted">{{ __('shop.payment.secure_note') }}</p>
                </div>
            </aside>
        </div>
    @endif
</x-app-layout>
