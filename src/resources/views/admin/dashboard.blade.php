<x-admin-layout :title="__('admin.dashboard.title')">
    <p class="text-body-md text-ink-soft">{{ __('admin.dashboard.subtitle') }}</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
        @foreach ($stats as $stat)
            <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :hint="$stat['hint']" />
        @endforeach
    </div>

    <section class="mt-12">
        <h2 class="font-display text-heading-md">{{ __('admin.dashboard.recent_orders') }}</h2>

        @if ($recentOrders->isEmpty())
            <p class="mt-4 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
                {{ __('admin.dashboard.no_orders') }}
            </p>
        @else
            <div class="mt-6 hidden overflow-hidden rounded-sm border border-line bg-canvas md:block">
                <table class="w-full text-left text-body-sm">
                    <thead class="bg-beige">
                        <tr>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.order') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.customer') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.placed') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.dashboard.status') }}</th>
                            <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted">{{ __('admin.dashboard.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr class="h-14 border-t border-line transition-colors duration-150 ease-brand hover:bg-beige-50">
                                <td class="px-6 tabular">{{ $order->order_number }}</td>
                                <td class="px-6">{{ $order->user?->name ?? '—' }}</td>
                                <td class="px-6">{{ localized_date($order->placed_at ?? $order->created_at) }}</td>
                                <td class="px-6">
                                    <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                                    <x-ui.badge :tone="$order->payment_status->badgeTone()" class="ms-2">{{ $order->payment_status->label() }}</x-ui.badge>
                                </td>
                                <td class="px-6 text-right tabular">{{ money($order->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="mt-6 space-y-4 md:hidden">
                @foreach ($recentOrders as $order)
                    <li class="rounded-sm border border-line bg-canvas p-4">
                        <dl class="space-y-3 text-body-sm">
                            <div>
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.order') }}</dt>
                                <dd class="mt-1 tabular">{{ $order->order_number }}</dd>
                            </div>
                            <div>
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.customer') }}</dt>
                                <dd class="mt-1">{{ $order->user?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.status') }}</dt>
                                <dd class="mt-1 flex flex-wrap gap-2">
                                    <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                                    <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="label-upper text-ink-muted">{{ __('admin.dashboard.total') }}</dt>
                                <dd class="mt-1 text-right tabular">{{ money($order->total) }}</dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-admin-layout>
