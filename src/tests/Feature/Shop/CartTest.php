<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Services\CartService;

test('a guest can put a piece in the basket', function () {
    $variant = sellable_variant(stock: 5);

    $this->post(route('cart.store', 'fr'), ['variant_id' => $variant->id, 'quantity' => 2])
        ->assertRedirect()
        ->assertSessionHas('success');

    $item = CartItem::query()->sole();

    expect($item->variant_id)->toBe($variant->id)
        ->and($item->quantity)->toBe(2)
        // The price the visitor saw is snapshotted with the line.
        ->and((float) $item->unit_price)->toBe(500.0)
        ->and($item->cart->user_id)->toBeNull();
});

test('adding the same piece again tops up the line instead of duplicating it', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 5);

    $this->actingAs($user)
        ->post(route('cart.store', 'fr'), ['variant_id' => $variant->id]);

    $this->post(route('cart.store', 'fr'), ['variant_id' => $variant->id, 'quantity' => 2]);

    expect(CartItem::query()->count())->toBe(1)
        ->and(CartItem::query()->sole()->quantity)->toBe(3);
});

test('the shelf is the ceiling on what can be added', function () {
    $variant = sellable_variant(stock: 2);

    $this->post(route('cart.store', 'fr'), ['variant_id' => $variant->id, 'quantity' => 3])
        ->assertSessionHasErrors('quantity');

    expect(CartItem::query()->count())->toBe(0);
});

test('a piece taken off the shop cannot be added', function () {
    $variant = sellable_variant(stock: 5);
    $variant->update(['is_active' => false]);

    $this->post(route('cart.store', 'fr'), ['variant_id' => $variant->id])
        ->assertSessionHasErrors('variant_id');

    expect(CartItem::query()->count())->toBe(0);
});

test('the basket page lists the line with its variant', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 5);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 2);

    $this->get(route('cart.index', 'fr'))
        ->assertOk()
        ->assertSee($variant->product->name_fr)
        ->assertSee('M / Noir');
});

test('an empty basket says so and offers the shop', function () {
    $this->get(route('cart.index', 'fr'))
        ->assertOk()
        ->assertSee(__('shop.cart.empty'))
        ->assertSee(__('shop.cart.empty_cta'));
});

test('a shopper can change a quantity and take a line out', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 6);

    $this->actingAs($user);
    $item = app(CartService::class)->add($variant, 1);

    $this->patch(route('cart.update', ['locale' => 'fr', 'item' => $item->id]), ['quantity' => 4])
        ->assertRedirect(route('cart.index', 'fr'));

    expect($item->refresh()->quantity)->toBe(4);

    $this->delete(route('cart.destroy', ['locale' => 'fr', 'item' => $item->id]))
        ->assertRedirect(route('cart.index', 'fr'));

    expect(CartItem::query()->count())->toBe(0);
});

test('a quantity above the shelf is refused without touching the line', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 3);

    $this->actingAs($user);
    $item = app(CartService::class)->add($variant, 1);

    $this->patch(route('cart.update', ['locale' => 'fr', 'item' => $item->id]), ['quantity' => 4])
        ->assertSessionHasErrors('quantity');

    expect($item->refresh()->quantity)->toBe(1);
});

test('a line that belongs to someone else is a dead end', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($owner);
    $item = app(CartService::class)->add(sellable_variant(stock: 5), 1);

    $this->actingAs($intruder)
        ->patch(route('cart.update', ['locale' => 'fr', 'item' => $item->id]), ['quantity' => 2])
        ->assertNotFound();
});

test('the basket a guest filled follows them into their account', function () {
    $variant = sellable_variant(stock: 10);

    app(CartService::class)->add($variant, 2);

    $user = User::factory()->create();
    app(CartService::class)->mergeGuestCart($user);

    $owned = Cart::query()->where('user_id', $user->id)->sole();

    expect($owned->items()->count())->toBe(1)
        ->and($owned->items()->sole()->quantity)->toBe(2)
        ->and(Cart::query()->whereNull('user_id')->count())->toBe(0);
});

test('merging never keeps more than the shelf holds', function () {
    $variant = sellable_variant(stock: 3);

    // A line left over from before the shelf shrank.
    app(CartService::class)->resolve()->items()->create([
        'product_id' => $variant->product_id,
        'variant_id' => $variant->id,
        'quantity' => 5,
        'unit_price' => $variant->price,
    ]);

    $user = User::factory()->create();
    app(CartService::class)->mergeGuestCart($user);

    expect(Cart::query()->where('user_id', $user->id)->sole()->items()->sole()->quantity)->toBe(3);
});

test('a line that ran out is flagged before the till', function () {
    $user = User::factory()->create();
    $variant = sellable_variant(stock: 3);

    $this->actingAs($user);
    app(CartService::class)->add($variant, 2);
    $variant->update(['stock_qty' => 1]);

    $this->get(route('cart.index', 'fr'))
        ->assertOk()
        ->assertSee(__('shop.cart.fix_first'))
        ->assertSee(__('shop.cart.unavailable_line'));

    $this->get(route('checkout.show', 'fr'))
        ->assertRedirect(route('cart.index', 'fr'))
        ->assertSessionHas('warning');
});

test('the product page posts the chosen line to the basket', function () {
    $variant = sellable_variant(stock: 3);

    $this->get($variant->product->url('fr'))
        ->assertOk()
        ->assertSee('action="'.route('cart.store', 'fr').'"', false)
        ->assertSee('name="variant_id"', false);
});
