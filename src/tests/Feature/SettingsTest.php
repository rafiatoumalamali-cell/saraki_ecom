<?php

use App\Models\User;
use App\Services\Settings;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed(SettingsSeeder::class);
});

function settingsPayload(array $overrides = []): array
{
    return array_replace([
        'store_name' => 'SARAKI Atelier',
        'contact_email' => 'bonjour@saraki.test',
        'contact_phone' => '+233 30 000 0000',
        'store_address' => '12 Oxford Street, Accra',
        'whatsapp_number' => '+233 24 000 0000',
        'map_embed' => '',
        'shipping_zones' => [
            ['name' => 'Grand Accra', 'name_en' => '', 'fee' => '30', 'delivery_days' => '2'],
            ['name' => 'Kumasi', 'name_en' => 'Kumasi', 'fee' => '60', 'delivery_days' => '4'],
        ],
        'payment_methods' => ['paystack' => '1', 'cod' => '0', 'transfer' => '0'],
        'tax_display' => '0',
        'order_expiry_minutes' => '90',
        'social_links' => [
            'instagram' => '',
            'facebook' => 'https://facebook.com/saraki',
            'tiktok' => '',
        ],
    ], $overrides);
}

test('an admin saves the whole shop from one screen', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), settingsPayload())
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('success');

    $settings = app(Settings::class);
    $zones = $settings->get('shipping_zones');

    expect($settings->text('store_name'))->toBe('SARAKI Atelier')
        ->and($settings->text('contact_email'))->toBe('bonjour@saraki.test')
        ->and($settings->text('store_address'))->toBe('12 Oxford Street, Accra')
        ->and($zones)->toHaveCount(2)
        // An English name left blank falls back to the French one instead of
        // leaving an empty cell in the English storefront.
        ->and($zones[0]['name_en'])->toBe('Grand Accra')
        ->and((float) $zones[1]['fee'])->toBe(60.0)
        ->and($settings->get('payment_methods'))->toBe(['paystack' => true, 'cod' => false, 'transfer' => false])
        ->and($settings->bool('tax_display'))->toBeFalse()
        ->and($settings->get('order_expiry_minutes'))->toBe(90)
        ->and($settings->socialLinks())->toBe(['facebook' => 'https://facebook.com/saraki']);

    $this->get(route('contact.show', 'en'))
        ->assertOk()
        ->assertSee('bonjour@saraki.test');
});

test('a shipping row left blank is dropped instead of saved', function () {
    $payload = settingsPayload();
    $payload['shipping_zones'][] = ['name' => '', 'name_en' => '', 'fee' => '', 'delivery_days' => ''];

    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)->put(route('admin.settings.update'), $payload)->assertRedirect();

    expect(app(Settings::class)->get('shipping_zones'))->toHaveCount(2);
});

test('checkout with every payment method switched off is refused', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), settingsPayload([
            'payment_methods' => ['paystack' => '0', 'cod' => '0', 'transfer' => '0'],
        ]))
        ->assertSessionHasErrors('payment_methods');

    expect(app(Settings::class)->get('payment_methods')['paystack'])->toBeTrue();
});

test('a pasted google maps iframe is reduced to its source address', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $snippet = '<iframe src="https://www.google.com/maps/embed?pb=abc" width="600" height="450"></iframe>';

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), settingsPayload(['map_embed' => $snippet]))
        ->assertRedirect();

    expect(app(Settings::class)->mapEmbedUrl())->toBe('https://www.google.com/maps/embed?pb=abc');

    $this->get(route('contact.show', 'fr'))
        ->assertOk()
        ->assertSee('https://www.google.com/maps/embed?pb=abc', false)
        ->assertDontSee('<iframe src="https://www.google.com/maps/embed?pb=abc" width', false);
});

test('a map value that is not a plain address is refused', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), settingsPayload(['map_embed' => '<script>alert(1)</script>']))
        ->assertSessionHasErrors('map_embed');

    expect(app(Settings::class)->mapEmbedUrl())->toBeNull();
});

test('the whatsapp button appears only once a number is configured', function () {
    $this->get(route('home', 'en'))->assertOk()->assertDontSee('wa.me/', false);

    app(Settings::class)->put('whatsapp_number', '+233 24 000 0000');

    $this->get(route('home', 'en'))
        ->assertOk()
        ->assertSee('https://wa.me/233240000000', false);
});

test('the footer publishes the social profiles that have a link', function () {
    $this->get(route('home', 'en'))->assertOk()->assertDontSee('instagram.com/saraki', false);

    app(Settings::class)->put('social_links', [
        'instagram' => 'https://instagram.com/saraki',
        'facebook' => null,
        'tiktok' => null,
    ], 'social');

    $this->get(route('home', 'en'))
        ->assertOk()
        ->assertSee('href="https://instagram.com/saraki"', false);
});

test('saved settings come back on the edit form', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)->put(route('admin.settings.update'), settingsPayload());

    $this->actingAs($admin)->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('SARAKI Atelier')
        ->assertSee('Oxford Street, Accra')
        ->assertSee('Kumasi');
});

test('settings warns when paystack is ticked but the secret key is missing', function () {
    config(['services.paystack.secret_key' => null]);
    commerce_setting('payment_methods', ['paystack' => true, 'cod' => true, 'transfer' => false]);

    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('PAYSTACK_SECRET_KEY');
});

test('the dashboard warns when checkout has no usable payment method', function () {
    config(['services.paystack.secret_key' => null]);
    commerce_setting('payment_methods', ['paystack' => true, 'cod' => false, 'transfer' => false]);

    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('No payment method is currently available', false);
});

test('the settings screen is reserved for the atelier', function () {
    $this->get(route('admin.settings.edit'))->assertRedirect('/login');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});
