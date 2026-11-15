@props(['tone' => 'neutral'])

@php
    $classes = match ($tone) {
        'success' => 'bg-tint-success text-success',
        'success-filled' => 'bg-success text-canvas',
        'warning' => 'bg-tint-warning text-warning',
        'danger' => 'bg-tint-danger text-danger',
        'info' => 'bg-tint-info text-info',
        'gold' => 'bg-gold text-charcoal shadow-sm',
        'outline' => 'border border-gold/70 bg-canvas/85 text-charcoal backdrop-blur-sm',
        default => 'bg-beige-100 text-ink-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => 'label-upper inline-flex h-6 items-center rounded-full px-2.5 whitespace-nowrap '.$classes]) }}>{{ $slot }}</span>
