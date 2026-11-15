<x-app-layout :title="$seo['title']" bare>
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :alternates="$seo['alternates']"
    />

    @php
        $locale = app()->getLocale();

        $artFor = fn (?string $slug) => match ($slug) {
            'caftans' => 'caftan',
            'ensembles-wax' => 'ensemble',
            'robes-de-soiree' => 'soiree',
            'pret-a-porter' => 'pret',
            'accessoires' => 'accessoires',
            default => 'boubou',
        };

        $heroImage = collect(['webp', 'jpg', 'jpeg', 'png'])
            ->map(fn (string $ext) => "images/hero.$ext")
            ->first(fn (string $path) => file_exists(public_path($path)));

        $promises = [
            ['label' => __('shop.home.promise_measure'), 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z'],
            ['label' => __('shop.home.promise_hand'), 'icon' => 'm7.84 12.5 3.18 3.18m0-3.18-3.18 3.18m6.36-6.36 3.18 3.18m0-3.18-3.18 3.18M6.75 3.75h10.5a3 3 0 0 1 3 3v10.5a3 3 0 0 1-3 3H6.75a3 3 0 0 1-3-3V6.75a3 3 0 0 1 3-3Z'],
            ['label' => __('shop.home.promise_payment'), 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
        ];
    @endphp

    <section class="relative overflow-hidden bg-charcoal text-beige">
        @if ($heroImage)
            <img
                src="{{ asset($heroImage) }}"
                alt=""
                aria-hidden="true"
                fetchpriority="high"
                class="pointer-events-none absolute inset-0 h-full w-full object-cover brightness-[.6]"
            >
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-charcoal/35"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-gradient-to-r from-charcoal/90 via-charcoal/60 to-transparent"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-gradient-to-t from-charcoal/50 via-transparent to-transparent"></div>
        @endif

        <span aria-hidden="true" class="pointer-events-none absolute -top-48 end-[-12%] h-[620px] w-[620px] rounded-full bg-gold/15 blur-[150px]"></span>
        <span aria-hidden="true" class="pointer-events-none absolute bottom-[-25%] start-[-8%] h-[460px] w-[460px] rounded-full bg-gold/10 blur-[130px]"></span>

        <div class="container relative grid items-center gap-14 py-16 lg:grid-cols-[1.05fr_.95fr] lg:gap-16 lg:py-24">
            <div class="animate-fade-up [text-shadow:0_1px_16px_rgba(0,0,0,.55)]">
                <p class="label-upper flex items-center gap-3 text-gold">
                    <span aria-hidden="true" class="h-px w-8 bg-gold/60"></span>
                    {{ __('shop.home.hero_eyebrow') }}
                </p>

                <h1 class="mt-7 max-w-[15ch] font-display text-display-2xl text-beige">
                    {{ __('shop.home.hero_title') }}
                </h1>

                <p class="mt-7 max-w-[48ch] text-body-lg text-beige/70">{{ __('shop.home.hero_text') }}</p>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <x-ui.button :href="route('shop.index', $locale)" variant="gold" size="hero">
                        {{ __('shop.home.hero_cta') }}
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </x-ui.button>

                    <x-ui.button :href="route('appointments.index', $locale)" variant="on-dark" size="hero">
                        {{ __('shop.home.hero_secondary') }}
                    </x-ui.button>
                </div>

                <p class="label-upper mt-9 flex items-center gap-2.5 text-beige/60">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    {{ __('shop.home.hero_note') }}
                </p>
            </div>

        </div>
    </section>

    <section class="border-b border-line bg-canvas" aria-label="{{ __('shop.home.hero_eyebrow') }}">
        <div class="container grid gap-5 py-7 sm:grid-cols-3 sm:gap-6">
            @foreach ($promises as $promise)
                <div class="flex items-center justify-center gap-3 sm:justify-start" data-reveal style="--reveal-delay: {{ $loop->index * 80 }}ms">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-tint-warning text-gold-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $promise['icon'] }}" />
                        </svg>
                    </span>
                    <span class="label-upper text-ink-soft">{{ $promise['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="container py-16 lg:py-24" aria-labelledby="featured-heading">
        <div class="flex flex-wrap items-end justify-between gap-6" data-reveal>
            <div class="max-w-[54ch]">
                <h2 id="featured-heading" class="font-display text-display-md">{{ __('shop.home.featured_title') }}</h2>
                <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.home.featured_text') }}</p>
            </div>

            <a href="{{ route('shop.index', $locale) }}" class="link inline-flex items-center gap-2 text-body-sm">
                {{ __('shop.home.view_all') }}
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>

        @if ($featured->isEmpty())
            <p class="card mt-10 p-8 text-body-sm text-ink-muted">{{ __('shop.home.empty') }}</p>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <div data-reveal style="--reveal-delay: {{ $loop->index * 70 }}ms">
                        <x-ui.product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    @if ($collections->isNotEmpty())
        <section class="border-y border-line bg-beige-100 py-16 lg:py-24" aria-labelledby="collections-heading">
            <div class="container">
                <div class="max-w-[54ch]" data-reveal>
                    <h2 id="collections-heading" class="font-display text-display-md">{{ __('shop.home.collections_title') }}</h2>
                    <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.home.collections_text') }}</p>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($collections as $collection)
                        <a
                            href="{{ route('shop.index', ['locale' => $locale, 'category' => $collection->slug]) }}"
                            class="card card-hover group flex items-center gap-5 p-5"
                            data-reveal
                            style="--reveal-delay: {{ $loop->index * 60 }}ms"
                        >
                            <span class="h-24 w-[72px] shrink-0 overflow-hidden rounded-sm">
                                <x-ui.garment-art :variant="$artFor($collection->slug)" />
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block font-display text-heading-sm text-ink transition-colors duration-200 ease-brand group-hover:text-gold-700">
                                    {{ $collection->translate('name') }}
                                </span>
                                <span class="label-upper mt-2 block text-ink-muted">
                                    {{ trans_choice('shop.home.collections_count', $collection->products_count, ['count' => $collection->products_count]) }}
                                </span>
                            </span>

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line text-ink-muted transition-all duration-200 ease-brand group-hover:border-gold group-hover:bg-gold group-hover:text-charcoal">
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($latest->isNotEmpty())
        <section class="container py-16 lg:py-24" aria-labelledby="latest-heading">
            <div class="flex flex-wrap items-end justify-between gap-6" data-reveal>
                <div class="max-w-[54ch]">
                    <h2 id="latest-heading" class="font-display text-display-md">{{ __('shop.home.latest_title') }}</h2>
                    <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.home.latest_text') }}</p>
                </div>

                <a href="{{ route('shop.index', ['locale' => $locale, 'sort' => 'newest']) }}" class="link inline-flex items-center gap-2 text-body-sm">
                    {{ __('shop.home.view_all') }}
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latest as $product)
                    <div data-reveal style="--reveal-delay: {{ $loop->index * 70 }}ms">
                        <x-ui.product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="container py-16 lg:py-24" aria-labelledby="story-heading">
        <div class="mx-auto max-w-[56ch] text-center" data-reveal>
            <p class="label-upper flex items-center justify-center gap-3 text-gold-700">
                <span aria-hidden="true" class="h-px w-8 bg-gold/60"></span>
                {{ __('shop.home.story_eyebrow') }}
                <span aria-hidden="true" class="h-px w-8 bg-gold/60"></span>
            </p>

            <h2 id="story-heading" class="mt-5 font-display text-display-md lg:text-display-lg">
                {{ __('shop.home.story_title') }}
            </h2>

            <p class="mt-6 text-body-md text-ink-soft">{{ __('shop.home.story_text') }}</p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-5">
                <x-ui.button :href="route('pages.show', ['locale' => $locale, 'page' => 'about'])" variant="secondary">
                    {{ __('shop.home.story_cta') }}
                </x-ui.button>
                <a href="{{ route('shop.index', $locale) }}" class="link text-body-sm">{{ __('shop.home.hero_cta') }}</a>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden bg-charcoal text-beige">
        <span aria-hidden="true" class="pointer-events-none absolute inset-0 bg-[radial-gradient(70%_120%_at_50%_0%,rgba(212,175,55,.20),transparent_65%)]"></span>

        <div class="container relative py-16 text-center lg:py-20" data-reveal>
            <p class="label-upper text-gold">{{ __('shop.home.rdv_eyebrow') }}</p>
            <h2 class="mx-auto mt-5 max-w-[20ch] font-display text-display-md lg:text-display-lg">{{ __('shop.home.rdv_title') }}</h2>
            <p class="mx-auto mt-5 max-w-[58ch] text-body-md text-beige/70">{{ __('shop.home.rdv_text') }}</p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                <x-ui.button :href="route('appointments.index', $locale)" variant="gold" size="hero">
                    {{ __('shop.home.hero_secondary') }}
                </x-ui.button>
                <x-ui.button :href="route('contact.show', $locale)" variant="on-dark" size="hero">
                    {{ __('ui.contact') }}
                </x-ui.button>
            </div>
        </div>
    </section>
</x-app-layout>