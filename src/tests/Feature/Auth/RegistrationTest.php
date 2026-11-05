<?php

use App\Enums\UserRole;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Awa Traore',
        'email' => 'awa@example.com',
        'phone' => '+233 20 123 4567',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::firstWhere('email', 'awa@example.com');

    expect($user->phone)->toBe('+233 20 123 4567')
        ->and($user->locale)->toBe('fr')
        ->and($user->role)->toBe(UserRole::Customer);
});

test('registration ignores injected role and activation fields', function () {
    $this->post('/register', [
        'name' => 'Sneaky User',
        'email' => 'sneaky@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
        'is_active' => false,
    ])->assertSessionHasNoErrors();

    $user = User::firstWhere('email', 'sneaky@example.com');

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->is_active)->toBeTrue();
});
