@php
    $locale = app()->getLocale();
    $email = $settings->text('contact_email') ?? config('saraki.admin_email');
    $phone = $settings->text('contact_phone');
    $address = $settings->text('store_address');
    $whatsapp = $settings->whatsappLink();
    $map = $settings->mapEmbedUrl();
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :alternates="$seo['alternates']"
    />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home', $locale) }}" class="link">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><span aria-current="page">{{ __('ui.contact') }}</span></li>
        </ol>
    </nav>

    <header class="mt-8 max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.contact.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md md:text-display-lg">{{ __('shop.contact.title') }}</h1>
        <p class="mt-4 text-body-md text-ink-soft">{{ __('shop.contact.description') }}</p>
    </header>

    <div class="mt-12 grid gap-12 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-16">
        <section aria-labelledby="form-heading">
            <h2 id="form-heading" class="font-display text-heading-md">{{ __('shop.contact.form_title') }}</h2>

            <form method="POST" action="{{ route('contact.store', $locale) }}" class="mt-6 space-y-5">
                @csrf

                {{-- Honeypot: hidden from people, filled by scripts. --}}
                <div class="sr-only" aria-hidden="true">
                    <label for="website">{{ __('shop.contact.fields.website') }}</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="name" :value="__('shop.contact.fields.name')" />
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', auth()->user()?->name) }}"
                            required
                            autocomplete="name"
                            @class(['field mt-1', 'field-error' => $errors->has('name')])
                        >
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('shop.contact.fields.email')" />
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', auth()->user()?->email) }}"
                            required
                            autocomplete="email"
                            @class(['field mt-1', 'field-error' => $errors->has('email')])
                        >
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('shop.contact.fields.phone')" />
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone', auth()->user()?->phone) }}"
                            autocomplete="tel"
                            @class(['field mt-1', 'field-error' => $errors->has('phone')])
                        >
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="subject" :value="__('shop.contact.fields.subject')" />
                        <input
                            id="subject"
                            name="subject"
                            type="text"
                            value="{{ old('subject') }}"
                            @class(['field mt-1', 'field-error' => $errors->has('subject')])
                        >
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="message" :value="__('shop.contact.fields.message')" />
                    <textarea
                        id="message"
                        name="message"
                        rows="7"
                        required
                        aria-describedby="message-hint"
                        @class(['field mt-1', 'field-error' => $errors->has('message')])
                    >{{ old('message') }}</textarea>
                    <p id="message-hint" class="mt-2 text-body-sm text-ink-muted">{{ __('shop.contact.fields.message_hint') }}</p>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>

                <x-ui.button type="submit" size="checkout">{{ __('shop.contact.submit') }}</x-ui.button>

                <p class="text-body-sm text-ink-muted">{{ __('shop.contact.privacy_note') }}</p>
            </form>
        </section>

        <aside class="space-y-8">
            <section class="rounded-sm border border-line bg-canvas p-6" aria-labelledby="ateliere-heading">
                <h2 id="ateliere-heading" class="label-upper text-gold-700">{{ __('shop.contact.details_title') }}</h2>

                <dl class="mt-4 space-y-4 text-body-sm">
                    @if ($address)
                        <div>
                            <dt class="label-upper text-ink-muted">{{ __('shop.contact.address') }}</dt>
                            <dd class="mt-1 text-ink">{{ $address }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="label-upper text-ink-muted">{{ __('shop.contact.email') }}</dt>
                        <dd class="mt-1"><a href="mailto:{{ $email }}" class="link">{{ $email }}</a></dd>
                    </div>

                    @if ($phone)
                        <div>
                            <dt class="label-upper text-ink-muted">{{ __('shop.contact.phone') }}</dt>
                            <dd class="mt-1"><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="link">{{ $phone }}</a></dd>
                        </div>
                    @endif
                </dl>

                @if ($whatsapp)
                    <x-ui.button :href="$whatsapp" variant="secondary" size="compact" class="mt-5 w-full">
                        {{ __('shop.contact.whatsapp') }}
                    </x-ui.button>
                @endif

                <p class="mt-5 text-body-sm text-ink-muted">{{ __('shop.contact.hours') }}</p>
            </section>

            @if ($map)
                <section aria-labelledby="map-heading">
                    <h2 id="map-heading" class="label-upper text-gold-700">{{ __('shop.contact.map_title') }}</h2>

                    <div class="mt-3 overflow-hidden rounded-sm border border-line">
                        <iframe
                            src="{{ $map }}"
                            title="{{ __('shop.contact.map_title') }}"
                            class="h-64 w-full"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
