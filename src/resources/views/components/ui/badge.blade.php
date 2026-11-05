@props(['tone' => 'neutral'])

@php
    $classes = match ($tone) {
        'success' => 'bg-tint-success text-success',
        'success-filled' => 'bg-success text-canvas',
        'warning' => 'bg-tint-warning text-warning',
        'danger' => 'bg-tint-danger text-danger',
        'info' => 'bg-tint-info text-info',
        'outline' => 'border border-gold text-charcoal',
        default => 'bg-beige text-ink-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => 'label-upper inline-flex h-6 items-center rounded-xs px-2 whitespace-nowrap '.$classes]) }}>{{ $slot }}</span>
