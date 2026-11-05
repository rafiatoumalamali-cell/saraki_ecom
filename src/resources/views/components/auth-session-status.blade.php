@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-sm bg-tint-success p-4 text-body-sm text-success']) }}>
        {{ $status }}
    </div>
@endif
