@props(['product', 'loading' => 'lazy'])

@php
    $src = $product->imageUrl();
    $name = $product->translate('name');
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $name }}"
        loading="{{ $loading }}"
        decoding="async"
        {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}
    >
@else
    {{-- A product can go live before the atelier uploads its photos. --}}
    <span {{ $attributes->merge(['class' => 'flex h-full w-full items-center justify-center bg-beige-200']) }}>
        <svg aria-hidden="true" class="h-10 w-10 text-ink-muted/50" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.09 2.09M2.25 19.5h19.5M3.75 3.75h16.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z" />
        </svg>
        <span class="sr-only">{{ __('shop.card.no_image') }}</span>
    </span>
@endif
