@props(['label', 'value', 'hint' => null])

<div {{ $attributes->merge(['class' => 'rounded-sm border border-line bg-canvas p-6 shadow-sm']) }}>
    <p class="label-upper text-ink-muted">{{ $label }}</p>
    <p class="mt-2 font-display text-display-md leading-none">{{ $value }}</p>

    @if ($hint)
        <p class="mt-2 text-body-sm text-ink-soft">{{ $hint }}</p>
    @endif
</div>
