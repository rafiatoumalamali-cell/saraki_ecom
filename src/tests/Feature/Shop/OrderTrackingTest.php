<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    enable_paystack();
});

test('an order belongs to the account that placed it', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $order = Order::factory()->for($owner)->create();

    $this->actingAs($stranger)
        ->get(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertNotFound();
});

test('the order list shows the shopper their own orders only', function () {
    $user = User::factory()->create();

    $mine = Order::factory()->for($user)->create();
    $theirs = Order::factory()->create();

    $this->actingAs($user)
        ->get(route('orders.index', 'fr'))
        ->assertOk()
        ->assertSee($mine->order_number)
        ->assertDontSee($theirs->order_number);
});

test('the order page shows the steps the order has reached', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $orders = app(OrderService::class);
    $orders->recordStatus($order, OrderStatus::Pending, null, 'Commande reçue.');
    $orders->recordStatus($order, OrderStatus::Confirmed, null, 'Paiement reçu.');

    $this->actingAs($user)
        ->get(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertOk()
        ->assertSee(__('shop.orders.timeline_pending'))
        ->assertSee(__('shop.orders.timeline_confirmed'))
        ->assertSee(__('shop.orders.timeline_ready'))
        ->assertSee('Paiement reçu.')
        ->assertSee($order->order_number);
});

test('an unpaid online order offers a way to try again', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create(['payment_method' => 'paystack']);
    $order->forceFill(['payment_status' => OrderPaymentStatus::Pending])->save();

    $this->actingAs($user)
        ->get(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertOk()
        ->assertSee(__('shop.orders.awaiting_payment'))
        ->assertSee('action="'.route('orders.pay', ['locale' => 'fr', 'order' => $order->order_number]).'"', false);
});

test('a paid order is not sent to the gateway again', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create(['payment_method' => 'paystack']);
    $order->forceFill(['payment_status' => OrderPaymentStatus::Paid])->save();

    $this->actingAs($user)
        ->post(route('orders.pay', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertSessionHas('info');

    expect($order->refresh()->payments()->count())->toBe(0);
});

test('a cancelled order cannot be paid', function () {
    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create(['payment_method' => 'paystack']);
    $order->forceFill(['status' => OrderStatus::Cancelled])->save();

    $this->actingAs($user)
        ->post(route('orders.pay', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertSessionHas('error');
});

test('the retry link opens a fresh attempt at the gateway', function () {
    Http::fake([
        '*/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.test/pay/retry',
                'access_code' => 'retry123',
                'reference' => 'PS-RETRY-1',
            ],
        ]),
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create(['payment_method' => 'paystack']);
    $order->forceFill(['payment_status' => OrderPaymentStatus::Pending])->save();

    $this->actingAs($user)
        ->post(route('orders.pay', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertRedirect('https://checkout.paystack.test/pay/retry');

    expect($order->refresh()->payments()->count())->toBe(1);
});

test('a taken reference is retired and a fresh attempt opens checkout', function () {
    Http::fake([
        '*/transaction/initialize' => Http::sequence()
            ->push(['status' => false, 'message' => 'Duplicate Transaction Reference'], 400)
            ->push([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.test/pay/fresh',
                    'access_code' => 'fresh123',
                    'reference' => 'PS-FRESH-1',
                ],
            ]),
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->for($user)->create(['payment_method' => 'paystack']);
    $order->forceFill(['payment_status' => OrderPaymentStatus::Pending])->save();

    // The previous attempt died after Paystack had already seen its reference.
    $order->payments()->create([
        'provider' => 'paystack',
        'reference' => $order->order_number.'-PAY-1',
        'amount' => $order->total,
        'currency' => $order->currency,
        'status' => PaymentStatus::Initialized,
    ]);

    $this->actingAs($user)
        ->post(route('orders.pay', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertRedirect('https://checkout.paystack.test/pay/fresh');

    $payments = $order->payments()->orderBy('id')->get();

    expect($payments)->toHaveCount(2)
        ->and($payments[0]->status)->toBe(PaymentStatus::Failed)
        ->and($payments[1]->status)->toBe(PaymentStatus::Pending)
        ->and($payments[1]->provider_reference)->toBe('PS-FRESH-1');
});

test('a guest is sent to sign in before seeing an order', function () {
    $order = Order::factory()->create();

    $this->get(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertRedirect(route('login'));
});
