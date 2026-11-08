<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    enable_paystack();
    fake_paystack();
});

/**
 * An order with one captured Paystack payment, ready to be refunded.
 *
 * @return array{0: Order, 1: Payment}
 */
function paid_order(float $amount = 1000.0): array
{
    $order = Order::factory()->create();

    $payment = Payment::factory()->for($order)->succeeded()->create();
    $payment->update(['amount' => $amount]);

    $order->forceFill([
        'status' => OrderStatus::Confirmed,
        'payment_status' => OrderPaymentStatus::Paid,
    ])->save();

    return [$order->refresh(), $payment->refresh()];
}

test('a customer cannot open the shop order book', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.orders.index'))
        ->assertForbidden();
});

test('the order book lists orders and filters them by status', function () {
    $admin = User::factory()->admin()->create();

    $awaiting = Order::factory()->create();
    $confirmed = Order::factory()->create();
    $confirmed->forceFill(['status' => OrderStatus::Confirmed])->save();

    $this->actingAs($admin)
        ->get(route('admin.orders.index', ['status' => 'pending']))
        ->assertOk()
        ->assertSee($awaiting->order_number)
        ->assertDontSee($confirmed->order_number);
});

test('the order screen shows the items, the payment and the history', function () {
    $admin = User::factory()->admin()->create();
    [$order] = paid_order();

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee(__('admin.orders.payments_heading'))
        ->assertSee(__('admin.orders.update_status'));
});

test('an admin moves an order along and the history records who did it', function () {
    $admin = User::factory()->admin()->create();

    $order = Order::factory()->create();
    $order->forceFill(['status' => OrderStatus::Confirmed])->save();

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), ['status' => 'in_production', 'note' => 'Tissé main'])
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::InProduction);

    $entry = $order->statusHistory()->first();

    expect($entry->note)->toBe('Tissé main')
        ->and($entry->changed_by)->toBe($admin->id);
});

test('an order cannot jump steps it has not reached', function () {
    $admin = User::factory()->admin()->create();

    $order = Order::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), ['status' => 'ready'])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
});

test('a delivered order cannot be cancelled', function () {
    $admin = User::factory()->admin()->create();

    $order = Order::factory()->create();
    $order->forceFill(['status' => OrderStatus::Delivered])->save();

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe(OrderStatus::Delivered);
});

test('cancelling puts the reserved pieces back exactly once', function () {
    $variant = sellable_variant(stock: 5);

    // 2 of the 5 are held by this order.
    $variant->decrement('stock_qty', 2);

    $order = Order::factory()->create();
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
    $order->forceFill(['status' => OrderStatus::Confirmed])->save();

    $orders = app(OrderService::class);
    $orders->cancel($order);
    $orders->cancel($order->refresh());

    expect($variant->refresh()->stock_qty)->toBe(5)
        ->and($order->refresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($order->restocked_at)->not->toBeNull();
});

test('cancelling through the order screen restocks too', function () {
    $admin = User::factory()->admin()->create();
    $variant = sellable_variant(stock: 5);
    $variant->decrement('stock_qty', 2);

    $order = Order::factory()->create();
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
    $order->forceFill(['status' => OrderStatus::Confirmed])->save();

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
        ->assertSessionHas('success');

    expect($variant->refresh()->stock_qty)->toBe(5)
        ->and($order->refresh()->status)->toBe(OrderStatus::Cancelled);
});

test('cash on delivery is reconciled into a payment record', function () {
    $admin = User::factory()->admin()->create();

    $order = Order::factory()->create(['payment_method' => 'cod']);
    $order->forceFill([
        'status' => OrderStatus::Confirmed,
        'payment_status' => OrderPaymentStatus::Unpaid,
    ])->save();

    $this->actingAs($admin)
        ->post(route('admin.orders.reconcile', $order), ['channel' => 'cash'])
        ->assertSessionHas('success');

    $payment = $order->refresh()->payments()->sole();

    expect($order->payment_status)->toBe(OrderPaymentStatus::Paid)
        ->and($payment->provider)->toBe('offline')
        ->and($payment->channel)->toBe('cash')
        ->and($payment->status)->toBe(PaymentStatus::Success);
});

test('a full refund marks the order refunded', function () {
    $admin = User::factory()->admin()->create();
    [$order, $payment] = paid_order(amount: 1000.0);

    $this->actingAs($admin)
        ->post(route('admin.orders.refunds.store', $order), [
            'payment_id' => $payment->id,
            'amount' => 1000,
            'reason' => 'Retour client',
        ])
        ->assertSessionHas('success');

    $refund = $payment->refunds()->sole();

    expect($refund->status)->toBe(RefundStatus::Success)
        ->and((float) $refund->amount)->toBe(1000.0)
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Refunded);

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/refund')
        && $request['amount'] === 100000);
});

test('a partial refund leaves the order partially refunded', function () {
    $admin = User::factory()->admin()->create();
    [$order, $payment] = paid_order(amount: 1000.0);

    $this->actingAs($admin)
        ->post(route('admin.orders.refunds.store', $order), [
            'payment_id' => $payment->id,
            'amount' => 400,
        ])
        ->assertSessionHas('success');

    expect($order->refresh()->payment_status)->toBe(OrderPaymentStatus::PartiallyRefunded);

    // Only what is left can go back.
    $this->actingAs($admin)
        ->post(route('admin.orders.refunds.store', $order), [
            'payment_id' => $payment->id,
            'amount' => 700,
        ])
        ->assertSessionHasErrors('amount');

    expect($payment->refunds()->count())->toBe(1);
});

test('two clicks on the same refund only send one to the gateway', function () {
    $admin = User::factory()->admin()->create();
    [$order, $payment] = paid_order(amount: 1000.0);

    Refund::factory()->for($payment)->pending()->create(['amount' => 400]);

    $this->actingAs($admin)
        ->post(route('admin.orders.refunds.store', $order), [
            'payment_id' => $payment->id,
            'amount' => 400,
        ])
        ->assertSessionHas('info');

    expect($payment->refunds()->count())->toBe(1);

    Http::assertNothingSent();
});

test('an offline payment is paid back without calling the gateway', function () {
    $admin = User::factory()->admin()->create();

    $order = Order::factory()->create(['payment_method' => 'cod']);
    $payment = Payment::factory()->for($order)->succeeded()->create([
        'provider' => 'offline',
        'channel' => 'cash',
        'provider_reference' => 'cash-1',
    ]);
    $payment->update(['amount' => 500]);

    $order->forceFill([
        'status' => OrderStatus::Confirmed,
        'payment_status' => OrderPaymentStatus::Paid,
    ])->save();

    $this->actingAs($admin)
        ->post(route('admin.orders.refunds.store', $order), [
            'payment_id' => $payment->id,
            'amount' => 500,
        ])
        ->assertSessionHas('success');

    expect($payment->refunds()->sole()->status)->toBe(RefundStatus::Success)
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Refunded);

    Http::assertNothingSent();
});

test('an unpaid payment cannot be refunded', function () {
    $admin = User::factory()->admin()->create();

    $order = Order::factory()->create();
    $payment = Payment::factory()->for($order)->create([
        'status' => PaymentStatus::Failed,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.orders.refunds.store', $order), [
            'payment_id' => $payment->id,
            'amount' => 100,
        ])
        ->assertSessionHas('error');

    expect($payment->refunds()->count())->toBe(0);
});
