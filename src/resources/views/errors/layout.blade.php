<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') — {{ config('saraki.store_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-beige-50 font-sans text-ink antialiased">
    {{--
        Deliberately not the storefront layout: a 500 can mean the database is
        down, and error pages must render without touching it.
    --}}
    <main class="flex min-h-screen items-center justify-center px-6 py-16">
        <div class="w-full max-w-lg text-center">
            @yield('content')
        </div>
    </main>
</body>
</html>
