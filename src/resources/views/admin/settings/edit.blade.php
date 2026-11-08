@php
    $zones = collect(old('shipping_zones', $settings->get('shipping_zones', [])))
        ->filter(fn ($zone) => is_array($zone))
        ->values();

    if ($zones->isEmpty()) {
        $zones->push(['name' => '', 'name_en' => '', 'fee' => '', 'delivery_days' => '']);
    }

    // One spare row at the bottom: a zone is added by typing into it, no JavaScript.
    $zones->push(['name' => '', 'name_en' => '', 'fee' => '', 'delivery_days' => '']);

    $methods = old('payment_methods', $settings->get('payment_methods', []));
    $methods = is_array($methods) ? $methods : [];

    $methodLabels = [
        'paystack' => __('admin.settings.paystack'),
        'cod' => __('admin.settings.cod'),
        'transfer' => __('admin.settings.transfer'),
    ];

    $links = old('social_links', $settings->get('social_links', []));
    $links = is_array($links) ? $links : [];

    $mapUrl = $settings->mapEmbedUrl();
@endphp

<x-admin-layout :title="__('admin.settings.title')">
    <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.settings.subtitle') }}</p>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-8">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start">
            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.settings.general') }}</h2>
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.general_hint') }}</p>

                    <div class="mt-5 grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="store_name" :value="__('admin.settings.store_name')" />
                            <x-text-input id="store_name" name="store_name" type="text" class="mt-1" :value="old('store_name', $settings->text('store_name'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('store_name')" />
                        </div>

                        <div>
                            <x-input-label for="contact_email" :value="__('admin.settings.contact_email')" />
                            <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1" :value="old('contact_email', $settings->text('contact_email'))" required />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.contact_email_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('contact_email')" />
                        </div>

                        <div>
                            <x-input-label for="contact_phone" :value="__('admin.settings.contact_phone')" />
                            <x-text-input id="contact_phone" name="contact_phone" type="tel" class="mt-1 tabular" :value="old('contact_phone', $settings->text('contact_phone'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
                        </div>

                        <div>
                            <x-input-label for="whatsapp_number" :value="__('admin.settings.whatsapp_number')" />
                            <x-text-input id="whatsapp_number" name="whatsapp_number" type="tel" class="mt-1 tabular" :value="old('whatsapp_number', $settings->text('whatsapp_number'))" />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.whatsapp_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="store_address" :value="__('admin.settings.store_address')" />
                            <textarea id="store_address" name="store_address" rows="2" class="field mt-1">{{ old('store_address', $settings->text('store_address')) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('store_address')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="map_embed" :value="__('admin.settings.map_embed')" />
                            <textarea id="map_embed" name="map_embed" rows="3" class="field mt-1 font-mono text-body-sm">{{ old('map_embed', $settings->text('map_embed')) }}</textarea>
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.map_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('map_embed')" />
                        </div>
                    </div>

                    @if ($mapUrl)
                        <div class="mt-5">
                            <p class="label-upper text-ink-muted">{{ __('admin.settings.map_preview') }}</p>
                            <div class="mt-2 overflow-hidden rounded-xs border border-line">
                                <iframe
                                    src="{{ $mapUrl }}"
                                    title="{{ __('admin.settings.map_preview') }}"
                                    class="h-56 w-full"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.settings.shipping_zones') }}</h2>
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.shipping_hint') }}</p>

                    <div class="mt-5 space-y-4">
                        @foreach ($zones as $index => $zone)
                            <div class="grid gap-3 rounded-xs border border-line p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_6rem_5rem]">
                                <div>
                                    <x-input-label :for="'zone-name-'.$index" :value="__('admin.settings.zone_name')" />
                                    <input
                                        id="zone-name-{{ $index }}"
                                        name="shipping_zones[{{ $index }}][name]"
                                        type="text"
                                        value="{{ $zone['name'] ?? '' }}"
                                        @class(['field mt-1', 'field-error' => $errors->has("shipping_zones.{$index}.name")])
                                    >
                                    @error("shipping_zones.{$index}.name")
                                        <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label :for="'zone-name-en-'.$index" :value="__('admin.settings.zone_name_en')" />
                                    <input
                                        id="zone-name-en-{{ $index }}"
                                        name="shipping_zones[{{ $index }}][name_en]"
                                        type="text"
                                        value="{{ $zone['name_en'] ?? '' }}"
                                        @class(['field mt-1', 'field-error' => $errors->has("shipping_zones.{$index}.name_en")])
                                    >
                                    @error("shipping_zones.{$index}.name_en")
                                        <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label :for="'zone-fee-'.$index" :value="__('admin.settings.zone_fee')" />
                                    <input
                                        id="zone-fee-{{ $index }}"
                                        name="shipping_zones[{{ $index }}][fee]"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value="{{ $zone['fee'] ?? '' }}"
                                        @class(['field mt-1 tabular', 'field-error' => $errors->has("shipping_zones.{$index}.fee")])
                                    >
                                    @error("shipping_zones.{$index}.fee")
                                        <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-input-label :for="'zone-days-'.$index" :value="__('admin.settings.zone_days')" />
                                    <input
                                        id="zone-days-{{ $index }}"
                                        name="shipping_zones[{{ $index }}][delivery_days]"
                                        type="number"
                                        min="0"
                                        max="60"
                                        value="{{ $zone['delivery_days'] ?? '' }}"
                                        @class(['field mt-1 tabular', 'field-error' => $errors->has("shipping_zones.{$index}.delivery_days")])
                                    >
                                    @error("shipping_zones.{$index}.delivery_days")
                                        <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-3 text-body-sm text-ink-muted">{{ __('admin.settings.zone_add_hint') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('shipping_zones')" />
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.settings.payment_methods') }}</h2>
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.payment_hint') }}</p>

                    <div class="mt-5 space-y-3">
                        @foreach ($methodLabels as $method => $label)
                            <div>
                                <input type="hidden" name="payment_methods[{{ $method }}]" value="0">
                                <label for="method-{{ $method }}" class="flex items-center gap-3">
                                    <input
                                        id="method-{{ $method }}"
                                        name="payment_methods[{{ $method }}]"
                                        type="checkbox"
                                        value="1"
                                        class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold"
                                        @checked(filter_var($methods[$method] ?? false, FILTER_VALIDATE_BOOLEAN))
                                    >
                                    <span class="text-body-md text-ink">{{ $label }}</span>
                                </label>
                            </div>
                        @endforeach

                        <x-input-error class="mt-2" :messages="$errors->get('payment_methods')" />
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.settings.commerce') }}</h2>

                    <div class="mt-5 space-y-5">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-body-md text-ink">{{ __('admin.settings.order_expiry_minutes') }}</span>
                            </div>
                            <x-text-input
                                id="order_expiry_minutes"
                                name="order_expiry_minutes"
                                type="number"
                                min="5"
                                max="1440"
                                class="mt-2 tabular"
                                :value="old('order_expiry_minutes', $settings->get('order_expiry_minutes', 60))"
                                required
                            />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.order_expiry_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('order_expiry_minutes')" />
                        </div>

                        <div class="border-t border-line pt-5">
                            <input type="hidden" name="tax_display" value="0">
                            <label for="tax_display" class="flex items-center gap-3">
                                <input
                                    id="tax_display"
                                    name="tax_display"
                                    type="checkbox"
                                    value="1"
                                    class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold"
                                    @checked(old('tax_display', $settings->bool('tax_display')))
                                >
                                <span class="text-body-md text-ink">{{ __('admin.settings.tax_display') }}</span>
                            </label>
                            <x-input-error class="mt-2" :messages="$errors->get('tax_display')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.settings.social') }}</h2>
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.settings.social_hint') }}</p>

                    <div class="mt-5 space-y-4">
                        @foreach (['instagram', 'facebook', 'tiktok'] as $network)
                            <div>
                                <x-input-label :for="'social-'.$network" :value="__('admin.settings.'.$network)" />
                                <x-text-input
                                    :id="'social-'.$network"
                                    :name="'social_links['.$network.']'"
                                    type="url"
                                    class="mt-1"
                                    :value="$links[$network] ?? ''"
                                    placeholder="https://"
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('social_links.'.$network)" />
                            </div>
                        @endforeach
                    </div>
                </section>

                <x-ui.button type="submit" class="w-full">{{ __('admin.common.save') }}</x-ui.button>
            </div>
        </div>
    </form>
</x-admin-layout>
