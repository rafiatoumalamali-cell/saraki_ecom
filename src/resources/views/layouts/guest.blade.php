<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.config('saraki.store_name') : config('saraki.store_name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas-muted font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen w-full max-w-[460px] flex-col justify-center px-4 py-16">
            <a href="{{ route('home', app()->getLocale()) }}" class="mb-10 flex items-baseline gap-2 self-start">
                <span class="font-display text-heading-md tracking-[0.3em] text-charcoal">{{ config('saraki.store_name') }}</span>
                <span class="h-1.5 w-1.5 rounded-full bg-gold" aria-hidden="true"></span>
            </a>

            <div class="rounded-sm border border-line bg-canvas p-6 shadow-md">
                <x-ui.flash-messages />
                {{ $slot }}
            </div>

            <x-ui.locale-switcher tone="light" class="mt-10 self-start" />
        </div>
    </body>
</html>
