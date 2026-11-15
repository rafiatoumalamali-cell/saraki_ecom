<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('saraki.store_name') : config('saraki.store_name') }}</title>

        {{-- Description, canonical, Open Graph and hreflang tags are pushed by <x-ui.seo>. --}}
        @stack('head')

        @php
            $organization = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('saraki.store_name'),
                'url' => route('home', app()->getLocale()),
                'email' => $settings->text('contact_email'),
                'telephone' => $settings->text('contact_phone'),
                'address' => $settings->text('store_address'),
            ], fn ($value) => $value !== null);
        @endphp
        <script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-beige-50 font-sans text-ink antialiased">
        <x-ui.site-header />

        <main id="main" tabindex="-1">
            @if ($bare)
                <div class="container pt-6">
                    <x-ui.flash-messages />
                </div>
                {{ $slot }}
            @else
                <div class="container py-10 lg:py-14">
                    <x-ui.flash-messages />
                    {{ $slot }}
                </div>
            @endif
        </main>

        <x-ui.site-footer />

        <x-ui.whatsapp-button />
    </body>
</html>
