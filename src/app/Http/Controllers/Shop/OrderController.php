<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Exceptions\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request, string $locale): View
    {
        return view('shop.orders.index', [
            'orders' => $request->user()
                ->orders()
                ->withCount('items')
                ->orderByDesc('placed_at')
                ->orderByDesc('id')
                ->paginate(10),
            'seo' => [
                'title' => __('shop.orders.title'),
                'canonical' => route('orders.index', $locale),
                'robots' => 'noindex,follow',
                'alternates' => [],
            ],
        ]);
    }

    public function show(string $locale, Order $order): View
    {
        $this->guard($order);

        $order->load(['items', 'payments', 'statusHistory.changedBy']);

        return view('shop.orders.show', [
            'order' => $order,
            'timeline' => $this->timeline($order),
            'seo' => [
                'title' => __('shop.orders.reference', ['number' => $order->order_number]),
                'canonical' => route('orders.show', ['locale' => $locale, 'order' => $order->order_number]),
                'robots' => 'noindex,nofollow',
                'alternates' => [],
            ],
        ]);
    }

    /**
     * The retry link on the order page and in the failure email.
     */
    public function pay(string $locale, Order $order): RedirectResponse
    {
        $this->guard($order);

        if ($order->payment_status === OrderPaymentStatus::Paid) {
            return back()->with('info', __('shop.orders.already_paid'));
        }

        if ($order->status === OrderStatus::Cancelled || $order->restocked_at !== null) {
            return back()->with('error', __('shop.orders.cancelled_cannot_pay'));
        }

        try {
            $result = $this->payments->start(
                $order,
                $order->delivery_email ?: auth()->user()->email,
                route('payment.callback', $locale),
            );
        } catch (PaymentGatewayException) {
            return back()->with('error', __('shop.checkout.gateway_unavailable'));
        }

        return redirect()->away($result['url']);
    }

    /**
     * The fulfilment steps of this order, with the moment each one was reached.
     *
     * @return array<int, array{status: OrderStatus, reached_at: mixed, note: ?string, done: bool}>
     */
    private function timeline(Order $order): array
    {
        $history = $order->statusHistory->keyBy(fn ($entry) => $entry->status->value);

        return collect([
            OrderStatus::Pending,
            OrderStatus::Confirmed,
            OrderStatus::InProduction,
            OrderStatus::Ready,
            OrderStatus::Delivered,
        ])->map(fn (OrderStatus $status) => [
            'status' => $status,
            'reached_at' => $history->get($status->value)?->created_at,
            'note' => $history->get($status->value)?->note,
            'done' => $history->has($status->value),
        ])->all();
    }

    /**
     * An order is private to the account that placed it; anything else is a 404 so
     * the reference cannot be probed.
     */
    private function guard(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 404);
    }
}
