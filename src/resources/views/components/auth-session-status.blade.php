@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-md border border-charcoal/5 bg-tint-success p-4 text-body-sm text-success shadow-sm']) }}>
        {{ $status }}
    </div>
@endif
