<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots'] ?? null"
        :alternates="$seo['alternates']"
    />

    <nav aria-label="Breadcrumb" class="text-body-sm text-ink-muted">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home', app()->getLocale()) }}" class="link">{{ __('ui.home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li>
                @if ($category)
                    <a href="{{ route('shop.index', app()->getLocale()) }}" class="link">{{ __('ui.catalogue') }}</a>
                @else
                    <span aria-current="page">{{ __('ui.catalogue') }}</span>
                @endif
            </li>
            @if ($category)
                <li aria-hidden="true">/</li>
                <li><span aria-current="page">{{ $category->translate('name') }}</span></li>
            @endif
        </ol>
    </nav>

    <header class="mt-8">
        <p class="label-upper text-gold-700">{{ __('shop.catalogue.eyebrow') }}</p>

        <h1 class="mt-3 font-display text-display-md md:text-display-lg">
            @if ($term !== '')
                {{ __('shop.search.heading', ['term' => $term]) }}
            @else
                {{ $category?->translate('name') ?? __('shop.catalogue.title') }}
            @endif
        </h1>

        <p class="mt-4 max-w-prose text-body-md text-ink-soft">
            @if ($category)
                {{ __('shop.catalogue.category_description', ['name' => $category->translate('name')]) }}
            @else
                {{ __('shop.catalogue.description') }}
            @endif
        </p>
    </header>

    <form
        method="GET"
        action="{{ route('shop.index', app()->getLocale()) }}"
        class="mt-10 rounded-sm border border-line bg-canvas p-4 md:p-6"
        role="search"
    >
        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,13rem)_minmax(0,13rem)_auto] md:items-end">
            <div>
                <x-input-label for="q" :value="__('shop.catalogue.search_label')" />
                <input
                    id="q"
                    name="q"
                    type="search"
                    value="{{ $term }}"
                    placeholder="{{ __('shop.catalogue.search_placeholder') }}"
                    class="field mt-1"
                >
            </div>

            <div>
                <x-input-label for="category" :value="__('shop.catalogue.category_label')" />
                <select id="category" name="category" class="field mt-1">
                    <option value="">{{ __('shop.catalogue.all_categories') }}</option>
                    @foreach ($categories as $option)
                        <option value="{{ $option->slug }}" @selected($category?->id === $option->id)>
                            {{ $option->translate('name') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="sort" :value="__('shop.catalogue.sort_label')" />
                <select id="sort" name="sort" class="field mt-1">
                    @foreach ($sorts as $key => $label)
                        <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <x-primary-button>{{ __('shop.catalogue.search_submit') }}</x-primary-button>
        </div>

        @if ($term !== '' || $category !== null)
            <a href="{{ route('shop.index', app()->getLocale()) }}" class="link mt-4 inline-block text-body-sm">
                {{ __('shop.catalogue.clear') }}
            </a>
        @endif
    </form>

    <p class="label-upper mt-8 text-ink-muted" role="status">
        {{ trans_choice('shop.catalogue.results', $products->total(), ['count' => $products->total()]) }}
    </p>

    @if ($products->isEmpty())
        <div class="mt-6 rounded-sm border border-line bg-canvas p-10 text-center">
            <p class="font-display text-heading-sm">{{ __('shop.catalogue.empty') }}</p>
            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.catalogue.empty_hint') }}</p>

            <x-ui.button :href="route('shop.index', app()->getLocale())" variant="secondary" size="compact" class="mt-6">
                {{ __('shop.catalogue.clear') }}
            </x-ui.button>
        </div>
    @else
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <x-ui.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    @endif
</x-app-layout>
