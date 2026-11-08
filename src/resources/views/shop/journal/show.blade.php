@php
    $locale = app()->getLocale();
    $body = (string) $post->translate('body');
    $paragraphs = preg_split('/\n\s*\n/', $body, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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
            <li><a href="{{ route('home', $locale) }}" class="link">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('journal.index', $locale) }}" class="link">{{ __('ui.journal') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><span aria-current="page">{{ $post->translate('title') }}</span></li>
        </ol>
    </nav>

    <article class="mt-8">
        <header class="max-w-3xl">
            <p class="label-upper text-gold-700">{{ __('shop.journal.eyebrow') }}</p>

            <h1 class="mt-3 font-display text-display-md md:text-display-lg">{{ $post->translate('title') }}</h1>

            <p class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-body-sm text-ink-muted">
                <span>{{ localized_date($post->publishedOn()) }}</span>

                @if ($post->author)
                    <span aria-hidden="true">·</span>
                    <span>{{ __('shop.journal.by', ['name' => $post->author->name]) }}</span>
                @endif
            </p>
        </header>

        @if ($post->coverUrl())
            <img
                src="{{ $post->coverUrl() }}"
                alt="{{ $post->translate('title') }}"
                class="mt-8 aspect-[16/9] w-full rounded-sm object-cover"
            >
        @endif

        @if (filled($post->translate('excerpt')))
            <p class="mt-10 max-w-prose font-display text-heading-sm text-ink-soft">
                {{ $post->translate('excerpt') }}
            </p>
        @endif

        <div class="mt-8 max-w-prose space-y-5 text-body-md text-ink-soft">
            @foreach ($paragraphs as $paragraph)
                <p>{!! nl2br(e(trim($paragraph))) !!}</p>
            @endforeach
        </div>

        <p class="mt-12 border-t border-line pt-6">
            <a href="{{ route('journal.index', $locale) }}" class="link text-body-sm">
                {{ __('shop.journal.back') }}
            </a>
        </p>
    </article>

    @if ($related->isNotEmpty())
        <section class="mt-16" aria-labelledby="related-heading">
            <h2 id="related-heading" class="font-display text-heading-lg">{{ __('shop.journal.related') }}</h2>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <article>
                        <a href="{{ $item->url($locale) }}" class="block overflow-hidden rounded-sm bg-beige-50">
                            @if ($item->coverUrl())
                                <img src="{{ $item->coverUrl() }}" alt="{{ $item->translate('title') }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                            @else
                                <div class="flex aspect-[4/3] w-full items-center justify-center bg-beige">
                                    <span class="font-display text-heading-md tracking-[0.3em] text-beige-200">{{ config('saraki.store_name') }}</span>
                                </div>
                            @endif
                        </a>

                        <p class="label-upper mt-3 text-ink-muted">{{ localized_date($item->publishedOn()) }}</p>

                        <h3 class="mt-1 font-display text-heading-sm">
                            <a href="{{ $item->url($locale) }}" class="link">{{ $item->translate('title') }}</a>
                        </h3>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</x-app-layout>
