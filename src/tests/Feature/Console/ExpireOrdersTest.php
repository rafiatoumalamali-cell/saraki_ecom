<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;

beforeEach(function () {
    enable_paystack();
});

/**
 * An online order whose stock was reserved minutes ago.
 *
 * @return array{0: Order, 1: ProductVariant}
 */
function held_order(string $placedAt = '-45 minutes', string $method = 'paystack'): array
{
    $variant = sellable_variant(stock: 3);

    $order = Order::factory()->create([
        'payment_method' => $method,
        'placed_at' => now()->modify($placedAt),
    ]);

    $order->items()->delete();
    $order->items()->create([
        'product_id' => $variant->product_id,
        'variant_id' => $variant->id,
        'product_name' => 'Boubou Royal',
        'variant_label' => 'M / Noir',
        'unit_price' => 500,
        'quantity' => 2,
        'line_total' => 1000,
    ]);

    $order->forceFill([
        'status' => OrderStatus::Pending,
        'payment_status' => OrderPaymentStatus::Pending,
    ])->save();

    return [$order->refresh(), $variant];
}

test('an unpaid order past its window goes back on the shelf', function () {
    [$order, $variant] = held_order();

    $payment = Payment::factory()->for($order)->create(['status' => PaymentStatus::Pending]);

    $this->artisan('orders:release-expired')->assertSuccessful();

    $order->refresh();

    expect($variant->refresh()->stock_qty)->toBe(5)
        ->and($order->status)->toBe(OrderStatus::Cancelled)
        ->and($order->payment_status)->toBe(OrderPaymentStatus::Failed)
        ->and($order->restocked_at)->not->toBeNull()
        ->and($payment->refresh()->status)->toBe(PaymentStatus::Abandoned);
});

test('an order still inside its window is left alone', function () {
    [$order, $variant] = held_order(placedAt: '-5 minutes');

    $this->artisan('orders:release-expired')->assertSuccessful();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending)
        ->and($variant->refresh()->stock_qty)->toBe(3)
        ->and($order->restocked_at)->toBeNull();
});

test('a paid order is never released', function () {
    [$order, $variant] = held_order();

    $order->forceFill(['payment_status' => OrderPaymentStatus::Paid])->save();

    $this->artisan('orders:release-expired')->assertSuccessful();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending)
        ->and($variant->refresh()->stock_qty)->toBe(3);
});

test('a cash on delivery order is not an online reservation', function () {
    [$order, $variant] = held_order(method: 'cod');

    $order->forceFill(['payment_status' => OrderPaymentStatus::Unpaid])->save();

    $this->artisan('orders:release-expired')->assertSuccessful();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending)
        ->and($variant->refresh()->stock_qty)->toBe(3);
});
