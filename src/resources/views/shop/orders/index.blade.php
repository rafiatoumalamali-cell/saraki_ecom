<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots']"
        :alternates="$seo['alternates']"
    />

    <header class="max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.orders.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md">{{ __('shop.orders.title') }}</h1>
        <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.orders.description') }}</p>
    </header>

    @if ($orders->isEmpty())
        <div class="mt-10 rounded-sm border border-line bg-canvas p-10 text-center">
            <p class="font-display text-heading-md">{{ __('shop.orders.empty') }}</p>
            <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.orders.empty_hint') }}</p>

            <x-ui.button :href="route('shop.index', app()->getLocale())" class="mt-6">
                {{ __('shop.cart.empty_cta') }}
            </x-ui.button>
        </div>
    @else
        <div class="mt-10 hidden overflow-hidden rounded-sm border border-line bg-canvas md:block">
            <table class="w-full text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('shop.orders.order') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('shop.orders.placed_on') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('shop.orders.items') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('shop.orders.status') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('shop.orders.payment') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted">{{ __('shop.orders.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="h-16 border-t border-line transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-6">
                                <a href="{{ route('orders.show', ['locale' => app()->getLocale(), 'order' => $order->order_number]) }}" class="link tabular">
                                    {{ $order->order_number }}
                                </a>
                                <span class="mt-1 block text-ink-muted">{{ __('shop.checkout.method_'.$order->payment_method) }}</span>
                            </td>
                            <td class="px-6">{{ localized_date($order->placed_at) }}</td>
                            <td class="px-6">{{ trans_choice('shop.orders.items_count', $order->items_count) }}</td>
                            <td class="px-6">
                                <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                            </td>
                            <td class="px-6">
                                <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
                            </td>
                            <td class="px-6 text-right tabular">{{ money($order->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="mt-10 space-y-4 md:hidden">
            @foreach ($orders as $order)
                <li class="rounded-sm border border-line bg-canvas p-5">
                    <div class="flex items-start justify-between gap-4">
                        <a href="{{ route('orders.show', ['locale' => app()->getLocale(), 'order' => $order->order_number]) }}" class="link tabular">
                            {{ $order->order_number }}
                        </a>
                        <span class="tabular text-ink">{{ money($order->total) }}</span>
                    </div>

                    <p class="mt-2 text-body-sm text-ink-muted">
                        {{ localized_date($order->placed_at) }} · {{ trans_choice('shop.orders.items_count', $order->items_count) }}
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                        <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
                    </div>

                    <a href="{{ route('orders.show', ['locale' => app()->getLocale(), 'order' => $order->order_number]) }}" class="link mt-4 inline-block text-body-sm">
                        {{ __('shop.orders.view') }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</x-app-layout>
