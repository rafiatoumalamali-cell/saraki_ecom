@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;">
        {{ $forAdmin ? __('mail.placed.admin_title') : __('mail.placed.title', ['number' => $order->order_number]) }}
    </h1>

    <p style="margin:0 0 8px;">
        {{ $forAdmin
            ? __('mail.placed.admin_intro', ['number' => $order->order_number, 'name' => $order->delivery_name])
            : __('mail.placed.intro', ['name' => $order->delivery_name]) }}
    </p>

    @include('mail.partials.order-summary', ['order' => $order])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.6;">
        <tr>
            <td style="padding:4px 0;width:130px;color:#6b6459;vertical-align:top;">{{ __('mail.labels.reference') }}</td>
            <td style="padding:4px 0;">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.placed_on') }}</td>
            <td style="padding:4px 0;">{{ localized_date($order->placed_at) }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.payment') }}</td>
            <td style="padding:4px 0;">
                {{ __('shop.checkout.method_'.$order->payment_method) }} — {{ $order->payment_status->label() }}
            </td>
        </tr>
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.delivery') }}</td>
            <td style="padding:4px 0;">
                {{ $order->delivery_name }}<br>
                {{ $order->delivery_address }}<br>
                {{ $order->delivery_city }}<br>
                {{ $order->delivery_phone }}
            </td>
        </tr>
        @if (filled($order->notes))
            <tr>
                <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.notes') }}</td>
                <td style="padding:4px 0;">{{ $order->notes }}</td>
            </tr>
        @endif
    </table>

    <p style="margin:22px 0 0;">
        <a
            href="{{ route('orders.show', ['locale' => app()->getLocale(), 'order' => $order->order_number]) }}"
            style="display:inline-block;background:#1c1c1c;color:#f4efe6;padding:12px 22px;text-decoration:none;font-size:13px;letter-spacing:1px;text-transform:uppercase;"
        >{{ $forAdmin ? __('mail.placed.admin_cta') : __('mail.placed.cta') }}</a>
    </p>
@endsection
