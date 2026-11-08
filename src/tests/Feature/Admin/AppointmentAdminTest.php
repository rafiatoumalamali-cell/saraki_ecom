<?php

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\BlockedDate;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    open_week();
});

/*
|--------------------------------------------------------------------------
| Access and reading
|--------------------------------------------------------------------------
*/

test('a customer cannot open the appointment book', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.appointments.index'))
        ->assertForbidden();
});

test('the appointment book lists the requests and filters them by status', function () {
    $admin = User::factory()->admin()->create();

    $waiting = Appointment::factory()->pending()->create();
    $confirmed = Appointment::factory()->approved()->create();

    $this->actingAs($admin)
        ->get(route('admin.appointments.index', ['status' => AppointmentStatus::Approved->value]))
        ->assertOk()
        ->assertSee($confirmed->user->name)
        ->assertDontSee($waiting->user->name);
});

test('the month planner shows the bookings of that month', function () {
    $admin = User::factory()->admin()->create();
    $day = next_weekday(3);

    $appointment = Appointment::factory()->pending()->create([
        'appointment_date' => $day->toDateString(),
        'start_time' => '10:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.appointments.calendar', ['month' => $day->format('Y-m')]))
        ->assertOk()
        ->assertSee(localized_month($day))
        ->assertSee($appointment->user->name);
});

test('the appointment screen shows the file and the moves left', function () {
    $admin = User::factory()->admin()->create();
    $appointment = Appointment::factory()->pending()->create();

    $this->actingAs($admin)
        ->get(route('admin.appointments.show', $appointment))
        ->assertOk()
        ->assertSee($appointment->service->name_fr)
        ->assertSee(__('admin.appointments.save_status'));
});

/*
|--------------------------------------------------------------------------
| Moving a booking along
|--------------------------------------------------------------------------
*/

test('the admin approves a request and the customer is emailed', function () {
    $admin = User::factory()->admin()->create();
    $appointment = Appointment::factory()->pending()->create();

    $this->actingAs($admin)
        ->patch(route('admin.appointments.status', $appointment), ['status' => 'approved', 'note' => 'Confirmé.'])
        ->assertSessionHas('success');

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Approved)
        ->and($appointment->admin_notes)->toBe('Confirmé.');

    Mail::assertSent(AppointmentStatusChanged::class, fn (AppointmentStatusChanged $mail) => $mail->hasTo($appointment->user->email));
    Mail::assertSent(AppointmentStatusChanged::class, fn (AppointmentStatusChanged $mail) => $mail->hasTo(config('saraki.admin_email')));
});

test('the admin reschedules a request onto a free slot', function () {
    $admin = User::factory()->admin()->create();
    $service = booking_service(45);
    $day = next_weekday(1);

    $appointment = Appointment::factory()->pending()->create([
        'service_id' => $service->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '09:00:00',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.appointments.status', $appointment), [
            'status' => 'rescheduled',
            'appointment_date' => $day->toDateString(),
            'start_time' => '14:00',
            'note' => 'Reporté à la demande de la cliente.',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Rescheduled)
        ->and(localized_time($appointment->start_time))->toBe('14:00')
        ->and(localized_time($appointment->end_time))->toBe('14:45')
        ->and($appointment->admin_notes)->toBe('Reporté à la demande de la cliente.');
});

test('rescheduling onto a taken slot leaves the booking where it was', function () {
    $admin = User::factory()->admin()->create();
    $service = booking_service(45);
    $day = next_weekday(1);

    Appointment::factory()->pending()->create([
        'service_id' => $service->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '09:00:00',
    ]);

    $moved = Appointment::factory()->pending()->create([
        'service_id' => $service->id,
        'appointment_date' => $day->toDateString(),
        'start_time' => '14:00:00',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.appointments.show', $moved))
        ->patch(route('admin.appointments.status', $moved), [
            'status' => 'rescheduled',
            'appointment_date' => $day->toDateString(),
            'start_time' => '09:00',
        ])
        ->assertSessionHasErrors('appointment_date');

    $moved->refresh();

    expect($moved->status)->toBe(AppointmentStatus::Pending)
        ->and(localized_time($moved->start_time))->toBe('14:00');
});

test('a cancelled booking accepts no further change', function () {
    $admin = User::factory()->admin()->create();
    $appointment = Appointment::factory()->cancelled()->create();

    $this->actingAs($admin)
        ->from(route('admin.appointments.show', $appointment))
        ->patch(route('admin.appointments.status', $appointment), ['status' => 'approved'])
        ->assertSessionHasErrors('status');

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Cancelled);
});

test('the admin cancels a request', function () {
    $admin = User::factory()->admin()->create();
    $appointment = Appointment::factory()->pending()->create();

    $this->actingAs($admin)
        ->patch(route('admin.appointments.status', $appointment), ['status' => 'cancelled'])
        ->assertSessionHas('success');

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Cancelled);
});

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

test('the service list shows what the atelier offers', function () {
    $admin = User::factory()->admin()->create();
    $service = booking_service();

    $this->actingAs($admin)
        ->get(route('admin.services.index'))
        ->assertOk()
        ->assertSee($service->name_fr);
});

test('the admin adds a service', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.services.store'), [
            'name_fr' => 'Retouche',
            'name_en' => 'Alteration',
            'duration_min' => 30,
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.services.index'));

    expect(AppointmentService::query()->where('name_fr', 'Retouche')->sole()->duration_min)->toBe(30);
});

test('the service form opens for a new and an existing service', function () {
    $admin = User::factory()->admin()->create();
    $service = booking_service();

    $this->actingAs($admin)
        ->get(route('admin.services.create'))
        ->assertOk()
        ->assertSee(__('admin.services.create'));

    $this->actingAs($admin)
        ->get(route('admin.services.edit', $service))
        ->assertOk()
        ->assertSee($service->name_fr);
});

test('a service needs a duration the atelier can hold', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.services.store'), [
            'name_fr' => 'Retouche express',
            'name_en' => 'Express alteration',
            'duration_min' => 5,
        ])
        ->assertSessionHasErrors('duration_min');

    expect(AppointmentService::query()->count())->toBe(0);
});

test('a service with a history is hidden rather than deleted', function () {
    $admin = User::factory()->admin()->create();
    $service = booking_service();

    Appointment::factory()->pending()->create(['service_id' => $service->id]);

    $this->actingAs($admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertSessionHas('error');

    expect(AppointmentService::query()->whereKey($service->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.services.toggle', $service))
        ->assertSessionHas('success');

    expect($service->refresh()->is_active)->toBeFalse();
});

test('an unused service is deleted outright', function () {
    $admin = User::factory()->admin()->create();
    $service = booking_service();

    $this->actingAs($admin)
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect(route('admin.services.index'));

    expect(AppointmentService::query()->whereKey($service->id)->exists())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Opening hours and closures
|--------------------------------------------------------------------------
*/

test('the admin sets the weekly hours', function () {
    $admin = User::factory()->admin()->create();

    $hours = collect(range(0, 6))->map(fn (int $day) => [
        'day_of_week' => $day,
        'is_closed' => $day === 0 ? 1 : 0,
        'open_time' => $day === 0 ? null : '08:00',
        'close_time' => $day === 0 ? null : '17:00',
    ])->all();

    $this->actingAs($admin)
        ->put(route('admin.schedule.update'), ['hours' => $hours])
        ->assertSessionHas('success');

    $monday = WorkingHour::query()->where('day_of_week', 1)->sole();
    $sunday = WorkingHour::query()->where('day_of_week', 0)->sole();

    expect(localized_time($monday->open_time))->toBe('08:00')
        ->and(localized_time($monday->close_time))->toBe('17:00')
        ->and($monday->is_closed)->toBeFalse()
        ->and($sunday->is_closed)->toBeTrue()
        ->and($sunday->open_time)->toBeNull();
});

test('the closing time must follow the opening time', function () {
    $admin = User::factory()->admin()->create();

    $hours = collect(range(0, 6))->map(fn (int $day) => [
        'day_of_week' => $day,
        'is_closed' => 0,
        'open_time' => '18:00',
        'close_time' => '09:00',
    ])->all();

    $this->actingAs($admin)
        ->from(route('admin.schedule.index'))
        ->put(route('admin.schedule.update'), ['hours' => $hours])
        ->assertSessionHasErrors('hours.1.close_time');

    expect(localized_time(WorkingHour::query()->where('day_of_week', 1)->sole()->open_time))->toBe('09:00');
});

test('a date can be closed and reopened', function () {
    $admin = User::factory()->admin()->create();
    $day = next_weekday(4);

    $this->actingAs($admin)
        ->post(route('admin.schedule.blocked-dates.store'), [
            'blocked_date' => $day->toDateString(),
            'reason' => 'Congé',
        ])
        ->assertSessionHas('success');

    $blocked = BlockedDate::query()->sole();

    expect($blocked->blocked_date->toDateString())->toBe($day->toDateString());

    $this->actingAs($admin)
        ->delete(route('admin.schedule.blocked-dates.destroy', $blocked))
        ->assertSessionHas('success');

    expect(BlockedDate::query()->count())->toBe(0);
});
