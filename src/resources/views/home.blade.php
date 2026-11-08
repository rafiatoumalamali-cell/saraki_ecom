<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :alternates="$seo['alternates']"
    />

    <section class="rounded-sm bg-charcoal px-6 py-16 text-center text-beige md:px-12 lg:py-24">
        <p class="label-upper text-gold">{{ __('shop.home.hero_eyebrow') }}</p>

        <h1 class="mx-auto mt-6 max-w-[18ch] font-display text-display-lg md:text-display-xl">
            {{ __('shop.home.hero_title') }}
        </h1>

        <p class="mx-auto mt-6 max-w-prose text-body-lg text-beige/70">{{ __('shop.home.hero_text') }}</p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <x-ui.button :href="route('shop.index', app()->getLocale())" variant="gold">
                {{ __('shop.home.hero_cta') }}
            </x-ui.button>

            <x-ui.button :href="route('appointments.index', app()->getLocale())" variant="on-dark">
                {{ __('shop.home.hero_secondary') }}
            </x-ui.button>
        </div>
    </section>

    <section class="mt-16" aria-labelledby="featured-heading">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="featured-heading" class="font-display text-heading-lg">{{ __('shop.home.featured_title') }}</h2>
                <p class="mt-2 max-w-prose text-body-md text-ink-soft">{{ __('shop.home.featured_text') }}</p>
            </div>

            <a href="{{ route('shop.index', app()->getLocale()) }}" class="link text-body-sm">{{ __('shop.home.view_all') }}</a>
        </div>

        @if ($featured->isEmpty())
            <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
                {{ __('shop.home.empty') }}
            </p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-ui.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    @if ($collections->isNotEmpty())
        <section class="mt-20" aria-labelledby="collections-heading">
            <h2 id="collections-heading" class="font-display text-heading-lg">{{ __('shop.home.collections_title') }}</h2>
            <p class="mt-2 max-w-prose text-body-md text-ink-soft">{{ __('shop.home.collections_text') }}</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($collections as $collection)
                    <a
                        href="{{ route('shop.index', ['locale' => app()->getLocale(), 'category' => $collection->slug]) }}"
                        class="group flex flex-col justify-between rounded-sm border border-line bg-canvas p-6 transition-colors duration-150 ease-brand hover:border-charcoal"
                    >
                        <span class="font-display text-heading-sm group-hover:text-gold-700">
                            {{ $collection->translate('name') }}
                        </span>

                        <span class="label-upper mt-8 text-ink-muted">
                            {{ trans_choice('shop.home.collections_count', $collection->products_count, ['count' => $collection->products_count]) }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($latest->isNotEmpty())
        <section class="mt-20" aria-labelledby="latest-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="latest-heading" class="font-display text-heading-lg">{{ __('shop.home.latest_title') }}</h2>
                    <p class="mt-2 max-w-prose text-body-md text-ink-soft">{{ __('shop.home.latest_text') }}</p>
                </div>

                <a href="{{ route('shop.index', ['locale' => app()->getLocale(), 'sort' => 'newest']) }}" class="link text-body-sm">
                    {{ __('shop.home.view_all') }}
                </a>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latest as $product)
                    <x-ui.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-20 rounded-sm bg-beige px-6 py-14 md:px-12">
        <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="label-upper text-gold-700">{{ __('shop.home.story_eyebrow') }}</p>
                <h2 class="mt-4 font-display text-heading-lg md:text-display-md">{{ __('shop.home.story_title') }}</h2>
            </div>

            <div>
                <p class="max-w-prose text-body-md text-ink-soft">{{ __('shop.home.story_text') }}</p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <x-ui.button :href="route('shop.index', app()->getLocale())" variant="secondary">
                        {{ __('shop.home.hero_cta') }}
                    </x-ui.button>
                    <span class="label-upper text-ink-muted">{{ __('shop.home.story_cta') }} · {{ __('ui.soon') }}</span>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
