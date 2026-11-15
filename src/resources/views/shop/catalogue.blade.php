<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots'] ?? null"
        :alternates="$seo['alternates']"
    />

    @php
        $locale = app()->getLocale();

        $chipUrl = fn (?string $slug) => route('shop.index', array_filter([
            'locale' => $locale,
            'type' => $type !== \App\Models\Product::TYPE_READY_TO_WEAR ? $type : null,
            'category' => $slug,
            'q' => $term !== '' ? $term : null,
            'sort' => $sort !== 'newest' ? $sort : null,
        ]));

        $typeUrl = fn (string $target) => route('shop.index', array_filter([
            'locale' => $locale,
            'type' => $target !== \App\Models\Product::TYPE_READY_TO_WEAR ? $target : null,
            'q' => $term !== '' ? $term : null,
            'sort' => $sort !== 'newest' ? $sort : null,
        ]));
    @endphp

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home', $locale) }}" class="transition-colors duration-200 ease-brand hover:text-gold-700">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">
                <svg class="h-3.5 w-3.5 opacity-50 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </li>
            <li>
                @if ($category)
                    <a href="{{ route('shop.index', $locale) }}" class="transition-colors duration-200 ease-brand hover:text-gold-700">{{ __('ui.catalogue') }}</a>
                @else
                    <span class="text-ink" aria-current="page">{{ __('ui.catalogue') }}</span>
                @endif
            </li>
            @if ($category)
                <li aria-hidden="true">
                    <svg class="h-3.5 w-3.5 opacity-50 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </li>
                <li><span class="text-ink" aria-current="page">{{ $category->translate('name') }}</span></li>
            @endif
        </ol>
    </nav>

    <header class="mt-8 max-w-[62ch]">
        <p class="label-upper flex items-center gap-3 text-gold-700">
            <span aria-hidden="true" class="h-px w-8 bg-gold/60"></span>
            {{ __('shop.catalogue.eyebrow') }}
        </p>

        <h1 class="mt-4 font-display text-display-md md:text-display-lg">
            @if ($term !== '')
                {{ __('shop.search.heading', ['term' => $term]) }}
            @else
                {{ $category?->translate('name') ?? __('shop.catalogue.title') }}
            @endif
        </h1>

        <p class="mt-4 text-body-md text-ink-soft">
            @if ($category)
                {{ __('shop.catalogue.category_description', ['name' => $category->translate('name')]) }}
            @else
                {{ __('shop.catalogue.description') }}
            @endif
        </p>
    </header>

    <nav aria-label="{{ __('shop.catalogue.type_label') }}" class="mt-9">
        <ul class="flex flex-wrap gap-2">
            @foreach ([\App\Models\Product::TYPE_READY_TO_WEAR, \App\Models\Product::TYPE_MADE_TO_MEASURE] as $option)
                <li>
                    <a
                        href="{{ $typeUrl($option) }}"
                        @class([
                            'label-upper inline-flex h-11 items-center rounded-full border px-6 transition-all duration-200 ease-brand',
                            'border-charcoal bg-charcoal text-beige shadow-md' => $type === $option,
                            'border-line bg-canvas text-ink-soft hover:-translate-y-0.5 hover:border-line-strong hover:text-ink hover:shadow-sm' => $type !== $option,
                        ])
                        @if ($type === $option) aria-current="true" @endif
                    >{{ __('shop.catalogue.type_'.$option) }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    @if ($type === \App\Models\Product::TYPE_MADE_TO_MEASURE)
        <p class="mt-5 max-w-[62ch] rounded-xl border border-gold/25 bg-tint-warning px-5 py-4 text-body-sm text-ink-soft">
            {{ __('shop.catalogue.sur_mesure_hint') }}
        </p>
    @endif

    <nav aria-label="{{ __('shop.catalogue.category_label') }}" class="-mx-5 mt-7 overflow-x-auto px-5 sm:mx-0 sm:px-0">
        <ul class="flex w-max items-center gap-2 pb-1 sm:w-auto sm:flex-wrap">
            <li>
                <a
                    href="{{ $chipUrl(null) }}"
                    @class([
                        'label-upper inline-flex h-10 items-center rounded-full border px-4 transition-all duration-200 ease-brand',
                        'border-charcoal bg-charcoal text-beige shadow-sm' => $category === null,
                        'border-line bg-canvas text-ink-soft hover:-translate-y-0.5 hover:border-line-strong hover:text-ink hover:shadow-sm' => $category !== null,
                    ])
                    @if ($category === null) aria-current="true" @endif
                >{{ __('shop.catalogue.all_categories') }}</a>
            </li>
            @foreach ($categories as $option)
                <li>
                    <a
                        href="{{ $chipUrl($option->slug) }}"
                        @class([
                            'label-upper inline-flex h-10 items-center rounded-full border px-4 transition-all duration-200 ease-brand',
                            'border-charcoal bg-charcoal text-beige shadow-sm' => $category?->id === $option->id,
                            'border-line bg-canvas text-ink-soft hover:-translate-y-0.5 hover:border-line-strong hover:text-ink hover:shadow-sm' => $category?->id !== $option->id,
                        ])
                        @if ($category?->id === $option->id) aria-current="true" @endif
                    >{{ $option->translate('name') }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    <form
        method="GET"
        action="{{ route('shop.index', $locale) }}"
        class="card mt-6 p-4 md:p-5"
        role="search"
        x-data
    >
        @if ($category)
            <input type="hidden" name="category" value="{{ $category->slug }}">
        @endif
        @if ($type !== \App\Models\Product::TYPE_READY_TO_WEAR)
            <input type="hidden" name="type" value="{{ $type }}">
        @endif

        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,14rem)_auto] md:items-end">
            <div>
                <x-input-label for="q" :value="__('shop.catalogue.search_label')" />
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-ink-muted">
                        <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    <input
                        id="q"
                        name="q"
                        type="search"
                        value="{{ $term }}"
                        placeholder="{{ __('shop.catalogue.search_placeholder') }}"
                        class="field ps-11"
                    >
                </div>
            </div>

            <div>
                <x-input-label for="sort" :value="__('shop.catalogue.sort_label')" />
                <select id="sort" name="sort" class="field mt-1.5" @change="$el.form.requestSubmit()">
                    @foreach ($sorts as $key => $label)
                        <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <x-ui.button type="submit">{{ __('shop.catalogue.search_submit') }}</x-ui.button>
        </div>

        @if ($term !== '' || $category !== null)
            <a href="{{ route('shop.index', $locale) }}" class="link mt-4 inline-flex items-center gap-1.5 text-body-sm">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
                {{ __('shop.catalogue.clear') }}
            </a>
        @endif
    </form>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
        <p class="label-upper text-ink-muted" role="status">
            {{ trans_choice('shop.catalogue.results', $products->total(), ['count' => $products->total()]) }}
        </p>
        <span aria-hidden="true" class="hidden h-px flex-1 bg-line sm:block"></span>
    </div>

    @if ($products->isEmpty())
        <div class="card mt-6 flex flex-col items-center px-6 py-14 text-center">
            <span class="h-32 w-24 overflow-hidden rounded-sm opacity-70">
                <x-ui.garment-art variant="boubou" />
            </span>
            <p class="mt-6 font-display text-heading-md">{{ __('shop.catalogue.empty') }}</p>
            <p class="mt-2 max-w-[46ch] text-body-sm text-ink-muted">{{ __('shop.catalogue.empty_hint') }}</p>

            <x-ui.button :href="route('shop.index', $locale)" variant="secondary" class="mt-7">
                {{ __('shop.catalogue.clear') }}
            </x-ui.button>
        </div>
    @else
        <h2 class="sr-only">{{ __('shop.catalogue.products_heading') }}</h2>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <div data-reveal style="--reveal-delay: {{ min($loop->index, 7) * 60 }}ms">
                    <x-ui.product-card :product="$product" />
                </div>
            @endforeach
        </div>

        <div class="mt-14">
            {{ $products->links() }}
        </div>
    @endif
</x-app-layout>