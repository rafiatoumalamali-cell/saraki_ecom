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

test('the admin dashboard is reserved for admins', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->get('/admin/dashboard')->assertRedirect('/login');

    $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();

    $this->post(route('locale.update', 'en'));

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Revenue this month');
});

test('the admin dashboard speaks the language of the visitor', function () {
    $admin = User::factory()->admin()->create(['locale' => 'fr']);

    $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Chiffre d’affaires du mois');
});

test('the root of the admin area lands on the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertRedirect(route('admin.dashboard'));
});

test('unbuilt admin sections show a placeholder and unknown ones 404', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);

    $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee('Orders');
    $this->actingAs($admin)->get('/admin/hacking')->assertNotFound();
});

