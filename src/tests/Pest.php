<?php

use App\Models\AppointmentService;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Commerce helpers
|--------------------------------------------------------------------------
|
| Every checkout test needs the same things: a secret key (the real one is never
| in phpunit.xml), the delivery zones the shop sells, and a body signed the way
| Paystack signs one.
|
*/

function commerce_setting(string $key, mixed $value): void
{
    Setting::updateOrCreate(['key' => $key], ['group' => 'commerce', 'value' => $value]);
}

/**
 * The three settings a working Paystack checkout depends on.
 */
function enable_paystack(): void
{
    config(['services.paystack.secret_key' => 'sk_test_commerce']);

    commerce_setting('shipping_zones', [
        ['name' => 'Grand Accra', 'name_en' => 'Greater Accra', 'fee' => 30, 'delivery_days' => 2],
        ['name' => 'Kumasi', 'name_en' => 'Kumasi', 'fee' => 60, 'delivery_days' => 4],
    ]);

    commerce_setting('payment_methods', [
        'paystack' => true,
        'cod' => false,
        'transfer' => false,
    ]);
}

/**
 * Cash on delivery and bank transfer, switched on for the shops that take them.
 */
function enable_offline_methods(): void
{
    commerce_setting('payment_methods', [
        'paystack' => true,
        'cod' => true,
        'transfer' => true,
    ]);
}

/**
 * A sellable line priced in cedis, labelled the way the storefront shows it.
 */
function sellable_variant(float $price = 500.0, int $stock = 10): ProductVariant
{
    $product = Product::factory()->create(['base_price' => $price]);
    $variant = $product->variants()->firstOrFail();

    $variant->update(['size' => 'M', 'color' => 'Noir', 'stock_qty' => $stock]);

    return $variant->refresh();
}

/**
 * Fakes the Paystack API so no test can reach the network. Stubs passed here are
 * tried first, so a test can make one endpoint answer differently.
 *
 * @param  array<string, mixed>  $stubs
 */
function fake_paystack(array $stubs = []): void
{
    Http::preventStrayRequests();

    Http::fake($stubs + [
        '*/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.test/pay',
                'access_code' => 'abc123',
                'reference' => 'PS-INIT-1',
            ],
        ]),
        // Verification is refused by default, which keeps the webhook on its
        // fallback path: the signed payload is what gets applied.
        '*/transaction/verify/*' => Http::response(['status' => false, 'message' => 'Unavailable'], 400),
        '*/refund' => Http::response(['status' => true, 'data' => ['id' => 'RF-1', 'status' => 'pending']]),
    ]);
}

/**
 * The raw body and the HMAC header of a genuine Paystack call.
 *
 * @param  array<string, mixed>  $payload
 * @return array{0: string, 1: string}
 */
function paystack_webhook(array $payload, ?string $secret = null): array
{
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return [$body, hash_hmac('sha512', $body, $secret ?? (string) config('services.paystack.secret_key'))];
}

/*
|--------------------------------------------------------------------------
| Appointment helpers
|--------------------------------------------------------------------------
|
| The atelier opens Monday to Saturday; the Sunday row stays closed so the
| tests can prove a closed day really takes no booking.
|
*/

function open_week(string $opens = '09:00:00', string $closes = '18:00:00'): void
{
    foreach (range(0, 6) as $day) {
        WorkingHour::updateOrCreate(
            ['day_of_week' => $day],
            $day === 0
                ? ['open_time' => null, 'close_time' => null, 'is_closed' => true]
                : ['open_time' => $opens, 'close_time' => $closes, 'is_closed' => false],
        );
    }
}

/**
 * A bookable service; the default 45 minutes lays slots at 09:00 and 09:45.
 */
function booking_service(int $duration = 45, bool $active = true): AppointmentService
{
    return AppointmentService::factory()->create([
        'duration_min' => $duration,
        'is_active' => $active,
    ]);
}

/**
 * The next time a weekday comes round, so no test sits inside the lead time.
 * 1 = Monday … 6 = Saturday; Sunday is closed in these fixtures.
 */
function next_weekday(int $dayOfWeek = 1): Carbon
{
    return today()->next($dayOfWeek)->startOfDay();
}
