<?php

declare(strict_types=1);

use App\Models\ContactMessage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('the catalogue separates ready-to-wear from made-to-measure', function () {
    Product::factory()->create(['name_fr' => 'Boubou Prêt', 'name_en' => 'Ready Boubou']);
    Product::factory()->madeToMeasure()->create(['name_fr' => 'Boubou Sur Mesure', 'name_en' => 'Tailored Boubou']);

    // Default tab: ready-to-wear only.
    $this->get(route('shop.index', 'fr'))
        ->assertOk()
        ->assertSee('Boubou Prêt')
        ->assertDontSee('Boubou Sur Mesure');

    // Sur-mesure tab shows only tailored pieces.
    $this->get(route('shop.index', ['locale' => 'fr', 'type' => 'made_to_measure']))
        ->assertOk()
        ->assertSee('Boubou Sur Mesure')
        ->assertDontSee('Boubou Prêt');
});

test('a made-to-measure product page offers the request form, not the cart', function () {
    $product = Product::factory()->madeToMeasure()->create(['name_fr' => 'Robe Sur Mesure']);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee('name="fabric"', false)
        ->assertSee('Envoyer ma demande')
        ->assertDontSee('Ajouter au panier');
});

test('a made-to-measure product shows the fabric and tailoring price breakdown', function () {
    $product = Product::factory()->madeToMeasure()->create([
        'fabric_price' => 120.00,
        'fabric_unit' => 'pagne',
        'confection_price' => 450.00,
    ]);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee('Tissu')
        ->assertSee('Confection')
        ->assertSee('/ pagne')
        ->assertSee('propre tissu');
});

test('a ready-to-wear product keeps the normal cart flow', function () {
    $product = Product::factory()->create(['name_fr' => 'Boubou Normal']);

    $this->get($product->url('fr'))
        ->assertOk()
        ->assertSee(route('cart.store', 'fr'), false)
        ->assertDontSee('name="fabric"', false);
});

test('a tailoring request is stored as a contact message with model and fabric', function () {
    Mail::fake();

    $product = Product::factory()->madeToMeasure()->create(['name_fr' => 'Caftan Royal']);

    $this->post(route('products.tailoring', ['locale' => 'fr', 'product' => $product->slug]), [
        'name' => 'Awa Mensah',
        'email' => 'awa@example.com',
        'phone' => '+233 24 000 0000',
        'fabric' => 'own',
        'message' => 'Pour un mariage en décembre.',
    ])->assertRedirect($product->url('fr'));

    $message = ContactMessage::sole();

    expect($message->email)->toBe('awa@example.com')
        ->and($message->product_id)->toBe($product->id)
        ->and($message->subject)->toContain('Caftan Royal')
        ->and($message->message)->toContain('Caftan Royal')
        ->and($message->message)->toContain('propre tissu')
        ->and($message->message)->toContain('mariage')
        ->and($message->message)->not->toContain('http');
});

test('a tailoring request needs a valid fabric choice', function () {
    $product = Product::factory()->madeToMeasure()->create();

    $this->post(route('products.tailoring', ['locale' => 'fr', 'product' => $product->slug]), [
        'name' => 'Awa Mensah',
        'email' => 'awa@example.com',
        'fabric' => 'silk',
    ])->assertSessionHasErrors('fabric');

    expect(ContactMessage::count())->toBe(0);
});

test('ready-to-wear products reject tailoring requests', function () {
    $product = Product::factory()->create();

    $this->post(route('products.tailoring', ['locale' => 'fr', 'product' => $product->slug]), [
        'name' => 'Awa Mensah',
        'email' => 'awa@example.com',
        'fabric' => 'saraki',
    ])->assertNotFound();
});
