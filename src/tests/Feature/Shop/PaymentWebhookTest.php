<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\PaymentFailed;
use App\Mail\PaymentReceived;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Paystack fixtures
|--------------------------------------------------------------------------
*/

/**
 * An online order waiting for its payment, with one attempt open.
 *
 * @return array{0: Order, 1: Payment}
 */
function pending_paystack_order(float $amount = 1000.0, string $reference = 'SRK-PAY-TEST'): array
{
    $order = Order::factory()->create(['payment_method' => 'paystack']);

    $order->forceFill([
        'status' => OrderStatus::Pending,
        'payment_status' => OrderPaymentStatus::Pending,
    ])->save();

    $payment = Payment::factory()->for($order)->create([
        'reference' => $reference,
        'status' => PaymentStatus::Pending,
    ]);

    // The factory mirrors the order total; tests need to know the figure.
    $payment->update(['amount' => $amount]);

    return [$order->refresh(), $payment->refresh()];
}

/**
 * The event Paystack posts for a successful charge.
 *
 * @return array<string, mixed>
 */
function paystack_charge(Payment $payment, array $overrides = []): array
{
    return [
        'event' => 'charge.success',
        'data' => array_merge([
            'id' => 990011,
            'reference' => $payment->reference,
            'amount' => (int) round((float) $payment->amount * 100),
            'currency' => 'GHS',
            'status' => 'success',
            'channel' => 'card',
        ], $overrides),
    ];
}

beforeEach(function () {
    enable_paystack();
});

test('a webhook that is not signed by Paystack is rejected', function () {
    fake_paystack();

    [, $payment] = pending_paystack_order();

    $body = json_encode(paystack_charge($payment));

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
    ], $body)->assertStatus(400);

    expect($payment->refresh()->status)->toBe(PaymentStatus::Pending);
});

test('a webhook signed with the wrong key is rejected', function () {
    fake_paystack();

    [, $payment] = pending_paystack_order();

    [$body, $signature] = paystack_webhook(paystack_charge($payment), 'sk_wrong_key');

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body)->assertStatus(400);

    expect($payment->refresh()->status)->toBe(PaymentStatus::Pending);
});

test('a signed charge marks the order paid and confirms it', function () {
    Mail::fake();
    fake_paystack();

    [$order, $payment] = pending_paystack_order();

    [$body, $signature] = paystack_webhook(paystack_charge($payment));

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body)->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Success)
        ->and($payment->channel)->toBe('card')
        ->and($payment->paid_at)->not->toBeNull()
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Paid)
        ->and($order->status)->toBe(OrderStatus::Confirmed)
        ->and(PaymentEvent::query()->count())->toBe(1);

    Mail::assertSent(PaymentReceived::class, 1);
});

test('an amount that does not match the order is ignored', function () {
    Mail::fake();
    fake_paystack();

    [$order, $payment] = pending_paystack_order(amount: 1000.0);

    [$body, $signature] = paystack_webhook(paystack_charge($payment, ['amount' => 100]));

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body)->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Pending);

    Mail::assertNothingSent();
});

test('the same event delivered twice is only acted on once', function () {
    Mail::fake();
    fake_paystack();

    [$order, $payment] = pending_paystack_order();

    [$body, $signature] = paystack_webhook(paystack_charge($payment));

    $server = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ];

    $this->call('POST', '/webhooks/paystack', [], [], [], $server, $body)->assertOk();
    $this->call('POST', '/webhooks/paystack', [], [], [], $server, $body)->assertOk();

    expect(PaymentEvent::query()->count())->toBe(1)
        ->and($payment->refresh()->status)->toBe(PaymentStatus::Success)
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Paid);

    Mail::assertSent(PaymentReceived::class, 1);
});

test('a second successful charge on the same order is refused', function () {
    fake_paystack();

    [$order, $payment] = pending_paystack_order();

    Payment::factory()->for($order)->succeeded()->create();
    $order->forceFill(['payment_status' => OrderPaymentStatus::Paid])->save();

    [$body, $signature] = paystack_webhook(paystack_charge($payment));

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body)->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Failed);
});

test('a charge for a reference we do not know is acknowledged and logged', function () {
    fake_paystack();

    [$body, $signature] = paystack_webhook([
        'event' => 'charge.success',
        'data' => [
            'id' => 424242,
            'reference' => 'PS-NOT-OURS',
            'amount' => 5000,
            'currency' => 'GHS',
            'status' => 'success',
        ],
    ]);

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body)->assertOk();

    expect(PaymentEvent::query()->sole()->payment_id)->toBeNull();
});

test('a failed charge leaves the order unpaid and warns the customer', function () {
    Mail::fake();
    fake_paystack();

    [$order, $payment] = pending_paystack_order();

    [$body, $signature] = paystack_webhook([
        'event' => 'charge.failed',
        'data' => ['id' => 515151, 'reference' => $payment->reference, 'status' => 'failed'],
    ]);

    $this->call('POST', '/webhooks/paystack', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
    ], $body)->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Failed)
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Failed);

    Mail::assertSent(PaymentFailed::class, 1);
});

test('the callback verifies the transaction before trusting the query string', function () {
    Mail::fake();

    [$order, $payment] = pending_paystack_order();

    fake_paystack([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => paystack_charge($payment)['data'],
        ]),
    ]);

    $this->get(route('payment.callback', ['locale' => 'fr', 'reference' => $payment->reference]))
        ->assertRedirect(route('orders.show', ['locale' => 'fr', 'order' => $order->order_number]))
        ->assertSessionHas('success');

    expect($payment->refresh()->status)->toBe(PaymentStatus::Success)
        ->and($order->refresh()->payment_status)->toBe(OrderPaymentStatus::Paid);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/transaction/verify/'));
});

test('a forged callback reference cannot mark anything paid', function () {
    $this->get(route('payment.callback', ['locale' => 'fr', 'reference' => 'PS-FORGED']))
        ->assertRedirect(route('orders.index', 'fr'))
        ->assertSessionHas('error');
});

test('a callback without a reference goes back to the order list', function () {
    $this->get(route('payment.callback', 'fr'))
        ->assertRedirect(route('orders.index', 'fr'))
        ->assertSessionHas('error');
});
