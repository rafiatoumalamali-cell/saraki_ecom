<x-admin-layout :title="$title">
    <p class="text-body-md text-ink-soft">{{ __('admin.orders.subtitle') }}</p>

    <dl class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-line bg-canvas shadow-card p-5">
            <dt class="label-upper text-ink-muted">{{ __('admin.orders.awaiting') }}</dt>
            <dd class="mt-2 font-display text-heading-md tabular">{{ $counts['awaiting'] }}</dd>
        </div>
        <div class="rounded-xl border border-line bg-canvas shadow-card p-5">
            <dt class="label-upper text-ink-muted">{{ __('admin.orders.paid_orders') }}</dt>
            <dd class="mt-2 font-display text-heading-md tabular">{{ $counts['paid'] }}</dd>
        </div>
        <div class="rounded-xl border border-line bg-canvas shadow-card p-5">
            <dt class="label-upper text-ink-muted">{{ __('admin.orders.in_production') }}</dt>
            <dd class="mt-2 font-display text-heading-md tabular">{{ $counts['in_production'] }}</dd>
        </div>
    </dl>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-8 flex flex-wrap items-end gap-4 rounded-xl border border-line bg-canvas shadow-card p-5">
        <div class="min-w-[160px]">
            <label for="status" class="label-upper text-ink">{{ __('admin.orders.filter_status') }}</label>
            <select id="status" name="status" class="field mt-2">
                <option value="">{{ __('admin.orders.all_statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[160px]">
            <label for="payment" class="label-upper text-ink">{{ __('admin.orders.filter_payment') }}</label>
            <select id="payment" name="payment" class="field mt-2">
                <option value="">{{ __('admin.orders.all_payments') }}</option>
                @foreach ($paymentStatuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['payment'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[220px] grow">
            <label for="q" class="label-upper text-ink">{{ __('admin.orders.search_label') }}</label>
            <input
                id="q"
                name="q"
                type="search"
                value="{{ $filters['q'] ?? '' }}"
                placeholder="{{ __('admin.orders.search_placeholder') }}"
                class="field mt-2"
            >
        </div>

        <x-ui.button type="submit" variant="secondary" size="compact">{{ __('admin.orders.apply') }}</x-ui.button>

        @if (filled($filters['status'] ?? null) || filled($filters['payment'] ?? null) || filled($filters['q'] ?? null))
            <a href="{{ route('admin.orders.index') }}" class="link pb-3 text-body-sm">{{ __('admin.orders.clear') }}</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        <p class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-6 text-body-sm text-ink-muted">
            {{ __('admin.orders.no_results') }}
        </p>
    @else
        <div class="mt-8 hidden overflow-hidden rounded-xl border border-line bg-canvas shadow-card md:block">
            <table class="w-full text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.orders.number') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.orders.customer') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.orders.placed') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.orders.status') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.orders.payment') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted">{{ __('admin.orders.total') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted"><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-t border-line transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="link tabular">{{ $order->order_number }}</a>
                                <span class="mt-1 block text-ink-muted">{{ trans_choice('shop.orders.items_count', $order->items_count) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="block text-ink">{{ $order->delivery_name }}</span>
                                <span class="block text-ink-muted tabular">{{ $order->delivery_phone }}</span>
                            </td>
                            <td class="px-6 py-4">{{ localized_date($order->placed_at) }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
                                <span class="mt-1 block text-ink-muted">{{ __('shop.checkout.method_'.$order->payment_method) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right tabular">{{ money($order->total) }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="link">{{ __('admin.orders.open') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="mt-8 space-y-4 md:hidden">
            @foreach ($orders as $order)
                <li class="rounded-xl border border-line bg-canvas shadow-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="link tabular">{{ $order->order_number }}</a>
                        <span class="tabular text-ink">{{ money($order->total) }}</span>
                    </div>

                    <p class="mt-2 text-body-sm text-ink-muted">
                        {{ $order->delivery_name }} · {{ $order->delivery_phone }}
                    </p>
                    <p class="text-body-sm text-ink-muted">{{ localized_date($order->placed_at) }}</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
                        <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
                    </div>

                    <a href="{{ route('admin.orders.show', $order) }}" class="link mt-3 inline-block text-body-sm">
                        {{ __('admin.orders.open') }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</x-admin-layout>
