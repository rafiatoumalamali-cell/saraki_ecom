@php
    $locale = app()->getLocale();
    $method = $order->payment_method;
    $canRetry = $order->payment_status !== \App\Enums\OrderPaymentStatus::Paid
        && $order->status !== \App\Enums\OrderStatus::Cancelled
        && $order->restocked_at === null
        && $method === \App\Services\OrderService::PAYSTACK;

    $expiry = app(\App\Services\OrderService::class)->expiryMinutes();
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots']"
        :alternates="$seo['alternates']"
    />

    <a href="{{ route('orders.index', $locale) }}" class="link text-body-sm">&larr; {{ __('shop.orders.back_to_orders') }}</a>

    <header class="mt-6 flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="label-upper text-gold-700">{{ __('shop.orders.eyebrow') }}</p>
            <h1 class="mt-3 font-display text-display-md">{{ __('shop.orders.reference', ['number' => $order->order_number]) }}</h1>
            <p class="mt-2 text-body-sm text-ink-muted">
                {{ __('shop.orders.placed_on') }} {{ localized_date($order->placed_at) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
            <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
        </div>
    </header>

    @if ($canRetry)
        <div class="mt-8 rounded-sm border border-line bg-tint-warning p-6">
            <p class="font-display text-heading-sm text-warning">{{ __('shop.orders.awaiting_payment') }}</p>

            <p class="mt-2 text-body-sm text-ink-soft">
                {{ __('shop.payment.retry_hint', ['minutes' => $expiry]) }}
            </p>

            <form method="POST" action="{{ route('orders.pay', ['locale' => $locale, 'order' => $order->order_number]) }}" class="mt-4">
                @csrf
                <x-ui.button type="submit" size="compact">{{ __('shop.payment.try_again') }}</x-ui.button>
            </form>

            <p class="mt-3 text-body-sm text-ink-muted">{{ __('shop.payment.expires_note', ['minutes' => $expiry]) }}</p>
        </div>
    @endif

    @if ($order->status === \App\Enums\OrderStatus::Cancelled)
        <x-ui.alert type="warning" class="mt-8">
            {{ __('shop.orders.cancelled') }}
            @if ($order->restocked_at !== null)
                {{ __('shop.orders.cancelled_note') }}
            @endif
        </x-ui.alert>
    @endif

    <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-14">
        <div class="space-y-10">
            <section aria-labelledby="order-timeline">
                <h2 id="order-timeline" class="font-display text-heading-md">{{ __('shop.orders.timeline') }}</h2>

                <ol class="mt-6 space-y-0 border-s border-line ps-6">
                    @foreach ($timeline as $step)
                        <li class="relative pb-6 last:pb-0">
                            <span
                                @class([
                                    'absolute -start-[31px] top-1 flex h-4 w-4 items-center justify-center rounded-full border-2',
                                    'border-gold bg-gold' => $step['done'],
                                    'border-line bg-canvas' => ! $step['done'],
                                ])
                                aria-hidden="true"
                            ></span>

                            <p @class(['label-upper', 'text-ink' => $step['done'], 'text-ink-muted' => ! $step['done']])>
                                {{ __('shop.orders.timeline_'.$step['status']->value) }}
                            </p>

                            @if ($step['reached_at'])
                                <p class="mt-1 text-body-sm text-ink-muted">
                                    {{ localized_date($step['reached_at']) }} · {{ localized_time($step['reached_at']) }}
                                </p>
                            @endif

                            @if (filled($step['note']))
                                <p class="mt-1 text-body-sm text-ink-soft">{{ $step['note'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>

            <section aria-labelledby="order-items">
                <h2 id="order-items" class="font-display text-heading-md">{{ __('shop.orders.items') }}</h2>

                <ul class="mt-4 divide-y divide-line border-y border-line">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-4 py-4">
                            <div class="min-w-0">
                                <p class="text-ink">{{ $item->product_name }}</p>
                                @if (filled($item->variant_label))
                                    <p class="mt-1 text-body-sm text-ink-muted">{{ $item->variant_label }}</p>
                                @endif
                                <p class="mt-1 text-body-sm text-ink-muted">
                                    {{ money($item->unit_price) }} × {{ $item->quantity }}
                                </p>
                            </div>

                            <p class="tabular whitespace-nowrap text-ink">{{ money($item->line_total) }}</p>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-3 text-body-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('shop.orders.subtotal') }}</dt>
                        <dd class="tabular text-ink">{{ money($order->subtotal) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('shop.orders.shipping') }}</dt>
                        <dd class="tabular text-ink">{{ money($order->shipping_fee) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-ink pt-3">
                        <dt class="label-upper">{{ __('shop.orders.total') }}</dt>
                        <dd class="text-price tabular text-ink">{{ money($order->total) }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="space-y-6">
            <div class="rounded-sm border border-line bg-canvas p-6">
                <h2 class="font-display text-heading-sm">{{ __('shop.orders.delivery') }}</h2>

                <address class="mt-4 text-body-sm not-italic text-ink-soft">
                    <span class="block text-ink">{{ $order->delivery_name }}</span>
                    <span class="block">{{ $order->delivery_address }}</span>
                    <span class="block">{{ $order->delivery_city }}</span>
                    <span class="block tabular">{{ $order->delivery_phone }}</span>
                    @if (filled($order->delivery_email))
                        <span class="block">{{ $order->delivery_email }}</span>
                    @endif
                </address>

                @if (filled($order->notes))
                    <div class="mt-4 border-t border-line pt-4">
                        <p class="label-upper text-ink-muted">{{ __('shop.orders.notes') }}</p>
                        <p class="mt-2 text-body-sm text-ink-soft">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-sm border border-line bg-canvas p-6">
                <h2 class="font-display text-heading-sm">{{ __('shop.orders.payment') }}</h2>

                <p class="mt-3 text-body-sm text-ink-soft">
                    {{ __('shop.checkout.method_'.$method) }} — {{ $order->payment_status->label() }}
                </p>

                @if ($order->payments->isNotEmpty())
                    <ul class="mt-4 space-y-3 border-t border-line pt-4 text-body-sm">
                        @foreach ($order->payments as $payment)
                            <li class="flex items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block tabular text-ink">{{ $payment->reference }}</span>
                                    <span class="block text-ink-muted">{{ $payment->status->label() }}</span>
                                </span>
                                <span class="tabular whitespace-nowrap text-ink">{{ money($payment->amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <x-ui.button :href="route('shop.index', $locale)" variant="secondary" class="w-full">
                {{ __('shop.orders.back_to_shop') }}
            </x-ui.button>
        </aside>
    </div>
</x-app-layout>
