@php
    $tabs = [
        ['label' => __('admin.content.tabs.overview'), 'href' => route('admin.content.index'), 'active' => request()->routeIs('admin.content.index')],
        ['label' => __('admin.content.tabs.gallery'), 'href' => route('admin.content.gallery.index'), 'active' => request()->routeIs('admin.content.gallery.*')],
        ['label' => __('admin.content.tabs.posts'), 'href' => route('admin.content.posts.index'), 'active' => request()->routeIs('admin.content.posts.*')],
        ['label' => __('admin.content.tabs.pages'), 'href' => route('admin.content.pages.index'), 'active' => request()->routeIs('admin.content.pages.*')],
    ];
@endphp

<nav aria-label="{{ __('admin.nav.content') }}" class="mb-8 border-b border-line">
    <ul class="flex flex-wrap gap-1">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ $tab['href'] }}"
                    @class([
                        'label-upper inline-block border-b-2 px-4 py-3 transition-colors duration-150 ease-brand',
                        'border-charcoal text-charcoal' => $tab['active'],
                        'border-transparent text-ink-muted hover:text-charcoal' => ! $tab['active'],
                    ])
                    @if ($tab['active']) aria-current="page" @endif
                >{{ $tab['label'] }}</a>
            </li>
        @endforeach
    </ul>
</nav>
