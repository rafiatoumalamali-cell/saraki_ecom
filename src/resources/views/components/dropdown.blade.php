@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1'])

@php
    $alignmentClasses = match ($align) {
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        default => 'ltr:origin-top-right rtl:origin-top-left end-0',
    };

    $width = match ($width) {
        '48' => 'w-48',
        default => $width,
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open" @keydown.enter.prevent="open = ! open" @keydown.escape="open = false">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition duration-300 ease-brand"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition duration-150 ease-brand"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-50 mt-2 {{ $width }} rounded-sm border border-line bg-canvas shadow-md {{ $alignmentClasses }}"
        style="display: none;"
        @click="open = false"
    >
        <div {{ $attributes->merge(['class' => $contentClasses]) }}>
            {{ $content }}
        </div>
    </div>
</div>
