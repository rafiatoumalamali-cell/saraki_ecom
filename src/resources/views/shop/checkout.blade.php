@php
    $locale = app()->getLocale();
    $defaultZone = old('shipping_zone', $zones->first()['name'] ?? '');
    $defaultMethod = old('payment_method', array_key_first($methods) ?? '');

    // The fee never leaves PHP by itself: the option label carries it, and the
    // closed-form total below is only a preview until the server recomputes it.
    $zoneOptions = $zones->map(fn (array $zone) => [
        'name' => $zone['name'],
        'label' => $zone['delivery_days'] > 0
            ? __('shop.checkout.zone_option', [
                'label' => $zone['label'],
                'fee' => money($zone['fee']),
                'days' => __('shop.checkout.zone_days', ['count' => $zone['delivery_days']]),
            ])
            : $zone['label'].' — '.($zone['fee'] > 0 ? money($zone['fee']) : __('shop.checkout.shipping_free')),
    ]);

    $fees = $zones->mapWithKeys(fn (array $zone) => [$zone['name'] => (float) $zone['fee']]);
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots']"
        :alternates="$seo['alternates']"
    />

    <header class="max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.checkout.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md">{{ __('shop.checkout.title') }}</h1>
        <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.checkout.description') }}</p>
    </header>

    <form
        method="POST"
        action="{{ route('checkout.store', $locale) }}"
        class="mt-10"
        x-data="{
            zone: @js($defaultZone),
            fees: @js($fees),
            subtotal: {{ (float) $totals['subtotal'] }},
            get shipping() { return this.fees[this.zone] ?? 0 },
            get total() { return this.subtotal + this.shipping },
            money(value) { return new Intl.NumberFormat(@js($locale === 'fr' ? 'fr-FR' : 'en-GH'), { style: 'currency', currency: @js(config('saraki.currency')) }).format(value) },
        }"
    >
        @csrf

        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-14">
            <div class="space-y-10">
                <fieldset>
                    <legend class="font-display text-heading-md">{{ __('shop.checkout.delivery_section') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="delivery_name" class="label-upper text-ink">{{ __('shop.checkout.delivery_name') }}</label>
                            <input
                                id="delivery_name"
                                name="delivery_name"
                                type="text"
                                autocomplete="name"
                                required
                                value="{{ old('delivery_name', $user->name) }}"
                                @class(['field mt-2', 'field-error' => $errors->has('delivery_name')])
                            >
                            @error('delivery_name')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="delivery_phone" class="label-upper text-ink">{{ __('shop.checkout.delivery_phone') }}</label>
                            <input
                                id="delivery_phone"
                                name="delivery_phone"
                                type="tel"
                                autocomplete="tel"
                                required
                                value="{{ old('delivery_phone', $user->phone) }}"
                                @class(['field mt-2', 'field-error' => $errors->has('delivery_phone')])
                            >
                            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.checkout.delivery_phone_hint') }}</p>
                            @error('delivery_phone')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="delivery_email" class="label-upper text-ink">{{ __('shop.checkout.delivery_email') }}</label>
                            <input
                                id="delivery_email"
                                name="delivery_email"
                                type="email"
                                autocomplete="email"
                                value="{{ old('delivery_email', $user->email) }}"
                                @class(['field mt-2', 'field-error' => $errors->has('delivery_email')])
                            >
                            @error('delivery_email')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($zones->isEmpty())
                            <div class="sm:col-span-2">
                                <label for="delivery_city" class="label-upper text-ink">{{ __('shop.checkout.delivery_city') }}</label>
                                <input
                                    id="delivery_city"
                                    name="delivery_city"
                                    type="text"
                                    autocomplete="address-level2"
                                    required
                                    value="{{ old('delivery_city') }}"
                                    @class(['field mt-2', 'field-error' => $errors->has('delivery_city')])
                                >
                                @error('delivery_city')
                                    <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div class="sm:col-span-2">
                                <label for="shipping_zone" class="label-upper text-ink">{{ __('shop.checkout.shipping_zone') }}</label>
                                <select
                                    id="shipping_zone"
                                    name="shipping_zone"
                                    required
                                    x-model="zone"
                                    @class(['field mt-2', 'field-error' => $errors->has('shipping_zone')])
                                >
                                    <option value="">{{ __('shop.checkout.choose_zone') }}</option>
                                    @foreach ($zoneOptions as $option)
                                        <option value="{{ $option['name'] }}" @selected($defaultZone === $option['name'])>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shipping_zone')
                                    <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div class="sm:col-span-2">
                            <label for="delivery_address" class="label-upper text-ink">{{ __('shop.checkout.delivery_address') }}</label>
                            <input
                                id="delivery_address"
                                name="delivery_address"
                                type="text"
                                autocomplete="street-address"
                                required
                                value="{{ old('delivery_address') }}"
                                @class(['field mt-2', 'field-error' => $errors->has('delivery_address')])
                            >
                            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.checkout.delivery_address_hint') }}</p>
                            @error('delivery_address')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="label-upper text-ink">{{ __('shop.checkout.notes') }}</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                @class(['field mt-2', 'field-error' => $errors->has('notes')])
                            >{{ old('notes') }}</textarea>
                            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.checkout.notes_hint') }}</p>
                            @error('notes')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="font-display text-heading-md">{{ __('shop.checkout.payment_section') }}</legend>

                    @if ($methods === [])
                        <x-ui.alert type="warning" class="mt-4">{{ __('shop.checkout.no_methods') }}</x-ui.alert>
                    @else
                        <div class="mt-5 space-y-3">
                            @foreach ($methods as $key => $method)
                                <label class="flex cursor-pointer items-start gap-3 rounded-sm border border-line bg-canvas p-4 transition-colors duration-150 ease-brand has-[:checked]:border-charcoal">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="{{ $key }}"
                                        class="mt-1"
                                        @checked($defaultMethod === $key)
                                    >

                                    <span class="min-w-0">
                                        <span class="block font-semibold text-ink">{{ $method['label'] }}</span>
                                        <span class="mt-1 block text-body-sm text-ink-muted">{{ $method['hint'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('payment_method')
                            <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                        @enderror
                    @endif

                    <p class="mt-4 text-body-sm text-ink-muted">{{ __('shop.payment.secure_note') }}</p>
                </fieldset>
            </div>

            <aside aria-labelledby="checkout-summary">
                <div class="rounded-sm border border-line bg-canvas p-6 lg:sticky lg:top-24">
                    <h2 id="checkout-summary" class="font-display text-heading-sm">{{ __('shop.checkout.summary_section') }}</h2>

                    <ul class="mt-5 space-y-4 border-b border-line pb-4">
                        @foreach ($items as $item)
                            <li class="flex items-start justify-between gap-4 text-body-sm">
                                <span class="min-w-0">
                                    <span class="block text-ink">{{ $item->variant?->product?->translate('name') }}</span>
                                    @if (filled($item->variant?->label))
                                        <span class="block text-ink-muted">{{ $item->variant->label }}</span>
                                    @endif
                                    <span class="block text-ink-muted">× {{ $item->quantity }}</span>
                                </span>
                                <span class="tabular whitespace-nowrap text-ink">
                                    {{ money((float) $item->unit_price * $item->quantity) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <dl class="mt-4 space-y-3 text-body-sm">
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-ink-muted">{{ __('shop.cart.subtotal') }}</dt>
                            <dd class="tabular text-ink">{{ money($totals['subtotal']) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-ink-muted">{{ __('shop.checkout.shipping') }}</dt>
                            <dd class="tabular text-ink" x-text="shipping > 0 ? money(shipping) : @js(__('shop.checkout.shipping_free'))">
                                {{ money(0) }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex items-baseline justify-between gap-4 border-t border-ink pt-4">
                        <span class="label-upper">{{ __('shop.cart.total') }}</span>
                        <span class="text-price tabular text-ink" x-text="money(total)">{{ money($totals['subtotal']) }}</span>
                    </div>

                    @if ($methods === [])
                        <x-ui.button class="mt-6 w-full" size="checkout" disabled>
                            {{ __('shop.checkout.place_order') }}
                        </x-ui.button>
                    @else
                        <x-ui.button
                            type="submit"
                            class="mt-6 w-full"
                            size="checkout"
                        >
                            {{ __('shop.checkout.place_order') }}
                        </x-ui.button>
                    @endif

                    <p class="mt-4 text-body-sm text-ink-muted">{{ __('shop.checkout.terms_note') }}</p>

                    <a href="{{ route('cart.index', $locale) }}" class="link mt-4 block text-center text-body-sm">
                        {{ __('shop.checkout.edit_cart') }}
                    </a>
                </div>
            </aside>
        </div>
    </form>
</x-app-layout>
