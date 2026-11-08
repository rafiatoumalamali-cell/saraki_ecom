@php
    $tabs = [
        ['label' => __('admin.catalogue.products'), 'href' => route('admin.catalogue.products.index'), 'active' => request()->routeIs('admin.catalogue.products.*')],
        ['label' => __('admin.catalogue.categories'), 'href' => route('admin.catalogue.categories.index'), 'active' => request()->routeIs('admin.catalogue.categories.*')],
    ];
@endphp

<nav aria-label="{{ __('admin.nav.catalogue') }}" class="mb-8 border-b border-line">
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
