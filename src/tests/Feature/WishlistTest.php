<?php

use App\Models\Product;
use App\Models\User;

test('a customer saves a piece and finds it in the wishlist', function () {
    $user = User::factory()->create(['locale' => 'en']);
    $product = Product::factory()->create(['name_en' => 'Gold boubou']);

    $this->actingAs($user)
        ->post(route('wishlist.store', 'en'), ['product_id' => $product->id])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->wishlist()->count())->toBe(1);

    $this->actingAs($user)->get(route('wishlist.index', 'en'))
        ->assertOk()
        ->assertSee('Gold boubou');
});

test('saving the same piece twice keeps a single row', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($user)->post(route('wishlist.store', 'fr'), ['product_id' => $product->id]);
    $this->actingAs($user)->post(route('wishlist.store', 'fr'), ['product_id' => $product->id]);

    expect($user->wishlist()->count())->toBe(1);
});

test('a customer removes a piece from the wishlist', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->wishlist()->attach($product->id);

    $this->actingAs($user)
        ->delete(route('wishlist.destroy', ['locale' => 'fr', 'product' => $product->id]))
        ->assertRedirect();

    expect($user->wishlist()->count())->toBe(0);
});

test('a piece that is no longer sold cannot be saved', function () {
    $user = User::factory()->create();
    $product = Product::factory()->inactive()->create();

    $this->actingAs($user)
        ->post(route('wishlist.store', 'fr'), ['product_id' => $product->id])
        ->assertSessionHasErrors('product_id');

    expect($user->wishlist()->count())->toBe(0);
});

test('the wishlist belongs to the signed-in customer', function () {
    $product = Product::factory()->create();
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $owner->wishlist()->attach($product->id);

    $this->actingAs($other)->get(route('wishlist.index', 'fr'))
        ->assertOk()
        ->assertDontSee($product->name_fr);

    $this->actingAs($owner)->get(route('wishlist.index', 'fr'))
        ->assertOk()
        ->assertSee($product->name_fr);
});

test('guests must sign in before saving a piece', function () {
    $product = Product::factory()->create();

    $this->post(route('wishlist.store', 'fr'), ['product_id' => $product->id])->assertRedirect('/login');
    $this->get(route('wishlist.index', 'fr'))->assertRedirect('/login');
});
