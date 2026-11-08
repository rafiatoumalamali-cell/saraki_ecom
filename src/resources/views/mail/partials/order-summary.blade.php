{{-- Line items and totals, shared by the three order emails. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0;border-collapse:collapse;">
    @foreach ($order->items as $item)
        <tr>
            <td style="padding:8px 0;border-bottom:1px solid #ece6da;font-size:14px;">
                {{ $item->product_name }}
                @if (filled($item->variant_label))
                    <span style="color:#6b6459;">— {{ $item->variant_label }}</span>
                @endif
                <span style="color:#6b6459;">× {{ $item->quantity }}</span>
            </td>
            <td style="padding:8px 0;border-bottom:1px solid #ece6da;font-size:14px;text-align:right;white-space:nowrap;">
                {{ money($item->line_total) }}
            </td>
        </tr>
    @endforeach

    <tr>
        <td style="padding:8px 0 0;font-size:14px;color:#6b6459;">{{ __('shop.cart.subtotal') }}</td>
        <td style="padding:8px 0 0;font-size:14px;text-align:right;">{{ money($order->subtotal) }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;font-size:14px;color:#6b6459;">{{ __('shop.cart.shipping', ['city' => $order->delivery_city]) }}</td>
        <td style="padding:4px 0;font-size:14px;text-align:right;">{{ money($order->shipping_fee) }}</td>
    </tr>
    <tr>
        <td style="padding:10px 0 0;border-top:1px solid #1c1c1c;font-size:15px;font-weight:600;">{{ __('shop.cart.total') }}</td>
        <td style="padding:10px 0 0;border-top:1px solid #1c1c1c;font-size:15px;font-weight:600;text-align:right;">{{ money($order->total) }}</td>
    </tr>
</table>
