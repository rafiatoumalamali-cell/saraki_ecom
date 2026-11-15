@props(['tone' => 'light'])

@php
    // Drop brand assets in public/brand/ and they appear automatically:
    //   logo.svg|png        — for light backgrounds (guest pages, light sections)
    //   logo-light.svg|png  — optional, preferred on the dark header/footer
    // With no file present the classic wordmark renders instead.
    $dark = $tone === 'dark';

    $pick = function (string $base): ?string {
        foreach (['svg', 'png', 'webp'] as $ext) {
            if (file_exists(public_path("brand/{$base}.{$ext}"))) {
                return "/brand/{$base}.{$ext}";
            }
        }

        return null;
    };

    $src = ($dark ? $pick('logo-light') : null) ?? $pick('logo');
@endphp

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ config('saraki.store_name') }}"
        {{ $attributes->merge(['class' => 'h-9 w-auto sm:h-10']) }}
    >
@else
    <span {{ $attributes->merge(['class' => 'font-display text-heading-md tracking-[0.3em] transition-colors duration-200 ease-brand '.($dark ? 'text-beige group-hover:text-gold' : 'text-charcoal group-hover:text-gold-700')]) }}>
        {{ config('saraki.store_name') }}
    </span>
@endif
