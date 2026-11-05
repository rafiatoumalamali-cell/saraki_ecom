<x-app-layout>
    <section class="rounded-sm bg-beige px-6 py-16 text-center md:px-12 lg:py-24">
        <h1 class="font-display text-display-lg md:text-display-xl">{{ config('saraki.store_name') }}</h1>
        <p class="mx-auto mt-6 max-w-prose text-body-lg text-ink-soft">{{ __('ui.footer.tagline') }}</p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            @auth
                <x-ui.button :href="route('dashboard')" variant="primary">{{ __('ui.account_title') }}</x-ui.button>
            @else
                <x-ui.button :href="route('register')" variant="primary">{{ __('ui.register') }}</x-ui.button>
                <x-ui.button :href="route('login')" variant="secondary">{{ __('ui.log_in') }}</x-ui.button>
            @endauth
        </div>
    </section>

    <section class="mt-16">
        <h2 class="font-display text-heading-lg">{{ __('ui.coming_soon_title') }}</h2>
        <p class="mt-4 max-w-prose text-body-md text-ink-soft">{{ __('ui.coming_soon_text') }}</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (['catalogue', 'appointments', 'about', 'contact'] as $item)
                <div class="rounded-sm border border-line bg-canvas p-6">
                    <p class="label-upper text-gold-700">{{ __('ui.soon') }}</p>
                    <p class="mt-2 font-display text-heading-sm">{{ __('ui.'.$item) }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>
