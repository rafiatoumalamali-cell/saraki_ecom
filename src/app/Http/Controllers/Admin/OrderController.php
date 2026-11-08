<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'payment' => ['nullable', Rule::enum(OrderPaymentStatus::class)],
            'q' => ['nullable', 'string', 'max:60'],
        ]);

        $term = trim((string) ($filters['q'] ?? ''));

        $orders = Order::query()
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['payment'] ?? null), fn ($query) => $query->where('payment_status', $filters['payment']))
            ->when($term !== '', fn ($query) => $query->where(function ($search) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $search->where('order_number', 'like', $like)
                    ->orWhere('delivery_name', 'like', $like)
                    ->orWhere('delivery_phone', 'like', $like);
            }))
            ->withCount('items')
            ->orderByDesc('placed_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => OrderPaymentStatus::cases(),
            'counts' => [
                'awaiting' => Order::query()->where('status', OrderStatus::Pending->value)->count(),
                'paid' => Order::query()->where('payment_status', OrderPaymentStatus::Paid->value)->count(),
                'in_production' => Order::query()->where('status', OrderStatus::InProduction->value)->count(),
            ],
            'title' => __('admin.orders.title'),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'user', 'payments.refunds', 'statusHistory.changedBy']);

        $captured = $order->payments->firstWhere('status', PaymentStatus::Success);

        return view('admin.orders.show', [
            'order' => $order,
            'nextStatuses' => collect(OrderStatus::cases())
                ->filter(fn (OrderStatus $status) => $this->orders->canTransition($order->status, $status))
                ->values(),
            'captured' => $captured,
            'refundable' => $captured === null ? 0.0 : $this->payments->refundable($captured),
            'title' => __('admin.orders.reference', ['number' => $order->order_number]),
        ]);
    }

    public function updateStatus(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        $this->orders->transition(
            $order,
            OrderStatus::from($data['status']),
            $request->user(),
            $data['note'] ?? null,
        );

        return back()->with('success', __('admin.orders.status_updated', [
            'number' => $order->order_number,
            'status' => $order->refresh()->status->label(),
        ]));
    }

    /**
     * Cash on delivery and bank transfer land here: the admin confirms the money
     * arrived, and an offline payment record keeps the books straight.
     */
    public function reconcile(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'channel' => ['required', Rule::in(['cash', 'transfer'])],
        ]);

        if ($order->payment_status === OrderPaymentStatus::Paid) {
            return back()->with('info', __('admin.orders.already_paid'));
        }

        $this->payments->reconcileOffline($order, $request->user(), $data['channel']);

        return back()->with('success', __('admin.orders.reconciled', ['number' => $order->order_number]));
    }
}
