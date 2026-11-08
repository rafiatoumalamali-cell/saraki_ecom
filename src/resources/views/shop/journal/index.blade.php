@php
    $locale = app()->getLocale();
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
            <li><span aria-current="page">{{ __('ui.journal') }}</span></li>
        </ol>
    </nav>

    <header class="mt-8 max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.journal.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md md:text-display-lg">{{ __('shop.journal.title') }}</h1>
        <p class="mt-4 text-body-md text-ink-soft">{{ __('shop.journal.description') }}</p>
    </header>

    @if ($posts->isEmpty())
        <div class="mt-10 rounded-sm border border-line bg-canvas p-10 text-center">
            <p class="font-display text-heading-sm">{{ __('shop.journal.empty') }}</p>
            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.journal.empty_hint') }}</p>
        </div>
    @else
        <ul class="mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <li class="group relative flex flex-col">
                    <div class="overflow-hidden rounded-sm bg-beige-50">
                        @if ($post->coverUrl())
                            <img
                                src="{{ $post->coverUrl() }}"
                                alt="{{ $post->translate('title') }}"
                                loading="lazy"
                                class="aspect-[4/3] w-full object-cover transition-transform duration-500 ease-brand group-hover:scale-[1.03]"
                            >
                        @else
                            <div class="flex aspect-[4/3] w-full items-center justify-center bg-beige">
                                <span class="font-display text-heading-md tracking-[0.3em] text-beige-200">{{ config('saraki.store_name') }}</span>
                            </div>
                        @endif
                    </div>

                    <p class="label-upper mt-4 text-ink-muted">
                        {{ localized_date($post->publishedOn()) }}
                    </p>

                    <h2 class="mt-2 font-display text-heading-sm">
                        <a
                            href="{{ $post->url($locale) }}"
                            class="text-ink transition-colors duration-150 ease-brand after:absolute after:inset-0 after:content-[''] hover:text-gold-700"
                        >{{ $post->translate('title') }}</a>
                    </h2>

                    @if (filled($post->translate('excerpt')))
                        <p class="mt-2 line-clamp-3 text-body-sm text-ink-soft">{{ $post->translate('excerpt') }}</p>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @endif
</x-app-layout>
