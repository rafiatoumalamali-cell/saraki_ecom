@php
    $locale = app()->getLocale();
    $isCancelled = $order->status === \App\Enums\OrderStatus::Cancelled;
    $isPaid = $order->payment_status === \App\Enums\OrderPaymentStatus::Paid;
    $canReconcile = ! $isPaid
        && ! $isCancelled
        && in_array($order->payment_method, [\App\Services\OrderService::COD, \App\Services\OrderService::TRANSFER], true);
@endphp

<x-admin-layout :title="$title">
    <a href="{{ route('admin.orders.index') }}" class="link text-body-sm">&larr; {{ __('admin.orders.back_to_list') }}</a>

    <header class="mt-5 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-heading-md tabular">{{ $order->order_number }}</h2>
            <p class="mt-1 text-body-sm text-ink-muted">
                {{ __('admin.orders.placed') }} {{ localized_date($order->placed_at) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.badge :tone="$order->status->badgeTone()">{{ $order->status->label() }}</x-ui.badge>
            <x-ui.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-ui.badge>
            <span class="text-body-sm text-ink-muted">{{ __('shop.checkout.method_'.$order->payment_method) }}</span>
        </div>
    </header>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="min-w-0 space-y-6">
            <section class="rounded-xl border border-line bg-canvas shadow-card">
                <h3 class="border-b border-line px-6 py-4 font-display text-heading-sm">{{ __('admin.orders.items_heading') }}</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-body-sm">
                        <thead class="bg-beige">
                            <tr>
                                <th scope="col" class="label-upper px-6 py-3 text-ink-muted">{{ __('admin.orders.item') }}</th>
                                <th scope="col" class="label-upper px-6 py-3 text-right text-ink-muted">{{ __('admin.orders.unit_price') }}</th>
                                <th scope="col" class="label-upper px-6 py-3 text-right text-ink-muted">{{ __('admin.orders.quantity') }}</th>
                                <th scope="col" class="label-upper px-6 py-3 text-right text-ink-muted">{{ __('admin.orders.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr class="border-t border-line">
                                    <td class="px-6 py-4">
                                        <span class="block text-ink">{{ $item->product_name }}</span>
                                        @if (filled($item->variant_label))
                                            <span class="block text-ink-muted">{{ $item->variant_label }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right tabular">{{ money($item->unit_price) }}</td>
                                    <td class="px-6 py-4 text-right tabular">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right tabular">{{ money($item->line_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <dl class="space-y-2 border-t border-line px-6 py-4 text-body-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('admin.orders.subtotal') }}</dt>
                        <dd class="tabular">{{ money($order->subtotal) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('admin.orders.shipping') }}</dt>
                        <dd class="tabular">{{ money($order->shipping_fee) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-line pt-2">
                        <dt class="label-upper">{{ __('admin.orders.total') }}</dt>
                        <dd class="text-price tabular">{{ money($order->total) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-line bg-canvas shadow-card">
                <h3 class="border-b border-line px-6 py-4 font-display text-heading-sm">{{ __('admin.orders.payments_heading') }}</h3>

                @if ($order->payments->isEmpty())
                    <p class="px-6 py-4 text-body-sm text-ink-muted">{{ __('admin.orders.no_payments') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-body-sm">
                            <thead class="bg-beige">
                                <tr>
                                    <th scope="col" class="label-upper px-6 py-3 text-ink-muted">{{ __('admin.orders.attempt') }}</th>
                                    <th scope="col" class="label-upper px-6 py-3 text-ink-muted">{{ __('admin.orders.channel') }}</th>
                                    <th scope="col" class="label-upper px-6 py-3 text-ink-muted">{{ __('admin.orders.payment_status') }}</th>
                                    <th scope="col" class="label-upper px-6 py-3 text-right text-ink-muted">{{ __('admin.orders.amount') }}</th>
                                    <th scope="col" class="label-upper px-6 py-3 text-ink-muted">{{ __('admin.orders.paid_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->payments as $payment)
                                    <tr class="border-t border-line align-top">
                                        <td class="px-6 py-4">
                                            <span class="block tabular">{{ $payment->reference }}</span>
                                            <span class="block text-ink-muted">{{ $payment->provider }}</span>
                                            @if (filled($payment->provider_reference))
                                                <span class="block text-ink-muted">
                                                    {{ __('admin.orders.gateway_reference') }}: <span class="tabular">{{ $payment->provider_reference }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ filled($payment->channel) ? __('shop.payment.channel_'.$payment->channel) : '—' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <x-ui.badge :tone="$payment->status->badgeTone()">{{ $payment->status->label() }}</x-ui.badge>
                                        </td>
                                        <td class="px-6 py-4 text-right tabular">{{ money($payment->amount) }}</td>
                                        <td class="px-6 py-4">
                                            {{ $payment->paid_at ? localized_date($payment->paid_at) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($captured && $captured->refunds->isNotEmpty())
                    <div class="border-t border-line px-6 py-4">
                        <h4 class="label-upper text-ink-muted">{{ __('admin.refunds.history') }}</h4>

                        <ul class="mt-3 space-y-2 text-body-sm">
                            @foreach ($captured->refunds as $refund)
                                <li class="flex items-center justify-between gap-4">
                                    <span class="tabular">{{ money($refund->amount) }}</span>
                                    <span class="text-ink-muted">{{ __('admin.refunds.status_'.$refund->status->value) }}</span>
                                    <span class="text-ink-muted">{{ localized_date($refund->created_at) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-line bg-canvas shadow-card">
                <h3 class="border-b border-line px-6 py-4 font-display text-heading-sm">{{ __('admin.orders.timeline') }}</h3>

                @if ($order->statusHistory->isEmpty())
                    <p class="px-6 py-4 text-body-sm text-ink-muted">{{ __('admin.orders.no_history') }}</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($order->statusHistory as $entry)
                            <li class="px-6 py-4 text-body-sm">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <x-ui.badge :tone="$entry->status->badgeTone()">{{ $entry->status->label() }}</x-ui.badge>

                                    <span class="text-ink-muted">
                                        {{ localized_date($entry->created_at) }} · {{ localized_time($entry->created_at) }}
                                    </span>
                                </div>

                                @if (filled($entry->note))
                                    <p class="mt-2 text-ink-soft">{{ $entry->note }}</p>
                                @endif

                                <p class="mt-1 text-ink-muted">
                                    {{ __('admin.orders.history_by') }}
                                    {{ $entry->changedBy?->name ?? __('admin.orders.changed_by_system') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="min-w-0 space-y-6">
            <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.orders.update_status') }}</h3>

                @if ($nextStatuses->isEmpty())
                    <p class="mt-3 text-body-sm text-ink-muted">{{ __('admin.orders.no_history') }}</p>
                @else
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="status" class="label-upper text-ink">{{ __('admin.orders.next_status') }}</label>
                            <select id="status" name="status" class="field mt-2">
                                @foreach ($nextStatuses as $status)
                                    <option value="{{ $status->value }}" @selected(old('status') === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="note" class="label-upper text-ink">{{ __('admin.orders.status_note') }}</label>
                            <textarea id="note" name="note" rows="2" class="field mt-2">{{ old('note') }}</textarea>
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.orders.status_note_hint') }}</p>
                        </div>

                        <x-ui.button type="submit" size="compact" class="w-full">
                            {{ __('admin.orders.save_status') }}
                        </x-ui.button>
                    </form>

                    <p class="mt-3 text-body-sm text-ink-muted">{{ __('admin.orders.cancel_hint') }}</p>
                @endif
            </section>

            @if ($canReconcile)
                <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                    <h3 class="font-display text-heading-sm">{{ __('admin.orders.reconcile_heading') }}</h3>
                    <p class="mt-2 text-body-sm text-ink-muted">{{ __('admin.orders.reconcile_hint') }}</p>

                    <form method="POST" action="{{ route('admin.orders.reconcile', $order) }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label for="channel" class="label-upper text-ink">{{ __('admin.orders.reconcile_channel') }}</label>
                            <select id="channel" name="channel" class="field mt-2">
                                <option value="cash" @selected(old('channel') === 'cash')>{{ __('admin.orders.channel_cash') }}</option>
                                <option value="transfer" @selected(old('channel') === 'transfer')>{{ __('admin.orders.channel_transfer') }}</option>
                            </select>
                            @error('channel')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-ui.button type="submit" variant="secondary" size="compact" class="w-full">
                            {{ __('admin.orders.reconcile') }}
                        </x-ui.button>
                    </form>
                </section>
            @endif

            @if ($captured && $refundable > 0)
                <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                    <h3 class="font-display text-heading-sm">{{ __('admin.refunds.heading') }}</h3>
                    <p class="mt-2 text-body-sm text-ink-muted">{{ __('admin.refunds.hint') }}</p>
                    <p class="mt-2 text-body-sm text-ink">
                        {{ __('admin.refunds.refundable', ['amount' => money($refundable)]) }}
                    </p>

                    <form method="POST" action="{{ route('admin.orders.refunds.store', $order) }}" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="payment_id" value="{{ $captured->id }}">

                        <div>
                            <label for="amount" class="label-upper text-ink">{{ __('admin.refunds.amount') }}</label>
                            <input
                                id="amount"
                                name="amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                max="{{ $refundable }}"
                                value="{{ old('amount', number_format($refundable, 2, '.', '')) }}"
                                @class(['field mt-2 tabular', 'field-error' => $errors->has('amount')])
                            >
                            @error('amount')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reason" class="label-upper text-ink">{{ __('admin.refunds.reason') }}</label>
                            <textarea id="reason" name="reason" rows="2" class="field mt-2" placeholder="{{ __('admin.refunds.reason_placeholder') }}">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-ui.button type="submit" variant="danger" size="compact" class="w-full">
                            {{ __('admin.refunds.submit') }}
                        </x-ui.button>
                    </form>
                </section>
            @endif

            <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.orders.delivery_heading') }}</h3>

                <address class="mt-3 text-body-sm not-italic text-ink-soft">
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
                        <p class="label-upper text-ink-muted">{{ __('mail.labels.notes') }}</p>
                        <p class="mt-2 text-body-sm text-ink-soft">{{ $order->notes }}</p>
                    </div>
                @endif

                @if ($order->user)
                    <a href="{{ route('admin.customers.show', $order->user) }}" class="link mt-4 inline-block text-body-sm">
                        {{ __('admin.orders.view_customer') }}
                    </a>
                @endif
            </section>
        </div>
    </div>
</x-admin-layout>
