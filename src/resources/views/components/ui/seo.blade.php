@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'robots' => null,
    'alternates' => [],
])

@php
    $site = config('saraki.store_name');
    $locale = app()->getLocale();
    $ogTitle = filled($title) ? $title.' · '.$site : $site;

    // Storage returns a path relative to the app root; crawlers need an absolute URL.
    $ogImage = filled($image) && ! preg_match('#^https?://#i', $image) ? url($image) : $image;

    $fallback = $alternates[config('app.fallback_locale')] ?? $canonical;
@endphp

@push('head')
    @if (filled($description))
        <meta name="description" content="{{ $description }}">
    @endif

    @if (filled($robots))
        <meta name="robots" content="{{ $robots }}">
    @endif

    @if (filled($canonical))
        <link rel="canonical" href="{{ $canonical }}">
    @endif

    @foreach ($alternates as $alternate => $href)
        <link rel="alternate" hreflang="{{ $alternate }}" href="{{ $href }}">
    @endforeach

    @if (filled($fallback))
        <link rel="alternate" hreflang="x-default" href="{{ $fallback }}">
    @endif

    <meta property="og:site_name" content="{{ $site }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:locale" content="{{ $locale === 'fr' ? 'fr_FR' : 'en_GB' }}">
    <meta property="og:title" content="{{ $ogTitle }}">

    @if (filled($description))
        <meta property="og:description" content="{{ $description }}">
    @endif

    @if (filled($canonical))
        <meta property="og:url" content="{{ $canonical }}">
    @endif

    @if (filled($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    @foreach ($alternates as $alternate => $href)
        @continue($alternate === $locale)
        <meta property="og:locale:alternate" content="{{ $alternate === 'fr' ? 'fr_FR' : 'en_GB' }}">
    @endforeach

    <meta name="twitter:card" content="{{ filled($ogImage) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $ogTitle }}">

    @if (filled($description))
        <meta name="twitter:description" content="{{ $description }}">
    @endif

    @if (filled($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
@endpush
