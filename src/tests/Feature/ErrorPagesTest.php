<?php

use App\Models\User;

test('unknown storefront urls render the branded localized 404', function () {
    $this->get('/fr/does-not-exist')
        ->assertNotFound()
        ->assertSee(__('errors.not_found.title'))
        ->assertSee(route('home', 'fr'), false);
});

test('the english 404 uses the english copy', function () {
    $this->get('/en/does-not-exist')
        ->assertNotFound()
        ->assertSee(__('errors.not_found.title', [], 'en'));
});

test('non-admin users get the branded 403 on admin routes', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/admin/dashboard')
        ->assertForbidden()
        ->assertSee(__('errors.forbidden.title'));
});

test('every error template renders without a database or exception context', function () {
    foreach ([403, 404, 419, 429, 500, 503] as $code) {
        expect(view("errors.{$code}")->render())->toContain('<html');
    }
});
