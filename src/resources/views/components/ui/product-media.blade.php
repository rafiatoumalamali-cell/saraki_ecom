@props(['product', 'loading' => 'lazy', 'decorative' => false, 'thumb' => true])

@php
    $src = $thumb ? $product->imageThumbUrl() : $product->imageUrl();
    $name = $product->translate('name');
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $decorative ? '' : $name }}"
        loading="{{ $loading }}"
        decoding="async"
        {{ $attributes->merge(['class' => 'h-full w-full bg-beige-100 object-cover']) }}
    >
@else
    {{-- A product can go live before the atelier uploads its photos. --}}
    @php
        $variant = match ($product->category?->slug) {
            'caftans' => 'caftan',
            'ensembles-wax' => 'ensemble',
            'robes-de-soiree' => 'soiree',
            'pret-a-porter' => 'pret',
            'accessoires' => 'accessoires',
            default => 'boubou',
        };
    @endphp

    <span {{ $attributes->merge(['class' => 'relative flex h-full w-full items-center justify-center overflow-hidden']) }}>
        <x-ui.garment-art :variant="$variant" />
        <span class="label-upper absolute inset-x-0 bottom-0 bg-canvas/80 py-2 text-center text-[10px] text-ink-muted backdrop-blur-sm">{{ __('shop.card.no_image') }}</span>
    </span>
@endif
