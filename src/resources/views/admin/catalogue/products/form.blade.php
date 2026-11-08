@php
    $editing = $product->exists;
@endphp

<x-admin-layout :title="$editing ? __('admin.products.edit') : __('admin.products.create')">
    @include('admin.catalogue.nav')

    <a href="{{ route('admin.catalogue.products.index') }}" class="link text-body-sm">{{ __('admin.products.back_to_list') }}</a>

    <form
        method="POST"
        action="{{ $editing ? route('admin.catalogue.products.update', $product) : route('admin.catalogue.products.store') }}"
        class="mt-6"
    >
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.products.product') }}</h2>

                    <div class="mt-4 grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="name_fr" :value="__('admin.products.name_fr')" />
                            <x-text-input id="name_fr" name="name_fr" type="text" class="mt-1" :value="old('name_fr', $product->name_fr)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name_fr')" />
                        </div>

                        <div>
                            <x-input-label for="name_en" :value="__('admin.products.name_en')" />
                            <x-text-input id="name_en" name="name_en" type="text" class="mt-1" :value="old('name_en', $product->name_en)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name_en')" />
                        </div>

                        <div>
                            <x-input-label for="slug" :value="__('admin.products.slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1" :value="old('slug', $product->slug)" />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.products.slug_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>

                        <div>
                            <x-input-label for="category_id" :value="__('admin.products.category')" />
                            <select id="category_id" name="category_id" class="field mt-1" required>
                                <option value="">{{ __('admin.products.choose_category') }}</option>
                                @foreach ($categories as $option)
                                    <option value="{{ $option->id }}" @selected((int) old('category_id', $product->category_id) === $option->id)>
                                        {{ $option->name_fr }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.products.price') }}</h2>

                    <div class="mt-4 grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="base_price" :value="__('admin.products.base_price')" />
                            <x-text-input id="base_price" name="base_price" type="text" inputmode="decimal" class="mt-1 tabular" :value="old('base_price', $product->base_price)" required />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.products.base_price_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('base_price')" />
                        </div>

                        <div>
                            <x-input-label for="base_sku" :value="__('admin.products.base_sku')" />
                            <x-text-input id="base_sku" name="base_sku" type="text" class="mt-1 font-mono" :value="old('base_sku', $product->base_sku)" required />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.products.base_sku_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('base_sku')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('shop.product.details') }}</h2>

                    <div class="mt-4 space-y-6">
                        <div>
                            <x-input-label for="description_fr" :value="__('admin.products.description_fr')" />
                            <textarea id="description_fr" name="description_fr" rows="5" class="field mt-1">{{ old('description_fr', $product->description_fr) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description_fr')" />
                        </div>

                        <div>
                            <x-input-label for="description_en" :value="__('admin.products.description_en')" />
                            <textarea id="description_en" name="description_en" rows="5" class="field mt-1">{{ old('description_en', $product->description_en) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description_en')" />
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.common.status') }}</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <x-input-label for="is_active" :value="__('admin.products.status')" />
                            <input type="hidden" name="is_active" value="0">
                            <label for="is_active" class="mt-2 flex items-center gap-3">
                                <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold" @checked(old('is_active', $product->is_active))>
                                <span class="text-body-md text-ink">{{ __('admin.products.visibility') }}</span>
                            </label>
                            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                        </div>

                        <div>
                            <input type="hidden" name="is_featured" value="0">
                            <label for="is_featured" class="flex items-center gap-3">
                                <input id="is_featured" name="is_featured" type="checkbox" value="1" class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold" @checked(old('is_featured', $product->is_featured))>
                                <span class="text-body-md text-ink">{{ __('admin.products.featured') }}</span>
                            </label>
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.products.featured_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('is_featured')" />
                        </div>
                    </div>
                </section>

                @if ($editing)
                    <section class="rounded-sm border border-line bg-canvas p-6">
                        <ul class="space-y-3 text-body-sm">
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-ink-muted">{{ __('admin.products.manage_images') }}</span>
                                <a href="{{ route('admin.catalogue.products.images.index', $product) }}" class="link">
                                    {{ $product->images->count() }}
                                </a>
                            </li>
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-ink-muted">{{ __('admin.products.manage_variants') }}</span>
                                <a href="{{ route('admin.catalogue.products.variants.index', $product) }}" class="link">
                                    {{ $product->variants->count() }}
                                </a>
                            </li>
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-ink-muted">{{ __('admin.products.view_in_shop') }}</span>
                                <a href="{{ $product->url() }}" class="link" target="_blank" rel="noopener">
                                    {{ __('shop.card.view') }}
                                </a>
                            </li>
                        </ul>
                    </section>
                @endif

                <div class="flex flex-col gap-3">
                    <x-ui.button type="submit">{{ __('admin.products.save') }}</x-ui.button>
                    <a href="{{ route('admin.catalogue.products.index') }}" class="label-upper text-center text-ink-muted hover:text-ink">
                        {{ __('admin.common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        <form
            method="POST"
            action="{{ route('admin.catalogue.products.destroy', $product) }}"
            class="mt-10 border-t border-line pt-6"
            data-confirm="{{ __('admin.products.delete_confirm', ['name' => $product->name_fr]) }}"
            onsubmit="return confirm(this.dataset.confirm)"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                {{ __('admin.products.delete') }}
            </button>
        </form>
    @endif
</x-admin-layout>
