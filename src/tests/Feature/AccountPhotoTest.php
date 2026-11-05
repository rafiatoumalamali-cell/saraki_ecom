<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

function registrationPayload(array $overrides = []): array
{
    return [
        'name' => 'Ama Serwaa',
        'email' => 'ama@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ] + $overrides;
}

test('registration accepts an optional profile photo', function () {
    $this->post('/register', registrationPayload([
        'avatar' => UploadedFile::fake()->image('ama.jpg'),
    ]))->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'ama@example.test')->firstOrFail();

    expect($user->avatar_path)->not->toBeNull()
        ->and(Storage::disk('public')->exists($user->avatar_path))->toBeTrue();
});

test('registration still works without a photo', function () {
    $this->post('/register', registrationPayload())->assertRedirect();

    expect(User::where('email', 'ama@example.test')->firstOrFail()->avatar_path)->toBeNull();
});

test('a file that is not an image is rejected during registration', function () {
    $this->post('/register', registrationPayload([
        'avatar' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
    ]))->assertSessionHasErrors('avatar');

    expect(User::count())->toBe(0);
});

test('a photo over two megabytes is rejected', function () {
    $this->post('/register', registrationPayload([
        'avatar' => UploadedFile::fake()->create('huge.jpg', 3000, 'image/jpeg'),
    ]))->assertSessionHasErrors('avatar');
});

test('uploading a replacement photo deletes the file it supersedes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/photo', [
        'avatar' => UploadedFile::fake()->image('one.jpg'),
    ])->assertRedirect();

    $first = $user->fresh()->avatar_path;

    $this->actingAs($user)->post('/profile/photo', [
        'avatar' => UploadedFile::fake()->image('two.jpg'),
    ])->assertRedirect();

    $second = $user->fresh()->avatar_path;

    expect($second)->not->toBe($first)
        ->and(Storage::disk('public')->exists($second))->toBeTrue()
        ->and(Storage::disk('public')->exists($first))->toBeFalse();
});

test('the photo can be removed again', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/profile/photo', [
        'avatar' => UploadedFile::fake()->image('one.jpg'),
    ]);

    $path = $user->fresh()->avatar_path;

    $this->actingAs($user)->delete('/profile/photo')->assertRedirect();

    expect($user->fresh()->avatar_path)->toBeNull()
        ->and(Storage::disk('public')->exists($path))->toBeFalse();
});

test('deleting the account deletes the photo with it', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->actingAs($user)->post('/profile/photo', [
        'avatar' => UploadedFile::fake()->image('one.jpg'),
    ]);

    $path = $user->fresh()->avatar_path;

    $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('guests cannot change a profile photo', function () {
    $this->post('/profile/photo', [
        'avatar' => UploadedFile::fake()->image('one.jpg'),
    ])->assertRedirect('/login');
});

test('the account area shows the photo, or initials while none exists', function () {
    $user = User::factory()->create(['name' => 'Ama Serwaa']);

    $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('AS');

    $this->actingAs($user)->post('/profile/photo', [
        'avatar' => UploadedFile::fake()->image('ama.jpg'),
    ]);

    $user = $user->fresh();

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('/storage/'.$user->avatar_path, escape: false);
});
