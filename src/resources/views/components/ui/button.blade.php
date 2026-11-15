@props(['variant' => 'primary', 'size' => 'default', 'href' => null, 'disabled' => false])

@php
    $classes = 'label-upper inline-flex items-center justify-center gap-2 rounded-sm transition-all duration-200 ease-brand active:scale-[.98] disabled:pointer-events-none disabled:opacity-40 disabled:shadow-none '
        . match ($variant) {
            'secondary' => 'border border-charcoal/25 bg-canvas text-charcoal hover:-translate-y-0.5 hover:border-charcoal hover:bg-charcoal hover:text-beige hover:shadow-md',
            'on-dark' => 'border border-beige/40 bg-charcoal/50 text-beige backdrop-blur-sm hover:-translate-y-0.5 hover:border-beige hover:bg-beige hover:text-charcoal',
            'gold' => 'bg-gradient-to-b from-gold to-gold-600 text-charcoal shadow-sm hover:-translate-y-0.5 hover:shadow-md active:from-gold-600 active:to-gold-700',
            'ghost' => 'text-charcoal hover:bg-beige-100 active:bg-beige-200',
            'danger' => 'bg-danger text-canvas shadow-sm hover:brightness-90 active:brightness-75',
            'link' => 'px-0 text-body-md font-semibold normal-case tracking-normal underline decoration-gold decoration-2 underline-offset-4 text-ink hover:text-gold-700 active:scale-100',
            default => 'bg-gradient-to-b from-charcoal to-charcoal-800 text-beige shadow-sm hover:-translate-y-0.5 hover:shadow-md active:from-charcoal-800 active:to-charcoal-700',
        }
        . ' '
        . match ($size) {
            'compact' => 'h-10 px-4',
            'checkout' => 'h-14 px-7',
            'hero' => 'h-14 px-8 text-[13px]',
            'link' => '',
            default => 'h-12 px-6',
        };
@endphp

@if ($href && ! $disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button @disabled($disabled) {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif