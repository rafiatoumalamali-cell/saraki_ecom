@php
    $sections = collect([
        'orders' => ['admin.orders.index', []],
        'catalogue' => ['admin.catalogue.products.index', []],
        'appointments' => ['admin.appointments.index', []],
        'customers' => ['admin.customers.index', []],
        'content' => ['admin.content.index', []],
        'messages' => ['admin.messages.index', []],
        'settings' => ['admin.settings.edit', []],
    ])->map(fn (array $route, string $key) => [
        'key' => $key,
        'label' => __('admin.nav.'.$key),
        'href' => route($route[0], $route[1]),
        'active' => request()->routeIs('admin.'.$key.'.*'),
    ])->all();
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
            {{-- Sidebar: sticky on desktop, horizontal bar on mobile --}}
            <aside class="flex shrink-0 items-stretch overflow-x-auto border-b border-white/10 bg-gradient-to-b from-black via-charcoal to-charcoal-800 text-beige lg:sticky lg:top-0 lg:h-screen lg:w-[264px] lg:flex-col lg:overflow-visible lg:border-b-0 lg:border-e lg:border-white/10">
                <div class="flex items-center gap-3 px-6 py-6 lg:block">
                    <a href="{{ route('home', app()->getLocale()) }}" class="block whitespace-nowrap">
                        <x-ui.logo tone="dark" class="h-7" />
                    </a>
                    <p class="label-upper ms-3 text-gold-600 lg:ms-0 lg:mt-1.5">{{ __('admin.title') }}</p>
                </div>

                <nav aria-label="{{ __('admin.nav.main_nav') }}" class="flex flex-1 items-stretch gap-1 px-3 lg:flex-col lg:items-stretch lg:px-4">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        @class([
                            'flex min-h-12 items-center gap-3 rounded-lg px-4 py-2.5 whitespace-nowrap transition-all duration-200 ease-brand',
                            'bg-gold/15 text-gold shadow-[inset_0_0_0_1px_rgba(212,175,55,0.25)]' => request()->routeIs('admin.dashboard'),
                            'text-beige/65 hover:bg-white/5 hover:text-beige' => ! request()->routeIs('admin.dashboard'),
                        ])
                        @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                    >
                        <x-ui.admin-icon name="dashboard" />
                        <span class="label-upper">{{ __('admin.nav.dashboard') }}</span>
                    </a>

                    @foreach ($sections as $section)
                        <a
                            href="{{ $section['href'] }}"
                            @class([
                                'flex min-h-12 items-center gap-3 rounded-lg px-4 py-2.5 whitespace-nowrap transition-all duration-200 ease-brand',
                                'bg-gold/15 text-gold shadow-[inset_0_0_0_1px_rgba(212,175,55,0.25)]' => $section['active'],
                                'text-beige/65 hover:bg-white/5 hover:text-beige' => ! $section['active'],
                            ])
                            @if ($section['active']) aria-current="page" @endif
                        >
                            <x-ui.admin-icon :name="$section['key']" />
                            <span class="label-upper">{{ $section['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="hidden border-t border-white/10 p-4 lg:block">
                    <div class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-3">
                        <x-ui.avatar :user="auth()->user()" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-body-sm font-medium text-beige">{{ auth()->user()->name }}</p>
                            <p class="label-upper text-[10px] text-gold-600">{{ __('admin.nav.admin_role') }}</p>
                        </div>
                    </div>

                    <a href="{{ route('home', app()->getLocale()) }}" class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2.5 text-beige/65 transition-all duration-200 ease-brand hover:bg-white/5 hover:text-beige">
                        <x-ui.admin-icon name="store" />
                        <span class="label-upper">{{ __('admin.nav.view_store') }}</span>
                    </a>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-30 flex h-20 items-center justify-between gap-4 border-b border-line bg-canvas/85 px-6 backdrop-blur-md lg:px-8">
                    <h1 class="truncate font-display text-heading-md">{{ $title ?? __('admin.nav.dashboard') }}</h1>

                    <div class="flex items-center gap-3">
                        <x-ui.locale-switcher tone="light" />

                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button type="button" aria-label="{{ auth()->user()->name }}" aria-haspopup="true" x-bind:aria-expanded="open" class="label-upper flex h-11 items-center gap-2 rounded-full border border-line bg-canvas px-3 text-charcoal transition-all duration-200 ease-brand hover:border-line-strong hover:shadow-card">
                                    <x-ui.avatar :user="auth()->user()" size="sm" />
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

                <main id="main" class="mx-auto w-full max-w-[1440px] p-6 lg:p-8" tabindex="-1">
                    <x-ui.flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
