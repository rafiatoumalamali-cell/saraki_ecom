@props(['user', 'size' => 'md'])

@php
    $shapes = [
        'sm' => 'h-8 w-8 text-label',
        'md' => 'h-12 w-12 text-body-md',
        'lg' => 'h-20 w-20 text-heading-sm',
        'xl' => 'h-28 w-28 text-display-md',
    ];

    $shape = $shapes[$size] ?? $shapes['md'];
@endphp

@if ($url = $user->avatarUrl())
    <img
        src="{{ $url }}"
        alt="{{ $user->name }}"
        loading="lazy"
        {{ $attributes->merge(['class' => $shape.' rounded-full border border-line bg-canvas object-cover']) }}
    />
@else
    <span
        aria-hidden="true"
        {{ $attributes->merge(['class' => $shape.' flex shrink-0 items-center justify-center rounded-full border border-line bg-beige-200 font-display font-semibold uppercase text-ink-soft']) }}
    >{{ $user->initials() }}</span>
@endif
