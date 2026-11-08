@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;">{{ __('mail.failed.title') }}</h1>

    <p style="margin:0 0 8px;">
        {{ __('mail.failed.intro', ['number' => $order->order_number]) }}
    </p>

    <p style="margin:0 0 8px;">
        {{ __('mail.failed.stock_note', ['minutes' => app(\App\Services\OrderService::class)->expiryMinutes()]) }}
    </p>

    @include('mail.partials.order-summary', ['order' => $order])

    <p style="margin:22px 0 0;">
        <a
            href="{{ $retryUrl }}"
            style="display:inline-block;background:#1c1c1c;color:#f4efe6;padding:12px 22px;text-decoration:none;font-size:13px;letter-spacing:1px;text-transform:uppercase;"
        >{{ __('mail.failed.cta') }}</a>
    </p>
@endsection
