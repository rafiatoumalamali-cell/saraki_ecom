<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Exceptions\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentMethods;
use App\Services\PaymentService;
use App\Services\ShippingZones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly ShippingZones $zones,
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
        private readonly PaymentMethods $methods,
    ) {}

    public function show(Request $request, string $locale): View|RedirectResponse
    {
        $cart = $this->carts->current();

        if ($cart === null || ! $cart->items()->exists()) {
            return redirect()
                ->route('cart.index', $locale)
                ->with('error', __('shop.checkout.empty_cart'));
        }

        if ($this->carts->soldOutLines($cart)->isNotEmpty()) {
            return redirect()
                ->route('cart.index', $locale)
                ->with('warning', __('shop.checkout.fix_cart'));
        }

        $user = $request->user();

        return view('shop.checkout', [
            'items' => $cart->items()->with('variant.product')->get(),
            'totals' => $this->carts->totals($cart),
            'zones' => $this->zones->all(),
            'methods' => $this->methods->options(),
            'user' => $user,
            'seo' => [
                'title' => __('shop.checkout.title'),
                'canonical' => route('checkout.show', $locale),
                'robots' => 'noindex,nofollow',
                'alternates' => [],
            ],
        ]);
    }

    public function store(CheckoutRequest $request, string $locale): RedirectResponse
    {
        $cart = $this->carts->current();

        if ($cart === null || ! $cart->items()->exists()) {
            return redirect()
                ->route('cart.index', $locale)
                ->with('error', __('shop.checkout.empty_cart'));
        }

        $data = $request->validated();
        $zone = $this->zones->find($data['shipping_zone'] ?? null);

        $order = $this->orders->place($request->user(), $cart, [
            'name' => $data['delivery_name'],
            'phone' => $data['delivery_phone'],
            'email' => $data['delivery_email'] ?? $request->user()->email,
            'address' => $data['delivery_address'],
            'city' => $zone['name'] ?? $data['delivery_city'],
            'notes' => $data['notes'] ?? null,
            'payment_method' => $data['payment_method'],
        ], $this->zones->feeFor($data['shipping_zone'] ?? null));

        if ($order->payment_method === PaymentMethods::PAYSTACK) {
            try {
                $result = $this->payments->start(
                    $order,
                    $order->delivery_email ?: $request->user()->email,
                    route('payment.callback', $locale),
                );
            } catch (PaymentGatewayException) {
                // The order stands with its stock reserved; the customer can retry
                // from the order page before the reservation expires.
                return $this->toOrder($order, $locale)
                    ->with('error', __('shop.checkout.gateway_unavailable'));
            }

            return redirect()->away($result['url']);
        }

        return $this->toOrder($order, $locale)->with('success', __('shop.checkout.placed'));
    }

    private function toOrder(Order $order, string $locale): RedirectResponse
    {
        return redirect()->route('orders.show', [
            'locale' => $locale,
            'order' => $order->order_number,
        ]);
    }
}
