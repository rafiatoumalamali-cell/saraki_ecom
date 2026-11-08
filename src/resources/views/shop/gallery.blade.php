@php
    $locale = app()->getLocale();

    // Handed to Alpine once: the lightbox then walks the list without another request.
    $photos = $items->values()->map(fn ($item) => [
        'src' => $item->url(),
        'title' => $item->translate('title'),
        'category' => $item->category->label(),
    ])->all();
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :image="$seo['image']"
        :alternates="$seo['alternates']"
    />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home', $locale) }}" class="link">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li>
                @if ($category)
                    <a href="{{ route('gallery.index', $locale) }}" class="link">{{ __('ui.gallery') }}</a>
                @else
                    <span aria-current="page">{{ __('ui.gallery') }}</span>
                @endif
            </li>
            @if ($category)
                <li aria-hidden="true">/</li>
                <li><span aria-current="page">{{ $category->label() }}</span></li>
            @endif
        </ol>
    </nav>

    <header class="mt-8 max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.gallery.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md md:text-display-lg">
            {{ $category?->label() ?? __('shop.gallery.title') }}
        </h1>
        <p class="mt-4 text-body-md text-ink-soft">{{ __('shop.gallery.description') }}</p>
    </header>

    <div class="mt-8 flex flex-wrap gap-2">
        <a
            href="{{ route('gallery.index', $locale) }}"
            @class([
                'label-upper inline-flex h-10 items-center rounded-xs border px-4 transition-colors duration-150 ease-brand',
                'border-charcoal bg-charcoal text-beige' => $category === null,
                'border-line text-ink hover:border-charcoal' => $category !== null,
            ])
            @if ($category === null) aria-current="page" @endif
        >{{ __('shop.gallery.all') }}</a>

        @foreach ($categories as $option)
            @continue($option['count'] === 0)

            <a
                href="{{ route('gallery.index', ['locale' => $locale, 'category' => $option['value']]) }}"
                @class([
                    'label-upper inline-flex h-10 items-center gap-2 rounded-xs border px-4 transition-colors duration-150 ease-brand',
                    'border-charcoal bg-charcoal text-beige' => $category?->value === $option['value'],
                    'border-line text-ink hover:border-charcoal' => $category?->value !== $option['value'],
                ])
                @if ($category?->value === $option['value']) aria-current="page" @endif
            >
                {{ $option['label'] }}
                <span class="tabular opacity-60" aria-hidden="true">{{ $option['count'] }}</span>
            </a>
        @endforeach
    </div>

    @if ($items->isEmpty())
        <div class="mt-10 rounded-sm border border-line bg-canvas p-10 text-center">
            <p class="font-display text-heading-sm">{{ __('shop.gallery.empty') }}</p>
            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.gallery.empty_hint') }}</p>
        </div>
    @else
        <div
            x-data="{
                open: false,
                index: 0,
                photos: @js($photos),
                get current() { return this.photos[this.index] ?? null; },
                show(position) {
                    this.index = position;
                    this.open = true;
                    document.body.classList.add('overflow-hidden');
                    $nextTick(() => $refs.dialog?.focus());
                },
                close() {
                    this.open = false;
                    document.body.classList.remove('overflow-hidden');
                },
                prev() { this.index = (this.index + this.photos.length - 1) % this.photos.length; },
                next() { this.index = (this.index + 1) % this.photos.length; },
            }"
            @keydown.escape.window="open && close()"
            @keydown.arrow-left.window="open && prev()"
            @keydown.arrow-right.window="open && next()"
            class="mt-10"
        >
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $position => $item)
                    <li>
                        <button
                            type="button"
                            @click="show({{ $position }})"
                            class="group relative block w-full overflow-hidden rounded-sm bg-beige-50 text-start"
                        >
                            <img
                                src="{{ $item->url() }}"
                                alt="{{ $item->translate('title') }}"
                                loading="lazy"
                                class="aspect-square w-full object-cover transition-transform duration-500 ease-brand group-hover:scale-[1.03]"
                            >

                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-charcoal/80 via-charcoal/30 to-transparent p-4">
                                <span class="label-upper block text-gold">{{ $item->category->label() }}</span>
                                <span class="mt-1 block font-display text-heading-sm text-beige">{{ $item->translate('title') }}</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>

            <div
                x-cloak
                x-show="open"
                x-transition.opacity.duration.200ms
                class="fixed inset-0 z-50 flex items-center justify-center bg-charcoal/95 p-4 sm:p-8"
                role="dialog"
                aria-modal="true"
                aria-label="{{ __('shop.gallery.lightbox') }}"
                tabindex="-1"
                x-ref="dialog"
                @click.self="close()"
            >
                <button
                    type="button"
                    class="label-upper absolute end-4 top-4 flex h-11 w-11 items-center justify-center rounded-xs text-beige transition-colors duration-150 ease-brand hover:bg-charcoal-800 hover:text-gold"
                    @click="close()"
                >
                    <span class="sr-only">{{ __('ui.close') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="absolute start-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-xs text-beige transition-colors duration-150 ease-brand hover:bg-charcoal-800 hover:text-gold sm:start-6"
                    @click="prev()"
                >
                    <span class="sr-only">{{ __('shop.gallery.previous') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <figure class="max-w-5xl">
                    <img
                        :src="current ? current.src : ''"
                        :alt="current ? current.title : ''"
                        class="mx-auto max-h-[76vh] w-auto max-w-full rounded-sm object-contain"
                    >
                    <figcaption class="mt-5 text-center">
                        <span class="label-upper block text-gold" x-text="current ? current.category : ''"></span>
                        <span class="mt-1 block font-display text-heading-sm text-beige" x-text="current ? current.title : ''"></span>
                        <span class="label-upper mt-2 block text-beige/50">
                            {{ __('shop.gallery.counter') }}
                            <span class="tabular" x-text="(index + 1) + ' / ' + photos.length"></span>
                        </span>
                    </figcaption>
                </figure>

                <button
                    type="button"
                    class="absolute end-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-xs text-beige transition-colors duration-150 ease-brand hover:bg-charcoal-800 hover:text-gold sm:end-6"
                    @click="next()"
                >
                    <span class="sr-only">{{ __('shop.gallery.next') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
</x-app-layout>
