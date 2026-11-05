<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['auth', 'admin'])
        ->get('/admin-preview', fn () => 'admin area')
        ->name('admin.preview');
});

test('guests are sent to the login screen', function () {
    $this->get('/admin-preview')->assertRedirect('/login');
});

test('customers are forbidden from admin routes', function () {
    $this->actingAs(User::factory()->create())->get('/admin-preview')->assertForbidden();
});

test('admins can open admin routes', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin-preview')
        ->assertOk()
        ->assertSee('admin area');
});

test('admins bypass every policy while customers keep theirs', function () {
    Gate::define('view-secret', fn (User $user) => $user->id === 999999);

    expect(Gate::forUser(User::factory()->admin()->create())->allows('view-secret'))->toBeTrue()
        ->and(Gate::forUser(User::factory()->create())->allows('view-secret'))->toBeFalse();
});
