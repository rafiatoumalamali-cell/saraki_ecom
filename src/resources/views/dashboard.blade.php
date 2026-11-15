<x-app-layout :title="__('ui.account_title')">
    <x-ui.seo robots="noindex,nofollow" />
    <h1 class="font-display text-display-md">{{ __('ui.account_title') }}</h1>
    <p class="mt-3 text-body-md text-ink-soft">{{ __('ui.account_intro') }}</p>

    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-sm border border-line bg-canvas p-6">
            <h2 class="font-display text-heading-sm">{{ __('ui.orders') }}</h2>

            @forelse ($recentOrders as $order)
                <a href="{{ route('orders.show', ['locale' => app()->getLocale(), 'order' => $order]) }}" class="mt-3 flex items-center justify-between gap-3 text-body-sm">
                    <span class="tabular">{{ $order->order_number }}</span>
                    <span class="text-ink-muted">{{ money($order->total) }}</span>
                </a>
            @empty
                <p class="mt-2 text-body-sm text-ink-muted">{{ __('ui.orders_empty') }}</p>
            @endforelse

            <x-ui.button :href="route('orders.index', app()->getLocale())" variant="secondary" size="compact" class="mt-4">
                {{ __('ui.orders') }}
            </x-ui.button>
        </div>

        <div class="rounded-sm border border-line bg-canvas p-6">
            <h2 class="font-display text-heading-sm">{{ __('shop.wishlist.title') }}</h2>
            <p class="mt-2 text-body-sm text-ink-muted">
                {{ trans_choice('shop.wishlist.count', $wishlistCount) }}
            </p>
            <x-ui.button :href="route('wishlist.index', app()->getLocale())" variant="secondary" size="compact" class="mt-4">
                {{ __('shop.wishlist.view') }}
            </x-ui.button>
        </div>

        <div class="rounded-sm border border-line bg-canvas p-6">
            <h2 class="font-display text-heading-sm">{{ __('ui.profile') }}</h2>
            <p class="mt-2 text-body-sm text-ink-soft">{{ auth()->user()->email }}</p>
            <x-ui.button :href="route('profile.edit')" variant="secondary" size="compact" class="mt-4">
                {{ __('ui.profile') }}
            </x-ui.button>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="rounded-sm border border-line bg-canvas p-6">
                <h2 class="font-display text-heading-sm">{{ __('ui.admin_area') }}</h2>
                <p class="mt-2 text-body-sm text-ink-soft">{{ __('admin.dashboard.subtitle') }}</p>
                <x-ui.button :href="route('admin.dashboard')" variant="primary" size="compact" class="mt-4">
                    {{ __('admin.nav.dashboard') }}
                </x-ui.button>
            </div>
        @endif
    </div>
</x-app-layout>
