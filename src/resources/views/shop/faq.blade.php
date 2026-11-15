@php
    $locale = app()->getLocale();
    $groups = __('faq.items');
    $whatsapp = $settings->whatsappLink();
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :alternates="$seo['alternates']"
    />
    <x-ui.json-ld :data="$jsonLd" />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home', $locale) }}" class="link">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><span aria-current="page">{{ __('faq.title') }}</span></li>
        </ol>
    </nav>

    <header class="mt-8 max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('faq.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md md:text-display-lg">{{ __('faq.title') }}</h1>
        <p class="mt-4 text-body-md text-ink-soft">{{ __('faq.intro') }}</p>
    </header>

    <div class="mt-12 grid gap-12 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-16">
        <div class="space-y-12">
            @foreach ($groups as $group => $items)
                <section aria-labelledby="faq-{{ $group }}">
                    <h2 id="faq-{{ $group }}" class="font-display text-heading-md">{{ __("faq.groups.{$group}") }}</h2>

                    <div class="mt-5 divide-y divide-line border-y border-line">
                        @foreach ($items as $item)
                            <details class="group py-1">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 text-body-md font-medium text-ink transition-colors duration-200 ease-brand hover:text-charcoal-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold/60 focus-visible:ring-offset-4 [&::-webkit-details-marker]:hidden">
                                    <span>{{ $item['q'] }}</span>
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-line text-ink-muted transition-all duration-200 ease-brand group-open:rotate-45 group-open:border-gold group-open:text-gold" aria-hidden="true">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </span>
                                </summary>
                                <div class="pb-5 pe-11 text-body-md leading-relaxed text-ink-soft">
                                    {{ $item['a'] }}
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-2xl border border-line bg-beige-50 p-6">
                <h2 class="font-display text-heading-sm">{{ __('faq.contact_cta') }}</h2>
                <p class="mt-2 text-body-sm text-ink-muted">{{ __('faq.contact_note') }}</p>
                <div class="mt-5 space-y-3">
                    <x-ui.button :href="route('contact.show', $locale)" variant="secondary" size="compact" class="w-full">
                        {{ __('ui.contact') }}
                    </x-ui.button>
                    @if ($whatsapp)
                        <x-ui.button :href="$whatsapp" variant="primary" size="compact" class="w-full">
                            {{ __('shop.contact.whatsapp') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
