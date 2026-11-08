@php
    $tabs = [
        ['label' => __('admin.appointments.list'), 'href' => route('admin.appointments.index'), 'active' => request()->routeIs('admin.appointments.index')],
        ['label' => __('admin.appointments.calendar'), 'href' => route('admin.appointments.calendar'), 'active' => request()->routeIs('admin.appointments.calendar')],
        ['label' => __('admin.appointments.services'), 'href' => route('admin.services.index'), 'active' => request()->routeIs('admin.services.*')],
        ['label' => __('admin.appointments.schedule'), 'href' => route('admin.schedule.index'), 'active' => request()->routeIs('admin.schedule.*')],
    ];
@endphp

<nav aria-label="{{ __('admin.nav.appointments') }}" class="mb-8 border-b border-line">
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
