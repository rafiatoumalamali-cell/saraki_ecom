@php
    $locale = app()->getLocale();
    $body = (string) $page->translate('body');
    $paragraphs = preg_split('/\n\s*\n/', $body, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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
            <li><span aria-current="page">{{ $page->translate('title') }}</span></li>
        </ol>
    </nav>

    <article class="mt-8 max-w-prose">
        <h1 class="font-display text-display-md md:text-display-lg">{{ $page->translate('title') }}</h1>

        <div class="mt-8 space-y-5 text-body-md text-ink-soft">
            @forelse ($paragraphs as $paragraph)
                <p>{!! nl2br(e(trim($paragraph))) !!}</p>
            @empty
                <p class="text-ink-muted">{{ __('shop.page.empty') }}</p>
            @endforelse
        </div>

        <p class="mt-12 border-t border-line pt-6 text-body-sm text-ink-muted">
            {{ __('shop.page.help') }}
            <a href="{{ route('contact.show', $locale) }}" class="link ms-1">{{ __('ui.contact') }}</a>
        </p>
    </article>
</x-app-layout>
