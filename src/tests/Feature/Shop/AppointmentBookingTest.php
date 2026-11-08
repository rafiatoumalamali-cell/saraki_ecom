<?php

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentRequested;
use App\Mail\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\User;
use App\Services\Availability;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    open_week();
});

test('a guest is sent to sign in before booking', function () {
    $this->get(route('appointments.create', 'fr'))
        ->assertRedirect(route('login'));
});

test('the appointments page lists the services and the atelier hours', function () {
    $service = booking_service();
    $closed = BlockedDate::create([
        'blocked_date' => next_weekday(6)->toDateString(),
        'reason' => 'Inventaire',
    ]);

    $this->get(route('appointments.index', 'fr'))
        ->assertOk()
        ->assertSee($service->name_fr)
        ->assertSee(__('shop.appointments.hours_title'))
        ->assertSee(__('shop.appointments.login'))
        ->assertSee($closed->reason);
});

test('the booking screen offers the free slots of the chosen day', function () {
    $service = booking_service();
    $day = next_weekday(1);

    // 09:00–18:00 on a 45-minute grid: 09:00, 09:45 … 17:15.
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get(route('appointments.create', ['locale' => 'fr', 'service' => $service->id, 'date' => $day->toDateString()]))
        ->assertOk()
        ->assertSee('09:00')
        ->assertSee('17:15')
        ->assertSee($customer->name)
        ->assertDontSee(__('shop.appointments.no_slots'));
});

test('a customer requests a slot and both sides are emailed', function () {
    $service = booking_service();
    $customer = User::factory()->create();
    $day = next_weekday(1);

    $this->actingAs($customer)
        ->post(route('appointments.store', 'fr'), [
            'service_id' => $service->id,
            'appointment_date' => $day->toDateString(),
            'start_time' => '10:00',
            'notes' => 'Retouche de la manche gauche.',
        ])
        ->assertRedirect(route('appointments.index', 'fr'))
        ->assertSessionHas('success');

    $appointment = Appointment::query()->sole();

    expect($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and($appointment->user_id)->toBe($customer->id)
        ->and($appointment->notes)->toBe('Retouche de la manche gauche.')
        ->and(localized_time($appointment->start_time))->toBe('10:00')
        ->and(localized_time($appointment->end_time))->toBe('10:45');

    Mail::assertSent(AppointmentRequested::class, fn (AppointmentRequested $mail) => $mail->hasTo($customer->email));
    Mail::assertSent(AppointmentRequested::class, fn (AppointmentRequested $mail) => $mail->hasTo(config('saraki.admin_email')));
});

test('the same slot cannot be booked twice', function () {
    $service = booking_service();
    $day = next_weekday(1);

    Appointment::factory()->pending()->create([
        'service_id' => $service->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '10:00:00',
    ]);

    $this->actingAs(User::factory()->create())
        ->from(route('appointments.create', ['locale' => 'fr', 'service' => $service->id, 'date' => $day->toDateString()]))
        ->post(route('appointments.store', 'fr'), [
            'service_id' => $service->id,
            'appointment_date' => $day->toDateString(),
            'start_time' => '10:00',
        ])
        ->assertSessionHasErrors('start_time');

    expect(Appointment::query()->count())->toBe(1);
});

test('a booked slot disappears from the offered slots', function () {
    $service = booking_service();
    $day = next_weekday(1);

    Appointment::factory()->pending()->create([
        'service_id' => $service->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '09:45:00',
    ]);

    $slots = collect(app(Availability::class)->slots($service, $day))
        ->keyBy('time');

    expect($slots['09:45']['available'])->toBeFalse()
        ->and($slots['09:00']['available'])->toBeTrue()
        ->and($slots['10:30']['available'])->toBeTrue();
});

test('back-to-back bookings on the same grid do not collide', function () {
    $service = booking_service();

    $this->actingAs(User::factory()->create())
        ->post(route('appointments.store', 'fr'), [
            'service_id' => $service->id,
            'appointment_date' => next_weekday(1)->toDateString(),
            'start_time' => '10:00',
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs(User::factory()->create())
        ->post(route('appointments.store', 'fr'), [
            'service_id' => $service->id,
            'appointment_date' => next_weekday(1)->toDateString(),
            'start_time' => '10:45',
        ])
        ->assertSessionHasNoErrors();

    expect(Appointment::query()->count())->toBe(2);
});

test('a slot of another service that overlaps an existing booking is refused', function () {
    $fitting = booking_service(45);
    $consultation = booking_service(30);
    $day = next_weekday(1);

    // 10:00–10:45 is taken; a 30-minute consultation at 10:30 would cut into it.
    Appointment::factory()->pending()->create([
        'service_id' => $fitting->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '10:00:00',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('appointments.store', 'fr'), [
            'service_id' => $consultation->id,
            'appointment_date' => $day->toDateString(),
            'start_time' => '10:30',
        ])
        ->assertSessionHasErrors('start_time');

    expect(Appointment::query()->count())->toBe(1);
});

test('a closed day or a blocked date takes no booking', function () {
    $service = booking_service();
    $sunday = next_weekday(0);
    $holiday = next_weekday(2);

    BlockedDate::create(['blocked_date' => $holiday->toDateString(), 'reason' => 'Fête nationale']);

    $customer = User::factory()->create();

    foreach ([$sunday, $holiday] as $day) {
        $this->actingAs($customer)
            ->post(route('appointments.store', 'fr'), [
                'service_id' => $service->id,
                'appointment_date' => $day->toDateString(),
                'start_time' => '10:00',
            ])
            ->assertSessionHasErrors('start_time');
    }

    expect(Appointment::query()->count())->toBe(0);
});

test('a date past the booking horizon is rejected by the form', function () {
    $service = booking_service();

    $this->actingAs(User::factory()->create())
        ->post(route('appointments.store', 'fr'), [
            'service_id' => $service->id,
            'appointment_date' => today()->addDays(90)->toDateString(),
            'start_time' => '10:00',
        ])
        ->assertSessionHasErrors('appointment_date');

    expect(Appointment::query()->count())->toBe(0);
});

test('the lead time keeps a slot that comes round too soon', function () {
    $service = booking_service();
    $day = next_weekday(1);
    $availability = app(Availability::class);

    expect($availability->isBookable($service, $day, '10:00'))->toBeTrue();

    // A lead longer than a week pushes that Monday out of reach.
    config(['saraki.appointment_lead_minutes' => 60 * 24 * 8]);

    expect(app(Availability::class)->isBookable($service, $day, '10:00'))->toBeFalse();
});

test('a booking may not run past the closing time', function () {
    $service = booking_service(60);

    // 17:30 + 60 minutes would close at 18:30.
    expect(app(Availability::class)->isBookable($service, next_weekday(1), '17:30'))->toBeFalse();
});

test('a customer releases their own slot and it can be booked again', function () {
    $service = booking_service();
    $customer = User::factory()->create();
    $day = next_weekday(1);

    $appointment = Appointment::factory()->pending()->for($customer)->create([
        'service_id' => $service->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '10:00:00',
    ]);

    $this->actingAs($customer)
        ->delete(route('appointments.cancel', ['locale' => 'fr', 'appointment' => $appointment]))
        ->assertRedirect(route('appointments.index', 'fr'))
        ->assertSessionHas('success');

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Cancelled)
        ->and(app(Availability::class)->isBookable($service, $day, '10:00'))->toBeTrue();
});

test('a customer cannot cancel somebody else appointment', function () {
    $appointment = Appointment::factory()->pending()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('appointments.cancel', ['locale' => 'fr', 'appointment' => $appointment]))
        ->assertNotFound();

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Pending);
});

test('a booking that already happened can no longer be cancelled', function () {
    $appointment = Appointment::factory()->completed()->create();

    $this->actingAs($appointment->user)
        ->delete(route('appointments.cancel', ['locale' => 'fr', 'appointment' => $appointment]))
        ->assertRedirect(route('appointments.index', 'fr'))
        ->assertSessionHas('error');

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Completed);
});

test('the customer sees their own appointments only', function () {
    $customer = User::factory()->create();

    $mine = Appointment::factory()->pending()->for($customer)->create();
    $theirs = Appointment::factory()->pending()->create();

    $this->actingAs($customer)
        ->get(route('appointments.index', 'fr'))
        ->assertOk()
        ->assertSee($mine->service->name_fr)
        ->assertDontSee($theirs->user->name);
});

test('the appointment emails carry the booking in both directions', function () {
    $appointment = Appointment::factory()->pending()->create(['notes' => 'Manche gauche.']);

    $customer = (new AppointmentRequested($appointment))->render();
    $admin = (new AppointmentRequested($appointment, forAdmin: true))->render();
    $status = (new AppointmentStatusChanged($appointment))->render();

    expect($customer)
        ->toContain(e($appointment->service->name_fr))
        ->toContain('Manche gauche.')
        ->toContain(localized_date($appointment->appointment_date))
        ->and($admin)->toContain(e($appointment->user->name))
        ->and($status)->toContain($appointment->status->label());
});
