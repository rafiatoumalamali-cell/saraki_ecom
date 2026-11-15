<x-admin-layout :title="__('admin.gallery.title')">
    @include('admin.content.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.gallery.subtitle') }}</p>

        <x-ui.button :href="route('admin.content.gallery.create')" size="compact">
            {{ __('admin.gallery.create') }}
        </x-ui.button>
    </div>

    <nav aria-label="{{ __('admin.gallery.filter_label') }}" class="mt-8 flex flex-wrap gap-2">
        <a
            href="{{ route('admin.content.gallery.index') }}"
            @class([
                'label-upper inline-flex h-10 items-center rounded-xs border px-4 transition-colors duration-150 ease-brand',
                'border-charcoal bg-charcoal text-beige' => $category === null,
                'border-line text-ink hover:border-charcoal' => $category !== null,
            ])
            @if ($category === null) aria-current="page" @endif
        >{{ __('admin.gallery.all') }}</a>

        @foreach ($categories as $option)
            <a
                href="{{ route('admin.content.gallery.index', ['category' => $option->value]) }}"
                @class([
                    'label-upper inline-flex h-10 items-center rounded-xs border px-4 transition-colors duration-150 ease-brand',
                    'border-charcoal bg-charcoal text-beige' => $category?->value === $option->value,
                    'border-line text-ink hover:border-charcoal' => $category?->value !== $option->value,
                ])
                @if ($category?->value === $option->value) aria-current="page" @endif
            >{{ $option->label() }}</a>
        @endforeach
    </nav>

    @if ($items->isEmpty())
        <p class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-6 text-body-sm text-ink-muted">
            {{ __('admin.gallery.empty') }}
        </p>
    @else
        <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($items as $item)
                <li class="flex flex-col overflow-hidden rounded-xl border border-line bg-canvas shadow-card">
                    <div class="relative bg-beige-200">
                        <img
                            src="{{ $item->thumbUrl() }}"
                            alt="{{ $item->title_fr }}"
                            class="aspect-square w-full object-cover"
                        >

                        <div class="absolute start-3 top-3">
                            <x-ui.badge :tone="$item->is_published ? 'success' : 'neutral'">
                                {{ $item->is_published ? __('admin.gallery.published') : __('admin.gallery.hidden') }}
                            </x-ui.badge>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <p class="label-upper text-ink-muted">{{ $item->category->label() }}</p>
                        <p class="mt-1 truncate font-semibold text-ink">{{ $item->title_fr }}</p>
                        <p class="truncate text-body-sm text-ink-muted">{{ $item->title_en }}</p>
                        <p class="mt-1 text-body-sm text-ink-muted">
                            {{ __('admin.gallery.sort_order') }} <span class="tabular">{{ $item->sort_order }}</span>
                        </p>

                        <div class="mt-auto flex flex-wrap items-center gap-3 pt-4 text-body-sm">
                            <a href="{{ route('admin.content.gallery.edit', $item) }}" class="link">{{ __('admin.common.edit') }}</a>

                            <form method="POST" action="{{ route('admin.content.gallery.toggle', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="link">
                                    {{ $item->is_published ? __('admin.gallery.hide') : __('admin.gallery.show') }}
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('admin.content.gallery.destroy', $item) }}"
                                data-confirm="{{ __('admin.gallery.delete_confirm', ['name' => $item->title_fr]) }}"
                                onsubmit="return confirm(this.dataset.confirm)"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger underline decoration-danger underline-offset-4 hover:brightness-75">
                                    {{ __('admin.common.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $items->links() }}
        </div>
    @endif
</x-admin-layout>
