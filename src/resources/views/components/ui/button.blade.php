@props(['variant' => 'primary', 'size' => 'default', 'href' => null, 'disabled' => false])

@php
    $classes = 'label-upper inline-flex items-center justify-center gap-2 rounded-xs transition-colors duration-150 ease-brand disabled:opacity-40 '
        . match ($variant) {
            'secondary' => 'border border-charcoal text-charcoal hover:bg-charcoal hover:text-beige active:bg-charcoal-800',
            'on-dark' => 'border border-beige text-beige hover:bg-beige hover:text-charcoal',
            'gold' => 'bg-gold text-charcoal hover:bg-gold-600 active:bg-gold-700',
            'ghost' => 'text-charcoal hover:bg-beige active:bg-beige-200',
            'danger' => 'bg-danger text-canvas hover:brightness-90 active:brightness-75',
            'link' => 'px-0 text-body-md font-normal normal-case tracking-normal underline decoration-gold underline-offset-4 text-ink hover:text-ink-soft',
            default => 'bg-charcoal text-beige hover:bg-charcoal-800 active:bg-charcoal-700',
        }
        . ' '
        . match ($size) {
            'compact' => 'h-10 px-4',
            'checkout' => 'h-14 px-6',
            'link' => '',
            default => 'h-12 px-6',
        };
@endphp

@if ($href && ! $disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button @disabled($disabled) {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
