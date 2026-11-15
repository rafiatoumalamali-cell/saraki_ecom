@props(['label', 'value', 'hint' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'card group relative overflow-hidden p-6 transition-all duration-300 ease-brand hover:-translate-y-0.5 hover:shadow-lift']) }}>
    <span aria-hidden="true" class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/70 to-transparent"></span>

    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="label-upper text-ink-muted">{{ $label }}</p>
            <p class="tabular mt-3 font-display text-display-md leading-none text-ink">{{ $value }}</p>
        </div>

        @if ($icon)
            <span aria-hidden="true" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gold/10 text-gold-700 transition-all duration-300 ease-brand group-hover:bg-gold group-hover:text-charcoal">
                <x-ui.admin-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>

    @if ($hint)
        <p class="mt-3 text-body-sm text-ink-soft">{{ $hint }}</p>
    @endif
</div>
