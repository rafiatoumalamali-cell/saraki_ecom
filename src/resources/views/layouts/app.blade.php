<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('saraki.store_name') : config('saraki.store_name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-ink antialiased">
        <x-ui.site-header />

        <main id="main" tabindex="-1">
            <div class="container py-12">
                <x-ui.flash-messages />
                {{ $slot }}
            </div>
        </main>

        <x-ui.site-footer />
    </body>
</html>
