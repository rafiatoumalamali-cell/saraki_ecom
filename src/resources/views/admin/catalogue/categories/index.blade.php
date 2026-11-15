<x-admin-layout :title="__('admin.categories.title')">
    @include('admin.catalogue.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.categories.subtitle') }}</p>

        <x-ui.button :href="route('admin.catalogue.categories.create')" size="compact">
            {{ __('admin.categories.create') }}
        </x-ui.button>
    </div>

    @if ($categories->isEmpty())
        <p class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-6 text-body-sm text-ink-muted">
            {{ __('admin.categories.empty') }}
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-xl border border-line bg-canvas shadow-card">
            <table class="w-full min-w-[860px] text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.categories.sort_order') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.categories.category') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.categories.parent') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.categories.products_count') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.common.status') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-right text-ink-muted">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="border-t border-line align-middle transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <span class="tabular w-6 text-ink-muted">{{ $category->sort_order }}</span>

                                    <form method="POST" action="{{ route('admin.catalogue.categories.move', [$category, 'up']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-xs text-ink-muted transition-colors duration-150 ease-brand hover:bg-beige hover:text-charcoal" @disabled($loop->first)>
                                            <span class="sr-only">{{ __('admin.categories.move_up') }}</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.catalogue.categories.move', [$category, 'down']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-xs text-ink-muted transition-colors duration-150 ease-brand hover:bg-beige hover:text-charcoal" @disabled($loop->last)>
                                            <span class="sr-only">{{ __('admin.categories.move_down') }}</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <a href="{{ route('admin.catalogue.categories.edit', $category) }}" class="font-semibold text-ink hover:underline">
                                    {{ $category->name_fr }}
                                </a>
                                <span class="mt-1 block text-ink-muted">{{ $category->name_en }}</span>
                                <span class="mt-1 block font-mono text-body-sm text-ink-muted">{{ $category->slug }}</span>
                            </td>

                            <td class="px-4 py-3 text-ink-soft">
                                {{ $category->parent?->name_fr ?? __('admin.categories.no_parent') }}
                            </td>

                            <td class="px-4 py-3 tabular">{{ $category->products_count }}</td>

                            <td class="px-4 py-3">
                                <x-ui.badge :tone="$category->is_active ? 'success' : 'neutral'">
                                    {{ $category->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-ui.badge>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('admin.catalogue.categories.edit', $category) }}" class="link text-body-sm">
                                        {{ __('admin.common.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('admin.catalogue.categories.toggle', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="link text-body-sm">
                                            {{ $category->is_active ? __('admin.categories.deactivate') : __('admin.categories.activate') }}
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.catalogue.categories.destroy', $category) }}"
                                        data-confirm="{{ __('admin.categories.delete_confirm', ['name' => $category->name_fr]) }}"
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
    @endif
</x-admin-layout>
