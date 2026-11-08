@props(['tone' => 'dark'])

@php
    $carts = app(\App\Services\CartService::class);
    $count = $carts->totals($carts->current())['count'];
@endphp

<a
    href="{{ route('cart.index', app()->getLocale()) }}"
    @class([
        'label-upper relative flex h-11 items-center gap-2 rounded-xs px-3 transition-colors duration-150 ease-brand',
        'text-beige hover:bg-charcoal-700' => $tone === 'dark',
        'text-charcoal hover:bg-beige' => $tone === 'light',
    ])
    @if (request()->routeIs('cart.*')) aria-current="page" @endif
>
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
    </svg>

    <span class="tabular">{{ $count }}</span>
    <span class="sr-only">{{ __('shop.cart.mini_label') }}</span>
</a>
