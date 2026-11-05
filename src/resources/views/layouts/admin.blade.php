@php
    $sections = [
        ['key' => 'orders', 'label' => __('admin.nav.orders')],
        ['key' => 'catalogue', 'label' => __('admin.nav.catalogue')],
        ['key' => 'appointments', 'label' => __('admin.nav.appointments')],
        ['key' => 'content', 'label' => __('admin.nav.content')],
        ['key' => 'messages', 'label' => __('admin.nav.messages')],
        ['key' => 'settings', 'label' => __('admin.nav.settings')],
    ];

    $activeSection = request()->routeIs('admin.section') ? request()->route('section') : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' · '.__('admin.title') : __('admin.title') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas-muted font-sans text-ink antialiased">
        <a href="#main" class="label-upper sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-gold focus:px-4 focus:py-2 focus:text-charcoal">
            {{ __('ui.skip_to_content') }}
        </a>

        <div class="min-h-screen lg:flex">
            <aside class="flex shrink-0 items-stretch overflow-x-auto border-b border-charcoal bg-charcoal-700 text-beige lg:w-[260px] lg:flex-col lg:overflow-visible lg:border-b-0">
                <div class="flex items-center px-6 py-6 lg:block">
                    <a href="{{ route('home', app()->getLocale()) }}" class="font-display text-heading-sm tracking-[0.3em] text-beige whitespace-nowrap">
                        {{ config('saraki.store_name') }}
                    </a>
                    <p class="label-upper ms-3 text-gold-600 lg:ms-0 lg:mt-1">{{ __('admin.title') }}</p>
                </div>

                <nav aria-label="{{ __('admin.nav.main_nav') }}" class="flex flex-1 items-stretch lg:flex-col lg:items-stretch lg:gap-1 lg:px-3">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        @class([
                            'flex min-h-14 items-center gap-3 border-l-[3px] px-4 py-3 whitespace-nowrap transition-colors duration-150 ease-brand lg:rounded-xs lg:px-3',
                            'border-gold bg-charcoal-800 text-beige' => request()->routeIs('admin.dashboard'),
                            'border-transparent text-beige/70 hover:bg-charcoal-800 hover:text-beige' => ! request()->routeIs('admin.dashboard'),
                        ])
                        @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                    >
                        <x-ui.admin-icon name="dashboard" />
                        <span class="label-upper lg:hidden xl:inline">{{ __('admin.nav.dashboard') }}</span>
                    </a>

                    @foreach ($sections as $section)
                        <a
                            href="{{ route('admin.section', $section['key']) }}"
                            @class([
                                'flex min-h-14 items-center gap-3 border-l-[3px] px-4 py-3 whitespace-nowrap transition-colors duration-150 ease-brand lg:rounded-xs lg:px-3',
                                'border-gold bg-charcoal-800 text-beige' => $activeSection === $section['key'],
                                'border-transparent text-beige/70 hover:bg-charcoal-800 hover:text-beige' => $activeSection !== $section['key'],
                            ])
                            @if ($activeSection === $section['key']) aria-current="page" @endif
                        >
                            <x-ui.admin-icon :name="$section['key']" />
                            <span class="label-upper lg:hidden xl:inline">{{ $section['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="hidden border-t border-charcoal p-3 lg:block">
                    <a href="{{ route('home', app()->getLocale()) }}" class="flex items-center gap-3 rounded-xs px-3 py-3 text-beige/70 transition-colors duration-150 ease-brand hover:bg-charcoal-800 hover:text-beige">
                        <x-ui.admin-icon name="store" />
                        <span class="label-upper">{{ __('admin.nav.view_store') }}</span>
                    </a>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="flex h-20 items-center justify-between gap-4 border-b border-line bg-canvas px-6">
                    <h1 class="truncate font-display text-heading-md">{{ $title ?? __('admin.nav.dashboard') }}</h1>

                    <div class="flex items-center gap-3">
                        <x-ui.locale-switcher tone="light" />

                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button type="button" class="label-upper flex h-11 items-center gap-2 rounded-xs px-3 text-charcoal transition-colors duration-150 ease-brand hover:bg-beige">
                                    <span class="max-w-[12ch] truncate">{{ auth()->user()->name }}</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">{{ __('ui.profile') }}</x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('ui.log_out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <main id="main" class="p-6 lg:p-8" tabindex="-1">
                    <x-ui.flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
