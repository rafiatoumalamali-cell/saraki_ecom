<x-admin-layout :title="__('admin.variants.title')">
    @include('admin.catalogue.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.catalogue.products.edit', $product) }}" class="link text-body-sm">{{ __('admin.variants.back_to_product') }}</a>
            <h2 class="mt-2 font-display text-heading-md">{{ $product->name_fr }}</h2>
            <p class="mt-1 max-w-prose text-body-md text-ink-soft">{{ __('admin.variants.subtitle') }}</p>
        </div>

        <a href="{{ route('admin.catalogue.products.images.index', $product) }}" class="link text-body-sm">
            {{ __('admin.products.manage_images') }}
        </a>
    </div>

    @if ($variants->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('admin.variants.empty') }}
        </p>
    @else
        <form method="POST" action="{{ route('admin.catalogue.products.variants.batch', $product) }}" class="mt-8 rounded-sm border border-line bg-canvas">
            @csrf
            @method('PATCH')

            <div class="flex flex-wrap items-baseline justify-between gap-3 border-b border-line px-6 py-4">
                <h3 class="font-display text-heading-sm">{{ __('admin.variants.title') }}</h3>
                <p class="text-body-sm text-ink-muted">
                    {{ __('admin.variants.stock_total') }}:
                    <span class="tabular font-semibold text-charcoal">{{ $variants->sum('stock_qty') }}</span>
                </p>
            </div>

            {{-- Column captions only make sense once the row turns into a table. --}}
            <div class="hidden gap-4 border-b border-line bg-beige-50 px-6 py-3 md:grid md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_6rem_7rem_5rem_4rem]">
                <span class="label-upper text-ink-muted">{{ __('admin.variants.size') }}</span>
                <span class="label-upper text-ink-muted">{{ __('admin.variants.color') }}</span>
                <span class="label-upper text-ink-muted">{{ __('admin.variants.sku') }}</span>
                <span class="label-upper text-ink-muted">{{ __('admin.variants.stock') }}</span>
                <span class="label-upper text-ink-muted">{{ __('admin.variants.price_delta') }}</span>
                <span class="label-upper text-ink-muted">{{ __('admin.variants.available') }}</span>
                <span class="label-upper text-ink-muted">{{ __('admin.variants.remove') }}</span>
            </div>

            <ul class="divide-y divide-line">
                @foreach ($variants as $variant)
                    <li class="grid gap-4 px-6 py-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_6rem_7rem_5rem_4rem] md:items-center">
                        <div>
                            <label for="size_{{ $variant->id }}" class="label-upper text-ink-muted md:hidden">{{ __('admin.variants.size') }}</label>
                            <x-text-input
                                :id="'size_'.$variant->id"
                                :name="'variants['.$variant->id.'][size]'"
                                type="text"
                                class="mt-1 md:mt-0"
                                :value="$variant->size"
                            />
                        </div>

                        <div>
                            <label for="color_{{ $variant->id }}" class="label-upper text-ink-muted md:hidden">{{ __('admin.variants.color') }}</label>
                            <x-text-input
                                :id="'color_'.$variant->id"
                                :name="'variants['.$variant->id.'][color]'"
                                type="text"
                                class="mt-1 md:mt-0"
                                :value="$variant->color"
                            />
                        </div>

                        <div>
                            <label for="sku_{{ $variant->id }}" class="label-upper text-ink-muted md:hidden">{{ __('admin.variants.sku') }}</label>
                            <x-text-input
                                :id="'sku_'.$variant->id"
                                :name="'variants['.$variant->id.'][sku]'"
                                type="text"
                                class="mt-1 md:mt-0"
                                :value="$variant->sku"
                                required
                            />
                        </div>

                        <div>
                            <label for="stock_{{ $variant->id }}" class="label-upper text-ink-muted md:hidden">{{ __('admin.variants.stock') }}</label>
                            <x-text-input
                                :id="'stock_'.$variant->id"
                                :name="'variants['.$variant->id.'][stock_qty]'"
                                type="number"
                                min="0"
                                max="1000000"
                                class="mt-1 tabular md:mt-0"
                                :value="$variant->stock_qty"
                                required
                            />
                        </div>

                        <div>
                            <label for="delta_{{ $variant->id }}" class="label-upper text-ink-muted md:hidden">{{ __('admin.variants.price_delta') }}</label>
                            <x-text-input
                                :id="'delta_'.$variant->id"
                                :name="'variants['.$variant->id.'][price_delta]'"
                                type="text"
                                inputmode="decimal"
                                class="mt-1 tabular md:mt-0"
                                :value="$variant->price_delta"
                                required
                            />
                        </div>

                        <div>
                            <label for="active_{{ $variant->id }}" class="flex items-center gap-2 text-body-sm text-ink md:justify-start">
                                <input
                                    id="active_{{ $variant->id }}"
                                    name="variants[{{ $variant->id }}][is_active]"
                                    type="checkbox"
                                    value="1"
                                    @checked($variant->is_active)
                                    class="rounded-xs border-line-strong text-charcoal focus:ring-gold"
                                >
                                <span class="md:hidden">{{ __('admin.variants.available') }}</span>
                            </label>
                        </div>

                        <div>
                            <label for="remove_{{ $variant->id }}" class="flex items-center gap-2 text-body-sm text-danger">
                                <input
                                    id="remove_{{ $variant->id }}"
                                    name="removed[]"
                                    type="checkbox"
                                    value="{{ $variant->id }}"
                                    class="rounded-xs border-line-strong text-danger focus:ring-danger"
                                >
                                <span class="md:hidden">{{ __('admin.variants.remove') }}</span>
                            </label>
                        </div>

                        <p class="text-body-sm text-ink-muted md:hidden">
                            {{ __('admin.variants.price') }}:
                            <span class="tabular font-semibold text-charcoal">{{ money($variant->price) }}</span>
                        </p>
                    </li>
                @endforeach
            </ul>

            <x-input-error class="px-6 pt-4" :messages="$errors->get('variants')" />
            <x-input-error class="px-6 pt-4" :messages="$errors->get('variants.*.stock_qty')" />
            <x-input-error class="px-6 pt-4" :messages="$errors->get('variants.*.price_delta')" />
            <x-input-error class="px-6 pt-4" :messages="$errors->get('variants.*.sku')" />

            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-line px-6 py-4">
                <p class="max-w-prose text-body-sm text-ink-muted">{{ __('admin.variants.remove_confirm') }}</p>
                <x-ui.button type="submit" size="compact">{{ __('admin.variants.save') }}</x-ui.button>
            </div>
        </form>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-2 lg:items-start">
        <section class="rounded-sm border border-line bg-canvas p-6">
            <h3 class="font-display text-heading-sm">{{ __('admin.variants.add') }}</h3>

            <form method="POST" action="{{ route('admin.catalogue.products.variants.store', $product) }}" class="mt-4">
                @csrf

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="new_size" :value="__('admin.variants.size')" />
                        <x-text-input id="new_size" name="size" type="text" class="mt-1" :value="old('size')" />
                    </div>

                    <div>
                        <x-input-label for="new_color" :value="__('admin.variants.color')" />
                        <x-text-input id="new_color" name="color" type="text" class="mt-1" :value="old('color')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="new_sku" :value="__('admin.variants.sku')" />
                        <x-text-input id="new_sku" name="sku" type="text" class="mt-1" :value="old('sku')" />
                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.variants.sku_hint') }}</p>
                    </div>

                    <div>
                        <x-input-label for="new_stock_qty" :value="__('admin.variants.stock')" />
                        <x-text-input id="new_stock_qty" name="stock_qty" type="number" min="0" max="1000000" class="mt-1 tabular" :value="old('stock_qty', 0)" required />
                    </div>

                    <div>
                        <x-input-label for="new_price_delta" :value="__('admin.variants.price_delta')" />
                        <x-text-input id="new_price_delta" name="price_delta" type="text" inputmode="decimal" class="mt-1 tabular" :value="old('price_delta', '0')" required />
                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.variants.price_delta_hint') }}</p>
                    </div>
                </div>

                <label for="new_is_active" class="mt-4 flex items-center gap-2 text-body-sm text-ink">
                    <input
                        id="new_is_active"
                        name="is_active"
                        type="checkbox"
                        value="1"
                        @checked(old('is_active', true))
                        class="rounded-xs border-line-strong text-charcoal focus:ring-gold"
                    >
                    {{ __('admin.variants.available') }}
                </label>

                <p class="mt-4 text-body-sm text-ink-muted">{{ __('admin.variants.default_hint') }}</p>

                <x-input-error class="mt-4" :messages="$errors->get('size')" />
                <x-input-error class="mt-2" :messages="$errors->get('color')" />
                <x-input-error class="mt-2" :messages="$errors->get('sku')" />
                <x-input-error class="mt-2" :messages="$errors->get('stock_qty')" />
                <x-input-error class="mt-2" :messages="$errors->get('price_delta')" />

                <x-ui.button type="submit" variant="secondary" size="compact" class="mt-6">{{ __('admin.variants.add') }}</x-ui.button>
            </form>
        </section>

        <section class="rounded-sm border border-line bg-canvas p-6">
            <h3 class="font-display text-heading-sm">{{ __('admin.variants.generate_title') }}</h3>
            <p class="mt-1 max-w-prose text-body-sm text-ink-soft">{{ __('admin.variants.generate_text') }}</p>

            <form method="POST" action="{{ route('admin.catalogue.products.variants.generate', $product) }}" class="mt-4">
                @csrf

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="sizes" :value="__('admin.variants.sizes')" />
                        <x-text-input id="sizes" name="sizes" type="text" class="mt-1" :value="old('sizes', 'S, M, L, XL')" required />
                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.variants.sizes_hint') }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="colors" :value="__('admin.variants.colors')" />
                        <x-text-input id="colors" name="colors" type="text" class="mt-1" :value="old('colors')" required />
                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.variants.colors_hint') }}</p>
                    </div>

                    <div>
                        <x-input-label for="gen_stock_qty" :value="__('admin.variants.stock')" />
                        <x-text-input id="gen_stock_qty" name="stock_qty" type="number" min="0" max="1000000" class="mt-1 tabular" :value="old('stock_qty', 0)" required />
                    </div>

                    <div>
                        <x-input-label for="gen_price_delta" :value="__('admin.variants.price_delta')" />
                        <x-text-input id="gen_price_delta" name="price_delta" type="text" inputmode="decimal" class="mt-1 tabular" :value="old('price_delta', '0')" required />
                    </div>
                </div>

                <x-input-error class="mt-4" :messages="$errors->get('sizes')" />
                <x-input-error class="mt-2" :messages="$errors->get('colors')" />

                <x-ui.button type="submit" variant="secondary" size="compact" class="mt-6">{{ __('admin.variants.generate') }}</x-ui.button>
            </form>
        </section>
    </div>
</x-admin-layout>
