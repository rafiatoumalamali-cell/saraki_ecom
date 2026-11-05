@props(['type' => 'info'])

@php
    $tones = match ($type) {
        'success' => ['box' => 'bg-tint-success text-success', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'warning' => ['box' => 'bg-tint-warning text-warning', 'icon' => 'M12 9v3.75m0 3.75h.008v.008H12v-.008M21.73 18H2.27l9.73-16.5L21.73 18Z'],
        'danger' => ['box' => 'bg-tint-danger text-danger', 'icon' => 'M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        default => ['box' => 'bg-tint-info text-info', 'icon' => 'M11.25 11.25h1.5v5.25h.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    };
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-sm p-4 text-body-sm '.$tones['box']]) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tones['icon'] }}" />
    </svg>

    <div class="min-w-0">{{ $slot }}</div>
</div>
