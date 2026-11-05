<?php

use App\Models\CustomerMeasurement;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('the sheet renders in the language the customer chose', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('Measurements')
        ->assertSee('Chest / bust')
        ->assertSee('Around the widest part of the thigh.');
});

test('the sheet renders in french by default', function () {
    $user = User::factory()->create(['locale' => 'fr']);

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('Mensurations')
        ->assertSee('Tour de poitrine')
        ->assertSee('Entrejambe');
});

test('guests cannot save measurements', function () {
    $this->patch('/profile/measurements')->assertRedirect('/login');
});

test('a customer keeps one sheet that is updated in place', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile/measurements', [
        'height_cm' => 165.5,
        'waist_cm' => 74,
        'fit_preference' => 'regular',
        'taken_at' => '2026-10-01',
    ])->assertRedirect()->assertSessionHas('status', 'measurements-updated');

    expect(CustomerMeasurement::count())->toBe(1);

    $this->actingAs($user)->patch('/profile/measurements', [
        'height_cm' => 165.5,
        'waist_cm' => 76,
    ]);

    $sheet = $user->fresh()->measurements;

    expect(CustomerMeasurement::count())->toBe(1)
        ->and((float) $sheet->waist_cm)->toBe(76.0)
        ->and((float) $sheet->height_cm)->toBe(165.5)
        ->and($sheet->fit_preference->value)->toBe('regular');
});

test('every measurement is optional', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile/measurements', [])->assertRedirect();

    expect($user->fresh()->measurements->height_cm)->toBeNull()
        ->and($user->fresh()->measurements->recordedCount())->toBe(0);
});

test('clearing a field takes the stored value away again', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile/measurements', ['chest_cm' => 92]);

    $this->actingAs($user)->patch('/profile/measurements', ['chest_cm' => null]);

    expect($user->fresh()->measurements->chest_cm)->toBeNull();
});

test('implausible or malformed centimetres are refused', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile/measurements', [
        'height_cm' => 900,
        'waist_cm' => 'not a number',
        'hips_cm' => 101.234,
        'fit_preference' => 'enormous',
    ])->assertSessionHasErrors(['height_cm', 'waist_cm', 'hips_cm', 'fit_preference']);

    expect(CustomerMeasurement::count())->toBe(0);
});

test('a future measurement date is refused', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile/measurements', [
        'taken_at' => now()->addWeek()->toDateString(),
    ])->assertSessionHasErrors('taken_at');
});

test('the tailor lists customers and opens a sheet', function () {
    $admin = User::factory()->admin()->create(['locale' => 'en']);
    $customer = User::factory()->create(['name' => 'Ama Serwaa', 'email' => 'ama@example.test']);

    CustomerMeasurement::factory()->create([
        'user_id' => $customer->id,
        'chest_cm' => 92,
        'dress_length_cm' => null,
    ]);

    $this->actingAs($admin)->get('/admin/customers')
        ->assertOk()
        ->assertSee('Ama Serwaa')
        ->assertSee('ama@example.test')
        ->assertDontSee($admin->email);

    $this->actingAs($admin)->get('/admin/customers/'.$customer->id)
        ->assertOk()
        ->assertSee('Chest / bust')
        ->assertSee('92.0 cm')
        ->assertSee('Not recorded');

    $this->actingAs($admin)->get('/admin/customers/'.$admin->id)->assertNotFound();
});

test('customers cannot reach the tailor screen', function () {
    $this->actingAs(User::factory()->create())->get('/admin/customers')->assertForbidden();
});

test('the tailor can search the customer list', function () {
    $admin = User::factory()->admin()->create();

    User::factory()->create(['name' => 'Ama Serwaa', 'email' => 'ama@example.test']);
    User::factory()->create(['name' => 'Kwame Boateng', 'email' => 'kwame@example.test']);

    $this->actingAs($admin)->get('/admin/customers?q=Ama')
        ->assertOk()
        ->assertSee('Ama Serwaa')
        ->assertDontSee('Kwame Boateng');
});

test('the sheet is readable in french for the tailor', function () {
    $admin = User::factory()->admin()->create(['locale' => 'fr']);
    $customer = User::factory()->create(['name' => 'Fatima Iddrisu']);

    CustomerMeasurement::factory()->create([
        'user_id' => $customer->id,
        'height_cm' => 159,
        'chest_cm' => null,
    ]);

    $this->actingAs($admin)->get('/admin/customers/'.$customer->id)
        ->assertOk()
        ->assertSee('Taille (debout)')
        ->assertSee('159,0 cm')
        ->assertSee('Non relevée');
});
