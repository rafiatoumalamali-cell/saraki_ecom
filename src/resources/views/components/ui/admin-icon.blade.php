@props(['name'])

{{-- Hand-drawn geometric icons on a 24x24 grid; markup is static, never user input. --}}
@php
    $icons = [
        'dashboard' => '<rect x="3.5" y="3.5" width="7" height="7"/><rect x="13.5" y="3.5" width="7" height="7"/><rect x="3.5" y="13.5" width="7" height="7"/><rect x="13.5" y="13.5" width="7" height="7"/>',
        'orders' => '<path d="M4.5 8h15l-1.5 12H6L4.5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'catalogue' => '<path d="M4 10.5 10.5 4H20v9.5L13.5 20a2 2 0 0 1-2.8 0L4 13.3a2 2 0 0 1 0-2.8Z"/><circle cx="16" cy="8" r="1.5"/>',
        'appointments' => '<rect x="3.5" y="6" width="17" height="14.5" rx="1"/><path d="M3.5 11h17M8 3.5V8M16 3.5V8"/>',
        'customers' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
        'content' => '<path d="M6 3.5h8L18.5 8v12.5H6Z"/><path d="M13.5 3.5V8.5H18.5M9 12.5h6M9 16h6"/>',
        'messages' => '<rect x="3.5" y="5.5" width="17" height="13" rx="1"/><path d="m4 6.5 8 6 8-6"/>',
        'settings' => '<circle cx="12" cy="12" r="3.5"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/>',
        'store' => '<path d="M4 9.5V20h16V9.5"/><path d="M2.5 9.5 5 4h14l2.5 5.5ZM9.5 20v-6h5v6"/>',
    ];
@endphp

<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    {!! $icons[$name] ?? '<circle cx="12" cy="12" r="8"/>' !!}
</svg>
