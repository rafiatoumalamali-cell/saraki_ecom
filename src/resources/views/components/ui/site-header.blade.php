@php
    $locale = app()->getLocale();

    $links = [
        ['label' => __('ui.home'), 'href' => route('home', $locale), 'active' => request()->routeIs('home')],
        ['label' => __('ui.catalogue'), 'href' => null],
        ['label' => __('ui.appointments'), 'href' => null],
        ['label' => __('ui.about'), 'href' => null],
        ['label' => __('ui.contact'), 'href' => null],
    ];
@endphp

<header class="sticky top-0 z-40 bg-charcoal text-beige" x-data="{ navOpen: false }" @keydown.escape="navOpen = false">
    <a href="#main" class="label-upper sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:bg-gold focus:px-4 focus:py-2 focus:text-charcoal">
        {{ __('ui.skip_to_content') }}
    </a>

    <div class="container flex h-20 items-center justify-between gap-6">
        <a href="{{ route('home', $locale) }}" class="flex items-baseline gap-2" aria-label="{{ config('saraki.store_name') }}">
            <span class="font-display text-heading-md tracking-[0.3em] text-beige">{{ config('saraki.store_name') }}</span>
            <span class="h-1.5 w-1.5 rounded-full bg-gold" aria-hidden="true"></span>
        </a>

        <nav aria-label="{{ __('ui.main_nav') }}" class="hidden flex-1 items-center justify-center gap-8 lg:flex">
            @foreach ($links as $link)
                @if ($link['href'])
                    <a
                        href="{{ $link['href'] }}"
                        @class([
                            'label-upper py-2 transition-colors duration-150 ease-brand',
                            'text-gold' => $link['active'],
                            'text-beige hover:text-gold' => ! $link['active'],
                        ])
                        @if ($link['active']) aria-current="page" @endif
                    >{{ $link['label'] }}</a>
                @else
                    <span class="label-upper cursor-default py-2 text-beige/50" aria-disabled="true" title="{{ __('ui.soon') }}">
                        {{ $link['label'] }}
                        <span class="ms-1 text-gold-600">· {{ __('ui.soon') }}</span>
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <x-ui.locale-switcher class="hidden sm:flex" />

            @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="label-upper flex h-11 items-center gap-2 rounded-xs px-3 text-beige transition-colors duration-150 ease-brand hover:bg-charcoal-700">
                            <span class="max-w-[10ch] truncate">{{ auth()->user()->name }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('ui.profile') }}</x-dropdown-link>

                        @if (auth()->user()->isAdmin())
                            <x-dropdown-link :href="route('admin.dashboard')">{{ __('admin.title') }}</x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('ui.log_out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @else
                <div class="hidden items-center gap-3 md:flex">
                    <a href="{{ route('login') }}" class="label-upper rounded-xs px-3 py-2 text-beige transition-colors duration-150 ease-brand hover:bg-charcoal-700">
                        {{ __('ui.log_in') }}
                    </a>
                    <a href="{{ route('register') }}" class="label-upper rounded-xs bg-gold px-4 py-2 text-charcoal transition-colors duration-150 ease-brand hover:bg-gold-600">
                        {{ __('ui.register') }}
                    </a>
                </div>
            @endauth

            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-xs text-beige transition-colors duration-150 ease-brand hover:bg-charcoal-700 lg:hidden"
                @click="navOpen = ! navOpen"
                :aria-expanded="navOpen ? 'true' : 'false'"
                aria-controls="mobile-menu"
            >
                <span class="sr-only" x-text="navOpen ? '{{ __('ui.close_menu') }}' : '{{ __('ui.open_menu') }}'">{{ __('ui.open_menu') }}</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path x-show="! navOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    <path x-show="navOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="fixed inset-0 z-50 bg-charcoal lg:hidden" x-cloak x-show="navOpen" x-transition.opacity.duration.300ms>
        <div class="flex h-20 items-center justify-between px-4">
            <span class="font-display text-heading-md tracking-[0.3em] text-beige">{{ config('saraki.store_name') }}</span>
            <button type="button" class="flex h-11 w-11 items-center justify-center rounded-xs text-beige hover:bg-charcoal-700" @click="navOpen = false">
                <span class="sr-only">{{ __('ui.close_menu') }}</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav aria-label="{{ __('ui.main_nav') }}" class="flex flex-col gap-2 px-6">
            @foreach ($links as $link)
                @if ($link['href'])
                    <a href="{{ $link['href'] }}" @click="navOpen = false" class="font-display text-heading-md py-3 text-beige hover:text-gold">{{ $link['label'] }}</a>
                @else
                    <span class="font-display text-heading-md py-3 text-beige/50" aria-disabled="true">
                        {{ $link['label'] }} <span class="label-upper text-gold-600">{{ __('ui.soon') }}</span>
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="mt-8 border-t border-charcoal-700 px-6 pt-6">
            <x-ui.locale-switcher />

            @guest
                <div class="mt-6 flex flex-col gap-3">
                    <x-ui.button :href="route('login')" variant="on-dark">{{ __('ui.log_in') }}</x-ui.button>
                    <x-ui.button :href="route('register')" variant="gold">{{ __('ui.register') }}</x-ui.button>
                </div>
            @endguest
        </div>
    </div>
</header>
