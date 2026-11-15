<x-admin-layout :title="__('admin.dashboard.title')">
    <p class="text-body-md text-ink-soft">{{ __('admin.dashboard.subtitle') }}</p>

    @unless ($paymentsAvailable)
        <x-ui.alert type="warning" class="mt-6">
            {{ __('admin.dashboard.no_payment_methods') }}
        </x-ui.alert>
    @endunless

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        @foreach ($stats as $stat)
            <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :hint="$stat['hint']" :icon="$stat['icon']" />
        @endforeach
    </div>

    <section class="mt-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h2 class="font-display text-heading-md">{{ __('admin.dashboard.recent_orders') }}</h2>
            <a href="{{ route('admin.orders.index') }}" class="link inline-flex items-center gap-1.5 text-body-sm">
                {{ __('admin.dashboard.view_all_orders') }}
                <svg class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>

        @if ($recentOrders->isEmpty())
            <p class="mt-4 rounded-xl border border-line bg-canvas p-8 text-center text-body-sm text-ink-muted">
                {{ __('admin.dashboard.no_orders') }}
            </p>
        else
            <div class="mt-6 hidden overflow-hidden rounded-xl bg-canvas shadow-card ring-1 ring-line md:block">
                <table class="w-full text-left text-body-sm">
                    <thead>
                        <tr class="border-b border-line bg-beige-50">
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.order') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.customer') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.placed') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.status') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted">{{ __('admin.dashboard.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr class="h-14 border-b border-line/60 transition-colors duration-150 ease-brand last:border-0 hover:bg-beige-50/60">
                                <td class="px-6 tabular font-medium">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-ink transition-colors hover:text-gold-700">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-6">{{ $order->user?->name ?? '—' }}</td>
                                <td class="px-6 text-ink-muted">{{ localized_date($order->placed_at ?? $order->created_at) }}</td>
                                <td class="px-6">
                                    <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                                    <x-ui.badge :tone="$order->payment_status->badgeTone()" class="ms-2">{{ $order->payment_status->label() }}</x-ui.badge>
                                </td>
                                <td class="px-6 text-right tabular font-semibold">{{ money($order->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="mt-6 space-y-4 md:hidden">
                @foreach ($recentOrders as $order)
                    <li class="rounded-xl border border-line bg-canvas p-5 shadow-card">
                        <dl class="space-y-3 text-body-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.order') }}</dt>
                                <dd class="tabular font-medium">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="transition-colors hover:text-gold-700">{{ $order->order_number }}</a>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.customer') }}</dt>
                                <dd>{{ $order->user?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.status') }}</dt>
                                <dd class="mt-1 flex flex-wrap gap-2">
                                    <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                                    <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 border-t border-line/60 pt-3">
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.total') }}</dt>
                                <dd class="tabular font-semibold">{{ money($order->total) }}</dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-admin-layout>
