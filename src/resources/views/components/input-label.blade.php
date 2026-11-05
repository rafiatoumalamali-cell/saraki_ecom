@props(['value'])

<label {{ $attributes->merge(['class' => 'label-upper block text-ink']) }}>
    {{ $value ?? $slot }}
</label>
