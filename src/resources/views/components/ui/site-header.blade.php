@php
    $locale = app()->getLocale();

    $links = [
        ['label' => __('ui.home'), 'href' => route('home', $locale), 'active' => request()->routeIs('home')],
        ['label' => __('ui.catalogue'), 'href' => route('shop.index', $locale), 'active' => request()->routeIs('shop.*')],
        ['label' => __('ui.gallery'), 'href' => route('gallery.index', $locale), 'active' => request()->routeIs('gallery.*')],
        ['label' => __('ui.journal'), 'href' => route('journal.index', $locale), 'active' => request()->routeIs('journal.*')],
        ['label' => __('ui.appointments'), 'href' => route('appointments.index', $locale), 'active' => request()->routeIs('appointments.*')],
        ['label' => __('ui.contact'), 'href' => route('contact.show', $locale), 'active' => request()->routeIs('contact.*')],
    ];
@endphp

<header
    class="sticky top-0 z-40 border-b border-white/10 bg-charcoal/90 text-beige shadow-[0_1px_0_rgba(212,175,55,.18)] backdrop-blur-xl"
    x-data="{ navOpen: false }"
    x-effect="document.body.style.overflow = navOpen ? 'hidden' : ''"
    @keydown.escape="navOpen = false"
>
    <a href="#main" class="label-upper sr-only rounded-sm focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-gold focus:px-4 focus:py-2 focus:text-charcoal">
        {{ __('ui.skip_to_content') }}
    </a>

    <div class="container flex h-20 items-center justify-between gap-6">
        <a href="{{ route('home', $locale) }}" class="group flex shrink-0 items-center gap-2" aria-label="{{ config('saraki.store_name') }}">
            <x-ui.logo tone="dark" />
            <span class="h-1.5 w-1.5 rounded-full bg-gold transition-transform duration-300 ease-spring group-hover:scale-150" aria-hidden="true"></span>
        </a>

        <nav aria-label="{{ __('ui.main_nav') }}" class="hidden flex-1 items-center justify-center gap-7 xl:flex">
            @foreach ($links as $link)
                @if ($link['href'])
                    <a
                        href="{{ $link['href'] }}"
                        @class([
                            'label-upper relative py-2 transition-colors duration-200 ease-brand after:absolute after:inset-x-0 after:-bottom-0.5 after:h-px after:origin-left after:scale-x-0 after:bg-gold after:transition-transform after:duration-300 after:ease-brand hover:after:scale-x-100',
                            'text-gold after:scale-x-100' => $link['active'],
                            'text-beige/85 hover:text-beige' => ! $link['active'],
                        ])
                        @if ($link['active']) aria-current="page" @endif
                    >{{ $link['label'] }}</a>
                @else
                    <span class="label-upper cursor-default py-2 text-beige/40" aria-disabled="true" title="{{ __('ui.soon') }}">
                        {{ $link['label'] }}
                        <span class="ms-1 text-gold-600">· {{ __('ui.soon') }}</span>
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <x-ui.cart-link class="hidden sm:flex" />

            <x-ui.locale-switcher class="hidden sm:flex" />

            @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" aria-label="{{ auth()->user()->name }}" aria-haspopup="true" x-bind:aria-expanded="open" class="label-upper flex h-11 items-center gap-2 rounded-sm px-3 text-beige transition-colors duration-200 ease-brand hover:bg-white/10">
                            <x-ui.avatar :user="auth()->user()" size="sm" />
                            <span class="hidden max-w-[10ch] truncate md:inline">{{ auth()->user()->name }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('dashboard')">{{ __('ui.dashboard') }}</x-dropdown-link>

                        <x-dropdown-link :href="route('wishlist.index', $locale)">{{ __('shop.wishlist.title') }}</x-dropdown-link>

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
                <div class="hidden items-center gap-2 md:flex">
                    <a href="{{ route('login') }}" class="label-upper rounded-sm px-3 py-2.5 text-beige transition-colors duration-200 ease-brand hover:bg-white/10">
                        {{ __('ui.log_in') }}
                    </a>
                    <a href="{{ route('register') }}" class="label-upper rounded-sm bg-gradient-to-b from-gold to-gold-600 px-4 py-2.5 text-charcoal shadow-sm transition-all duration-200 ease-brand hover:-translate-y-0.5 hover:shadow-md active:translate-y-0">
                        {{ __('ui.register') }}
                    </a>
                </div>
            @endauth

            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-sm text-beige transition-colors duration-200 ease-brand hover:bg-white/10 xl:hidden"
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

    <div
        id="mobile-menu"
        class="fixed inset-0 z-50 overflow-y-auto bg-charcoal xl:hidden"
        x-cloak
        x-show="navOpen"
        x-transition:enter="transition-opacity duration-200 ease-brand"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150 ease-brand"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="flex h-20 items-center justify-between border-b border-white/10 px-5">
            <x-ui.logo tone="dark" />
            <button type="button" class="flex h-11 w-11 items-center justify-center rounded-sm text-beige transition-colors duration-200 ease-brand hover:bg-white/10" @click="navOpen = false">
                <span class="sr-only">{{ __('ui.close_menu') }}</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav aria-label="{{ __('ui.main_nav') }}" class="flex flex-col px-5 py-4">
            @foreach ($links as $link)
                @if ($link['href'])
                    <a
                        href="{{ $link['href'] }}"
                        @click="navOpen = false"
                        @class([
                            'animate-fade-up flex items-center justify-between border-b border-white/5 py-4 font-display text-heading-md transition-colors duration-200 ease-brand',
                            'text-gold' => $link['active'],
                            'text-beige hover:text-gold' => ! $link['active'],
                        ])
                        style="animation-delay: {{ $loop->index * 45 }}ms"
                        @if ($link['active']) aria-current="page" @endif
                    >
                        {{ $link['label'] }}
                        <svg class="h-5 w-5 text-beige/30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                @else
                    <span class="flex items-baseline gap-2 border-b border-white/5 py-4 font-display text-heading-md text-beige/40" aria-disabled="true" style="animation-delay: {{ $loop->index * 45 }}ms">
                        {{ $link['label'] }} <span class="label-upper text-gold-600">{{ __('ui.soon') }}</span>
                    </span>
                @endif
            @endforeach
        </nav>

        <div class="px-5 pb-10 pt-6">
            <x-ui.locale-switcher />

            <div class="mt-6">
                <x-ui.button :href="route('cart.index', $locale)" variant="on-dark" class="w-full">
                    {{ __('shop.cart.title') }}
                </x-ui.button>
            </div>

            @guest
                <div class="mt-4 flex flex-col gap-3">
                    <x-ui.button :href="route('login')" variant="on-dark">{{ __('ui.log_in') }}</x-ui.button>
                    <x-ui.button :href="route('register')" variant="gold">{{ __('ui.register') }}</x-ui.button>
                </div>
            @endguest
        </div>
    </div>
</header>
