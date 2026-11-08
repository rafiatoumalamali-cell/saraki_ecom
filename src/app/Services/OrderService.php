<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlaced;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Turns a basket into an order. Stock is reserved here, inside one transaction
 * with the variant rows locked, so two shoppers cannot buy the last dress at the
 * same time.
 */
class OrderService
{
    public const PAYSTACK = 'paystack';

    public const COD = 'cod';

    public const TRANSFER = 'transfer';

    /**
     * @param  array{name: string, phone: string, email: ?string, address: string, city: string, notes: ?string, payment_method: string}  $delivery
     *
     * @throws ValidationException when the basket is empty or stock ran out
     */
    public function place(User $user, Cart $cart, array $delivery, float $shippingFee = 0.0): Order
    {
        $order = DB::transaction(function () use ($user, $cart, $delivery, $shippingFee) {
            $items = $cart->items()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => __('shop.checkout.empty_cart')]);
            }

            // One lock per variant, taken in a stable order so two checkouts cannot
            // deadlock each other by locking the same rows in opposite directions.
            $variants = ProductVariant::query()
                ->with('product')
                ->whereIn('id', $items->pluck('variant_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $variant = $variants->get($item->variant_id);

                $this->guardSellable($variant, $item);

                $unitPrice = round((float) $variant->price, 2);
                $lineTotal = round($unitPrice * $item->quantity, 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product->translate('name') ?? $variant->product->base_sku,
                    'variant_label' => $variant->label,
                    'unit_price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                ];

                // Reserved now; released again on expiry or cancellation.
                $variant->decrement('stock_qty', $item->quantity);
            }

            $isOnline = $delivery['payment_method'] === self::PAYSTACK;
            $isCod = $delivery['payment_method'] === self::COD;

            $order = Order::create([
                'user_id' => $user->id,
                // Replaced with the readable number below, once the id exists.
                'order_number' => 'TMP-'.Str::upper(Str::random(16)),
                'payment_method' => $delivery['payment_method'],
                'subtotal' => round($subtotal, 2),
                'shipping_fee' => round($shippingFee, 2),
                'total' => round($subtotal + $shippingFee, 2),
                'currency' => config('saraki.currency'),
                'delivery_name' => $delivery['name'],
                'delivery_phone' => $delivery['phone'],
                'delivery_email' => $delivery['email'] ?? null,
                'delivery_address' => $delivery['address'],
                'delivery_city' => $delivery['city'],
                'notes' => $delivery['notes'] ?? null,
                'placed_at' => now(),
            ]);

            // Status is deliberately not mass-assignable, so it is written here.
            $order->forceFill([
                'order_number' => $this->formatOrderNumber($order->id),
                'payment_status' => $isOnline ? OrderPaymentStatus::Pending : OrderPaymentStatus::Unpaid,
            ])->save();

            $order->items()->createMany($lines);

            $this->recordStatus($order, OrderStatus::Pending, null, __('shop.orders.note_placed'));

            if ($isCod) {
                // Cash on delivery needs no gateway, so the shop accepts it at once.
                $this->recordStatus($order, OrderStatus::Confirmed, null, __('shop.orders.note_confirmed'));
            }

            if ($isOnline) {
                $this->createPaymentAttempt($order);
            }

            $cart->items()->delete();

            return $order->load('items', 'payments');
        });

        if ($order->payment_method !== self::PAYSTACK) {
            $this->notifyPlaced($order);
        }

        return $order;
    }

    /**
     * Puts the reserved units back and closes the order. Safe to call twice: the
     * restock timestamp means the shelf is only credited once.
     */
    public function cancel(Order $order, ?User $actor = null, ?string $note = null): Order
    {
        if ($order->status === OrderStatus::Cancelled) {
            return $order;
        }

        if ($order->status === OrderStatus::Delivered) {
            throw ValidationException::withMessages([
                'status' => __('admin.orders.cannot_cancel_delivered'),
            ]);
        }

        DB::transaction(function () use ($order, $actor, $note) {
            $this->restock($order);

            $this->recordStatus($order, OrderStatus::Cancelled, $actor, $note ?? __('shop.orders.note_cancelled'));
        });

        return $order->refresh();
    }

    /**
     * Moves the order along the fulfilment path. Anything else is refused, which is
     * what keeps a delivered order from quietly going back to production.
     */
    public function transition(Order $order, OrderStatus $next, ?User $actor = null, ?string $note = null): Order
    {
        if (! $this->canTransition($order->status, $next)) {
            throw ValidationException::withMessages([
                'status' => __('admin.orders.transition_refused', [
                    'from' => $order->status->label(),
                    'to' => $next->label(),
                ]),
            ]);
        }

        if ($next === OrderStatus::Cancelled) {
            return $this->cancel($order, $actor, $note);
        }

        DB::transaction(function () use ($order, $next, $actor, $note) {
            $this->recordStatus($order, $next, $actor, $note);

            if ($next === OrderStatus::Delivered) {
                $order->forceFill(['completed_at' => now()])->save();
            }
        });

        return $order->refresh();
    }

    public function canTransition(OrderStatus $from, OrderStatus $to): bool
    {
        return in_array($to, match ($from) {
            OrderStatus::Pending => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed => [OrderStatus::InProduction, OrderStatus::Cancelled],
            OrderStatus::InProduction => [OrderStatus::Ready, OrderStatus::Cancelled],
            OrderStatus::Ready => [OrderStatus::Delivered, OrderStatus::Cancelled],
            OrderStatus::Delivered, OrderStatus::Cancelled => [],
        }, true);
    }

    /**
     * Online orders that were never paid have held their stock long enough; the
     * scheduler calls this every few minutes.
     */
    public function releaseExpired(): int
    {
        $cutoff = now()->subMinutes($this->expiryMinutes());

        $orders = Order::query()
            ->where('payment_method', self::PAYSTACK)
            ->where('payment_status', OrderPaymentStatus::Pending)
            ->whereIn('status', [OrderStatus::Pending->value, OrderStatus::Confirmed->value])
            ->where('placed_at', '<=', $cutoff)
            ->get();

        $released = 0;

        foreach ($orders as $order) {
            try {
                $this->cancel($order, null, __('shop.orders.note_expired'));

                $order->payments()
                    ->whereIn('status', [PaymentStatus::Initialized->value, PaymentStatus::Pending->value])
                    ->update(['status' => PaymentStatus::Abandoned->value]);

                $order->forceFill(['payment_status' => OrderPaymentStatus::Failed])->save();
                $released++;
            } catch (Throwable $exception) {
                Log::error('Releasing an expired order failed', [
                    'order' => $order->order_number,
                    'reason' => $exception->getMessage(),
                ]);
            }
        }

        return $released;
    }

    /**
     * A fresh attempt at paying the same order, used by the retry link.
     */
    public function createPaymentAttempt(Order $order): Payment
    {
        $attempt = $order->payments()->count() + 1;

        return $order->payments()->create([
            'provider' => self::PAYSTACK,
            'reference' => $order->order_number.'-PAY-'.$attempt,
            'amount' => $order->total,
            'currency' => $order->currency ?: config('saraki.currency'),
            'status' => PaymentStatus::Initialized,
        ]);
    }

    public function expiryMinutes(): int
    {
        $configured = Setting::query()->where('key', 'order_expiry_minutes')->first()?->value;

        return (int) ($configured ?: config('saraki.order_expiry_minutes'));
    }

    public function restock(Order $order): void
    {
        if ($order->restocked_at !== null) {
            return;
        }

        foreach ($order->items()->get() as $item) {
            if ($item->variant_id === null) {
                continue;
            }

            ProductVariant::query()
                ->whereKey($item->variant_id)
                ->increment('stock_qty', $item->quantity);
        }

        // forceFill: restocked_at is bookkeeping, not a field an admin may edit.
        $order->forceFill(['restocked_at' => now()])->save();
    }

    public function recordStatus(Order $order, OrderStatus $status, ?User $actor = null, ?string $note = null): void
    {
        $order->forceFill(['status' => $status])->save();

        $order->statusHistory()->create([
            'status' => $status,
            'note' => $note,
            'changed_by' => $actor?->id,
        ]);
    }

    public function formatOrderNumber(int $id): string
    {
        return sprintf('%s-%s-%06d', config('saraki.order_number_prefix'), now()->format('Y'), $id);
    }

    private function guardSellable(?ProductVariant $variant, CartItem $item): void
    {
        if ($variant === null || ! $variant->is_active || $variant->product?->is_active === false) {
            throw ValidationException::withMessages([
                'cart' => __('shop.checkout.unavailable', ['label' => $item->variant?->label ?? '']),
            ]);
        }

        if ($variant->stock_qty < $item->quantity) {
            throw ValidationException::withMessages([
                'cart' => __('shop.checkout.insufficient_stock', [
                    'label' => $variant->label,
                    'count' => $variant->stock_qty,
                ]),
            ]);
        }
    }

    private function notifyPlaced(Order $order): void
    {
        try {
            Mail::to($order->delivery_email ?: $order->user->email)
                ->locale($order->user->locale ?? config('app.locale'))
                ->send(new OrderPlaced($order));

            Mail::to(config('saraki.admin_email'))
                ->locale(config('app.locale'))
                ->send(new OrderPlaced($order, forAdmin: true));
        } catch (Throwable $exception) {
            // A failed email must never undo an order that is already recorded.
            Log::error('Order confirmation email failed', [
                'order' => $order->order_number,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
