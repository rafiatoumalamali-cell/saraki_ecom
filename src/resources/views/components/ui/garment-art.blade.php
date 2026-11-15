{{-- Licence-free placeholder art. Swap for atelier photography by uploading a product image. --}}
@props(['variant' => 'boubou', 'tone' => 'light'])

@php
    $uid = 'ga'.substr(md5(uniqid((string) mt_rand(), true)), 0, 8);

    $dark = $tone === 'dark';

    $ground = $dark ? '#141414' : '#F5F1E8';
    $halo = $dark ? '#D4AF37' : '#D4AF37';
    $fill = $dark ? '#2E2E2E' : '#FFFFFF';
    $stroke = $dark ? '#D4AF37' : '#8A6D1F';
    $motif = $dark ? '#D4AF37' : '#B8962B';

    $shapes = match ($variant) {
        'caftan' => [
            'M150 72c-10 0-17 7-19 16l-27 10c-12 6-18 20-20 36l-6 76 20 4 6-54-8 184h108l-8-184 6 54 20-4-6-76c-2-16-8-30-20-36l-27-10c-2-9-9-16-19-16Z',
        ],
        'ensemble' => [
            'M150 76c-9 0-16 6-18 14l-24 10c-10 6-16 18-18 32l-4 44 18 4 4-30-4 56h92l-4-56 4 30 18-4-4-44c-2-14-8-26-18-32l-24-10c-2-8-9-14-18-14Z',
            'M104 214h92l16 130H88Z',
        ],
        'soiree' => [
            'M150 78c-8 0-14 6-16 14l-18 8 4 40-8 60-8 60c-8 40-20 70-28 88h148c-8-18-20-48-28-88l-8-60-8-60 4-40-18-8c-2-8-8-14-16-14Z',
        ],
        'pret' => [
            'M150 78c-8 0-14 6-16 14l-24 10-14 48 18 8 6-20-2 76h64l-2-76 6 20 18-8-14-48-24-10c-2-8-8-14-16-14Z',
            'M120 222h60l6 122h-30l-6-72-6 72h-30Z',
        ],
        'accessoires' => [
            'M104 196h92l10 108H94Z',
            'M124 196c0-42 52-42 52 0',
            'M112 104c14-16 62-16 76 0 10 12 4 34-12 40-14 6-38 6-52 0-16-6-22-28-12-40Z',
        ],
        default => [
            'M150 70c-12 0-20 8-22 18l-32 12c-26 12-38 50-44 96l22 10c6-30 14-48 22-58l-8 192h124l-8-192c8 10 16 28 22 58l22-10c-6-46-18-84-44-96l-32-12c-2-10-10-18-22-18Z',
        ],
    };

    $details = match ($variant) {
        'caftan' => ['M150 96v248', 'M118 214h64'],
        'ensemble' => ['M104 214h92', 'M112 240l76 84'],
        'soiree' => ['M134 92c8 14 24 14 32 0', 'M150 140v190'],
        'pret' => ['M150 96v118', 'M120 222h60'],
        'accessoires' => ['M94 232h112', 'M132 128c10 8 26 8 36 0'],
        default => ['M128 88c8 18 36 18 44 0', 'M138 112h24v96h-24Z'],
    };
@endphp

<svg {{ $attributes->merge(['class' => 'h-full w-full']) }} viewBox="0 0 300 400" fill="none" aria-hidden="true" focusable="false">
    <defs>
        <pattern id="{{ $uid }}-wax" width="26" height="26" patternUnits="userSpaceOnUse">
            <circle cx="13" cy="13" r="4.5" fill="{{ $motif }}" />
            <path d="M0 0h4M22 22h4M0 22h4M22 0h4" stroke="{{ $motif }}" stroke-width="2" />
        </pattern>
        <linearGradient id="{{ $uid }}-ground" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{{ $ground }}" />
            <stop offset="100%" stop-color="{{ $dark ? '#0A0A0A' : '#E8E1D2' }}" />
        </linearGradient>
        @foreach ($shapes as $i => $d)
            <path id="{{ $uid }}-s{{ $i }}" d="{{ $d }}" />
        @endforeach
    </defs>

    <rect width="300" height="400" fill="url(#{{ $uid }}-ground)" />
    <circle cx="150" cy="180" r="112" fill="{{ $halo }}" opacity="{{ $dark ? '.10' : '.14' }}" />
    <circle cx="150" cy="180" r="112" stroke="{{ $stroke }}" stroke-width="1" opacity=".35" />

    @foreach ($shapes as $i => $d)
        @php($open = $variant === 'accessoires' && $i === 1)
        <use href="#{{ $uid }}-s{{ $i }}" fill="{{ $open ? 'none' : $fill }}" stroke="{{ $stroke }}" stroke-width="{{ $open ? '3' : '2' }}" stroke-linejoin="round" stroke-linecap="round" />
        @unless ($open)
            <use href="#{{ $uid }}-s{{ $i }}" fill="url(#{{ $uid }}-wax)" opacity=".22" />
        @endunless
    @endforeach

    @foreach ($details as $d)
        <path d="{{ $d }}" stroke="{{ $stroke }}" stroke-width="1.5" opacity=".55" stroke-linecap="round" />
    @endforeach
</svg>