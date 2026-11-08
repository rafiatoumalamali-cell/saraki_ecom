<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlaced;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Checkout factory
|--------------------------------------------------------------------------
|
| The same delivery form every test fills in, so a test only has to say what
| makes it different.
|
*/

function checkout_payload(array $overrides = []): array
{
    return array_merge([
        'delivery_name' => 'Ama Mensah',
        'delivery_phone' => '+233 24 000 0000',
        'delivery_email' => 'ama@example.com',
        'delivery_address' => '12 Independence Avenue',
        'shipping_zone' => 'Grand Accra',
        'payment_method' => 'paystack',
        'notes' => null,
    ], $overrides);
}

function fake_paystack_initialization(string $url = 'https://checkout.paystack.test/pay/abc'): void
{
    Http::fake([
        '*/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => $url,
                'access_code' => 'abc123',
                'reference' => 'PS-INIT-1',
            ],
        ]),
    ]);
}

beforeEach(function () {
    enable_paystack();
    Mail::fake();
});

test('a visitor has to sign in before reaching the till', function () {
    $this->get(route('checkout.show', 'fr'))->assertRedirect(route('login'));
});

test('an empty basket is sent back to the shop', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('checkout.show', 'fr'))
        ->assertRedirect(route('cart.index', 'fr'))
        ->assertSessionHas('error');
});

test('the till shows the delivery form, the zones and the enabled methods', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 1);

    $this->get(route('checkout.show', 'fr'))
        ->assertOk()
        ->assertSee(__('shop.checkout.delivery_name'))
        ->assertSee('Grand Accra')
        ->assertSee('Kumasi')
        ->assertSee(__('shop.checkout.method_paystack'))
        ->assertDontSee(__('shop.checkout.method_cod'));
});

test('a card checkout reserves the stock and hands the shopper to Paystack', function () {
    fake_paystack_initialization();

    $user = User::factory()->create();
    $variant = sellable_variant(price: 500, stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 2);

    $response = $this->post(route('checkout.store', 'fr'), checkout_payload());

    $order = Order::query()->sole();

    $response->assertRedirect('https://checkout.paystack.test/pay/abc');

    expect($order->user_id)->toBe($user->id)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(OrderPaymentStatus::Pending)
        // 2 × 500 + 30 GHS delivery to Grand Accra.
        ->and((float) $order->subtotal)->toBe(1000.0)
        ->and((float) $order->shipping_fee)->toBe(30.0)
        ->and((float) $order->total)->toBe(1030.0)
        ->and($order->order_number)->toMatch('/^SRK-\d{4}-\d{6}$/')
        ->and($variant->refresh()->stock_qty)->toBe(3)
        ->and(CartItem::query()->count())->toBe(0);

    // The amount crosses the gateway in pesewas, never in cedis.
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/transaction/initialize')
        && $request['amount'] === 103000
        && $request['currency'] === 'GHS');

    $payment = $order->payments()->sole();

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->provider_reference)->toBe('PS-INIT-1');
});

test('an order keeps a snapshot of what was bought', function () {
    fake_paystack_initialization();

    $user = User::factory()->create();
    $variant = sellable_variant(price: 500, stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 2);

    $this->post(route('checkout.store', 'fr'), checkout_payload());

    $item = Order::query()->sole()->items()->sole();

    expect($item->product_id)->toBe($variant->product_id)
        ->and($item->variant_id)->toBe($variant->id)
        ->and($item->product_name)->toBe($variant->product->name_fr)
        ->and($item->variant_label)->toBe('M / Noir')
        ->and((float) $item->unit_price)->toBe(500.0)
        ->and((float) $item->line_total)->toBe(1000.0);
});

test('cash on delivery is accepted at once and the stock is held', function () {
    enable_offline_methods();

    $user = User::factory()->create();
    $variant = sellable_variant(price: 400, stock: 4);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 1);

    $response = $this->post(route('checkout.store', 'fr'), checkout_payload([
        'payment_method' => 'cod',
        'shipping_zone' => 'Kumasi',
    ]));

    $order = Order::query()->sole();

    $response->assertRedirect(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]));

    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_status)->toBe(OrderPaymentStatus::Unpaid)
        ->and((float) $order->shipping_fee)->toBe(60.0)
        ->and($order->payments()->count())->toBe(0)
        ->and($variant->refresh()->stock_qty)->toBe(3);

    Mail::assertSent(OrderPlaced::class, 2);
});

test('a piece that ran out between the basket and the till is refused', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 3);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 2);

    // Someone else bought the last two while this basket was open.
    $variant->update(['stock_qty' => 1]);

    $this->post(route('checkout.store', 'fr'), checkout_payload())
        ->assertSessionHasErrors('cart');

    expect(Order::query()->count())->toBe(0)
        ->and($variant->refresh()->stock_qty)->toBe(1);
});

test('a delivery zone that is not on the list is refused', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 1);

    $this->post(route('checkout.store', 'fr'), checkout_payload(['shipping_zone' => 'Lyon']))
        ->assertSessionHasErrors('shipping_zone');

    expect(Order::query()->count())->toBe(0)
        ->and($variant->refresh()->stock_qty)->toBe(5);
});

test('a payment method the shop does not offer is refused', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 1);

    $this->post(route('checkout.store', 'fr'), checkout_payload(['payment_method' => 'cod']))
        ->assertSessionHasErrors('payment_method');

    expect(Order::query()->count())->toBe(0);
});

test('a gateway outage keeps the order and its reservation for a retry', function () {
    Http::fake([
        '*/transaction/initialize' => Http::response(['status' => false, 'message' => 'Service unavailable'], 503),
    ]);

    $user = User::factory()->create();
    $variant = sellable_variant(price: 500, stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 2);

    $response = $this->post(route('checkout.store', 'fr'), checkout_payload());

    $order = Order::query()->sole();

    $response->assertRedirect(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertSessionHas('error');

    expect($order->payments()->sole()->status)->toBe(PaymentStatus::Failed)
        ->and($variant->refresh()->stock_qty)->toBe(3);
});
