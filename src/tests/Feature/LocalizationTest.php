<?php

use App\Models\User;

test('the root url redirects to the default locale', function () {
    config(['app.locale' => 'fr']);

    $this->get('/')->assertRedirect('/fr');
});

test('both locales serve the storefront', function (string $locale, string $title) {
    $this->get('/'.$locale)
        ->assertOk()
        ->assertSee('lang="'.$locale.'"', false)
        ->assertSee($title);
})->with([
    ['fr', 'La boutique ouvre en phase 2'],
    ['en', 'The catalogue opens in Phase 2'],
]);

test('an unsupported locale prefix is not routed', function () {
    $this->get('/de')->assertNotFound();
});

test('switching the locale is remembered for the next request', function () {
    $this->post(route('locale.update', 'en'))
        ->assertRedirect()
        ->assertSessionHas('locale', 'en');

    $this->get('/')->assertRedirect('/en');
    $this->get('/login')->assertSee('Forgot your password?');

    $this->post(route('locale.update', 'fr'));
    $this->get('/login')->assertSee('Mot de passe oublié ?');
});

test('a signed-in customer keeps their own language when nothing was chosen yet', function () {
    $customer = User::factory()->create(['locale' => 'en']);

    $this->actingAs($customer)->get('/dashboard')->assertOk()->assertSee('My account');
});

test('saving the profile changes the language of the pages that follow', function () {
    $customer = User::factory()->create(['locale' => 'fr']);

    $this->actingAs($customer)->patch(route('profile.update'), [
        'name' => $customer->name,
        'email' => $customer->email,
        'locale' => 'en',
    ])->assertRedirect(route('profile.edit'));

    expect($customer->refresh()->locale)->toBe('en')
        ->and(session('locale'))->toBe('en');

    $this->get('/dashboard')->assertOk()->assertSee('My account');
});

test('the language switch is offered on every storefront page', function () {
    $this->get('/en')
        ->assertSee('<form method="POST" action="'.route('locale.update', 'fr').'"', false)
        ->assertSee('Change language');
});
