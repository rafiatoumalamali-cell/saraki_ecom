<x-admin-layout :title="__('admin.images.title')">
    @include('admin.catalogue.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.catalogue.products.edit', $product) }}" class="link text-body-sm">{{ __('admin.images.back_to_product') }}</a>
            <h2 class="mt-2 font-display text-heading-md">{{ $product->name_fr }}</h2>
            <p class="mt-1 max-w-prose text-body-md text-ink-soft">{{ __('admin.images.subtitle') }}</p>
        </div>

        <a href="{{ route('admin.catalogue.products.variants.index', $product) }}" class="link text-body-sm">
            {{ __('admin.products.manage_variants') }}
        </a>
    </div>

    <form
        method="POST"
        action="{{ route('admin.catalogue.products.images.store', $product) }}"
        enctype="multipart/form-data"
        class="mt-8 rounded-sm border border-line bg-canvas p-6"
    >
        @csrf

        <h3 class="font-display text-heading-sm">{{ __('admin.images.upload') }}</h3>

        <div class="mt-4 grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="images" :value="__('admin.images.upload')" />
                <input
                    id="images"
                    name="images[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    required
                    class="mt-1 block w-full text-body-sm text-ink file:me-4 file:rounded-xs file:border-0 file:bg-charcoal file:px-4 file:py-2 file:text-label file:font-semibold file:uppercase file:tracking-[0.08em] file:text-beige"
                >
                <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.images.upload_hint') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('images')" />
                <x-input-error class="mt-2" :messages="$errors->get('images.*')" />
            </div>

            <div>
                <x-input-label for="alt_fr" :value="__('admin.images.alt_fr')" />
                <x-text-input id="alt_fr" name="alt_fr" type="text" class="mt-1" :value="old('alt_fr')" placeholder="{{ $product->name_fr }}" />
                <x-input-error class="mt-2" :messages="$errors->get('alt_fr')" />
            </div>

            <div>
                <x-input-label for="alt_en" :value="__('admin.images.alt_en')" />
                <x-text-input id="alt_en" name="alt_en" type="text" class="mt-1" :value="old('alt_en')" placeholder="{{ $product->name_en }}" />
                <x-input-error class="mt-2" :messages="$errors->get('alt_en')" />
            </div>
        </div>

        <p class="mt-4 text-body-sm text-ink-muted">{{ __('admin.images.alt_hint') }}</p>

        <x-ui.button type="submit" size="compact" class="mt-6">{{ __('admin.images.upload') }}</x-ui.button>
    </form>

    @if ($images->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('admin.images.empty') }}
        </p>
    @else
        <ul class="mt-8 space-y-4">
            @foreach ($images as $image)
                <li class="rounded-sm border border-line bg-canvas p-4">
                    <div class="grid gap-4 md:grid-cols-[7rem_minmax(0,1fr)_auto] md:items-start">
                        <div class="relative overflow-hidden rounded-xs bg-beige-200">
                            <img src="{{ $image->url() }}" alt="{{ $image->altText() }}" loading="lazy" class="aspect-[3/4] w-full object-cover">

                            @if ($image->is_primary)
                                <span class="label-upper absolute start-2 top-2 rounded-xs bg-charcoal px-2 py-1 text-beige">
                                    {{ __('admin.images.primary') }}
                                </span>
                            @endif
                        </div>

                        <form
                            method="POST"
                            action="{{ route('admin.catalogue.products.images.update', [$product, $image]) }}"
                            class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_6rem_auto] sm:items-end"
                        >
                            @csrf
                            @method('PATCH')

                            <div>
                                <x-input-label :for="'alt_fr_'.$image->id" :value="__('admin.images.alt_fr')" />
                                <x-text-input :id="'alt_fr_'.$image->id" name="alt_fr" type="text" class="mt-1" :value="$image->alt_fr" />
                            </div>

                            <div>
                                <x-input-label :for="'alt_en_'.$image->id" :value="__('admin.images.alt_en')" />
                                <x-text-input :id="'alt_en_'.$image->id" name="alt_en" type="text" class="mt-1" :value="$image->alt_en" />
                            </div>

                            <div>
                                <x-input-label :for="'sort_'.$image->id" :value="__('admin.images.position')" />
                                <x-text-input :id="'sort_'.$image->id" name="sort_order" type="number" min="0" max="9999" class="mt-1 tabular" :value="$image->sort_order" />
                            </div>

                            <x-ui.button type="submit" variant="secondary" size="compact">{{ __('admin.images.save') }}</x-ui.button>

                            <x-input-error class="sm:col-span-4" :messages="$errors->get('alt_fr')" />
                            <x-input-error class="sm:col-span-4" :messages="$errors->get('alt_en')" />
                        </form>

                        <div class="flex flex-wrap items-center gap-3 md:flex-col md:items-stretch">
                            @unless ($image->is_primary)
                                <form method="POST" action="{{ route('admin.catalogue.products.images.primary', [$product, $image]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="link text-body-sm">{{ __('admin.images.make_primary') }}</button>
                                </form>
                            @endunless

                            <form
                                method="POST"
                                action="{{ route('admin.catalogue.products.images.destroy', [$product, $image]) }}"
                                data-confirm="{{ __('admin.images.remove_confirm') }}"
                                onsubmit="return confirm(this.dataset.confirm)"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                                    {{ __('admin.images.remove') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-admin-layout>
