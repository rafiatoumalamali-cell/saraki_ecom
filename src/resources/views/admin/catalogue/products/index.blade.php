<x-admin-layout :title="__('admin.products.title')">
    @include('admin.catalogue.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.products.subtitle') }}</p>

        <x-ui.button :href="route('admin.catalogue.products.create')" size="compact">
            {{ __('admin.products.create') }}
        </x-ui.button>
    </div>

    <form method="GET" action="{{ route('admin.catalogue.products.index') }}" class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-4">
        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,13rem)_minmax(0,11rem)_auto] md:items-end">
            <div>
                <x-input-label for="q" :value="__('admin.products.search_label')" />
                <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('admin.products.search_placeholder') }}" class="field mt-1">
            </div>

            <div>
                <x-input-label for="category" :value="__('admin.products.category')" />
                <select id="category" name="category" class="field mt-1">
                    <option value="">{{ __('admin.products.all_categories') }}</option>
                    @foreach ($categories as $option)
                        <option value="{{ $option->id }}" @selected($categoryId === $option->id)>{{ $option->name_fr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="status" :value="__('admin.common.status')" />
                <select id="status" name="status" class="field mt-1">
                    <option value="">{{ __('admin.products.any_status') }}</option>
                    <option value="active" @selected($status === 'active')>{{ __('admin.products.published') }}</option>
                    <option value="inactive" @selected($status === 'inactive')>{{ __('admin.products.hidden') }}</option>
                </select>
            </div>

            <x-primary-button>{{ __('admin.products.search_label') }}</x-primary-button>
        </div>
    </form>

    @if ($products->isEmpty())
        <p class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-6 text-body-sm text-ink-muted">
            {{ $products->total() === 0 && $search === '' && $categoryId === null && $status === ''
                ? __('admin.products.empty')
                : __('admin.products.no_results') }}
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-xl border border-line bg-canvas shadow-card">
            <table class="w-full min-w-[1000px] text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.products.product') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.products.category') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.products.price') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.products.variants_count') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.products.stock') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.common.status') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-right text-ink-muted">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-t border-line align-middle transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-12 shrink-0 overflow-hidden rounded-xs bg-beige-200">
                                        <x-ui.product-media :product="$product" decorative class="h-full w-full object-cover" />
                                    </div>

                                    <div class="min-w-0">
                                        <a href="{{ route('admin.catalogue.products.edit', $product) }}" class="font-semibold text-ink hover:underline">
                                            {{ $product->name_fr }}
                                        </a>
                                        <span class="mt-1 block truncate text-ink-muted">{{ $product->name_en }}</span>
                                        <span class="mt-1 block font-mono text-body-sm text-ink-muted">{{ $product->base_sku }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-ink-soft">{{ $product->category->name_fr }}</td>

                            <td class="px-4 py-3 tabular">{{ money($product->base_price) }}</td>

                            <td class="px-4 py-3 tabular">{{ $product->variants_count }}</td>

                            <td class="px-4 py-3 tabular">
                                @if ($product->isSoldOut())
                                    <x-ui.badge tone="danger">{{ __('admin.products.sold_out') }}</x-ui.badge>
                                @else
                                    {{ $product->totalStock() }}
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-col items-start gap-2">
                                    <x-ui.badge :tone="$product->is_active ? 'success' : 'neutral'">
                                        {{ $product->is_active ? __('admin.products.published') : __('admin.products.hidden') }}
                                    </x-ui.badge>

                                    @if ($product->is_featured)
                                        <x-ui.badge tone="outline">{{ __('admin.products.featured') }}</x-ui.badge>
                                    @endif

                                    @if ($product->isMadeToMeasure())
                                        <x-ui.badge tone="gold">{{ __('admin.products.type_made') }}</x-ui.badge>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('admin.catalogue.products.edit', $product) }}" class="link text-body-sm">
                                        {{ __('admin.common.edit') }}
                                    </a>
                                    <a href="{{ route('admin.catalogue.products.images.index', $product) }}" class="link text-body-sm">
                                        {{ __('admin.products.manage_images') }}
                                    </a>
                                    <a href="{{ route('admin.catalogue.products.variants.index', $product) }}" class="link text-body-sm">
                                        {{ __('admin.products.manage_variants') }}
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.catalogue.products.destroy', $product) }}"
                                        data-confirm="{{ __('admin.products.delete_confirm', ['name' => $product->name_fr]) }}"
                                        onsubmit="return confirm(this.dataset.confirm)"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                                            {{ __('admin.common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</x-admin-layout>
