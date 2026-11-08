<?php

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

function contactPayload(array $overrides = []): array
{
    return array_replace([
        'name' => 'Ama Serwaa',
        'email' => 'ama@example.test',
        'phone' => '+233 24 000 0000',
        'subject' => 'Mesures pour un boubou',
        'message' => 'Bonjour, je souhaite un boubou sur mesure pour le mois prochain.',
        'website' => '',
    ], $overrides);
}

test('a visitor sends an enquiry and both the atelier and the sender hear about it', function () {
    $this->post(route('contact.store', 'fr'), contactPayload())
        ->assertRedirect(route('contact.show', 'fr'))
        ->assertSessionHas('success');

    $message = ContactMessage::firstOrFail();

    expect($message->email)->toBe('ama@example.test')
        ->and($message->status->value)->toBe('new');

    Mail::assertSent(ContactMessageReceived::class, fn ($mail) => $mail->hasTo('ama@example.test'));
    Mail::assertSent(ContactMessageReceived::class, fn ($mail) => $mail->hasTo(config('saraki.admin_email')));
});

test('a message shorter than twenty characters is refused', function () {
    $this->post(route('contact.store', 'fr'), contactPayload(['message' => 'Trop court']))
        ->assertSessionHasErrors('message');

    expect(ContactMessage::count())->toBe(0);
    Mail::assertNothingSent();
});

test('a bot filling the honeypot gets the same answer but leaves nothing behind', function () {
    $this->post(route('contact.store', 'fr'), contactPayload(['website' => 'https://spam.test']))
        ->assertRedirect(route('contact.show', 'fr'))
        ->assertSessionHas('success');

    expect(ContactMessage::count())->toBe(0);
    Mail::assertNothingSent();
});

test('the form stops answering after five messages in a minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store', 'fr'), contactPayload())
            ->assertRedirect(route('contact.show', 'fr'));
    }

    $this->post(route('contact.store', 'fr'), contactPayload())->assertStatus(429);

    expect(ContactMessage::count())->toBe(5);
});

test('the showroom map is embedded only once an address is configured', function () {
    $this->get(route('contact.show', 'fr'))->assertOk()->assertDontSee('<iframe', false);

    app(Settings::class)->put('map_embed', 'https://www.google.com/maps/embed?pb=saraki');

    $this->get(route('contact.show', 'fr'))
        ->assertOk()
        ->assertSee('https://www.google.com/maps/embed?pb=saraki', false);
});

test('the contact page speaks both languages', function (string $locale, string $heading) {
    $this->get(route('contact.show', $locale))
        ->assertOk()
        ->assertSee($heading);
})->with([
    ['fr', 'Écrivez-nous'],
    ['en', 'Write to us'],
]);

test('enquiries stay private to the atelier', function () {
    $this->get(route('admin.messages.index'))->assertRedirect('/login');

    $this->actingAs(User::factory()->create())
        ->get(route('admin.messages.index'))
        ->assertForbidden();
});
