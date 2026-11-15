<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('saraki.store_name') : config('saraki.store_name') }}</title>
        <meta name="robots" content="noindex,nofollow">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-beige-50 font-sans text-ink antialiased">
        <div class="relative mx-auto flex min-h-screen w-full max-w-[480px] flex-col justify-center px-4 py-12 sm:py-16">
            <span aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-[radial-gradient(60%_100%_at_50%_0%,rgba(212,175,55,.14),transparent_70%)]"></span>

            <a href="{{ route('home', app()->getLocale()) }}" class="group relative mb-10 flex items-center gap-2 self-start">
                <x-ui.logo tone="light" class="h-8" />
                <span class="h-1.5 w-1.5 rounded-full bg-gold" aria-hidden="true"></span>
            </a>

            <div class="relative animate-fade-up rounded-lg border border-line bg-canvas p-6 shadow-lift sm:p-8">
                <x-ui.flash-messages />
                {{ $slot }}
            </div>

            <x-ui.locale-switcher tone="light" class="relative mt-10 self-start" />
        </div>
    </body>
</html>
