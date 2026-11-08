@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;">{{ __('mail.received.title') }}</h1>

    <p style="margin:0 0 8px;">
        {{ __('mail.received.intro', ['name' => $order->delivery_name, 'amount' => money($payment->amount)]) }}
    </p>

    @include('mail.partials.order-summary', ['order' => $order])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.6;">
        <tr>
            <td style="padding:4px 0;width:130px;color:#6b6459;vertical-align:top;">{{ __('mail.labels.reference') }}</td>
            <td style="padding:4px 0;">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.gateway_reference') }}</td>
            <td style="padding:4px 0;">{{ $payment->reference }}</td>
        </tr>
        @if (filled($payment->channel))
            <tr>
                <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.channel') }}</td>
                <td style="padding:4px 0;">{{ __('shop.payment.channel_'.$payment->channel) }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.paid_on') }}</td>
            <td style="padding:4px 0;">{{ localized_date($payment->paid_at) }}</td>
        </tr>
    </table>

    <p style="margin:16px 0 0;color:#6b6459;font-size:13px;">{{ __('mail.received.next_step') }}</p>

    <p style="margin:22px 0 0;">
        <a
            href="{{ route('orders.show', ['locale' => app()->getLocale(), 'order' => $order->order_number]) }}"
            style="display:inline-block;background:#1c1c1c;color:#f4efe6;padding:12px 22px;text-decoration:none;font-size:13px;letter-spacing:1px;text-transform:uppercase;"
        >{{ __('mail.received.cta') }}</a>
    </p>
@endsection
