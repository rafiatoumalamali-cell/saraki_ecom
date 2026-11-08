<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentRequested;
use App\Mail\AppointmentStatusChanged;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Books, moves and closes appointments. Every write happens inside one
 * transaction that locks the requested day, so two requests arriving together
 * cannot both claim the same slot.
 */
class BookingService
{
    public function __construct(private readonly Availability $availability) {}

    /**
     * @throws ValidationException when the slot is gone, closed or out of range
     */
    public function book(User $user, AppointmentService $service, string $date, string $time, ?string $notes = null): Appointment
    {
        $day = Carbon::parse($date)->startOfDay();

        $appointment = DB::transaction(function () use ($user, $service, $day, $time, $notes) {
            $this->lockDay($day);

            // Re-checked while holding the lock: the slot may have gone between
            // rendering the form and submitting it.
            if (! $this->availability->isBookable($service, $day, $time)) {
                throw ValidationException::withMessages(['start_time' => __('shop.appointments.slot_taken')]);
            }

            $start = $day->copy()->setTimeFromTimeString($time);

            $appointment = new Appointment([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'appointment_date' => $day->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $start->copy()->addMinutes((int) $service->duration_min)->format('H:i:s'),
                'notes' => $notes,
            ]);

            // Status is guarded, like order statuses, so it is written explicitly.
            $appointment->forceFill(['status' => AppointmentStatus::Pending])->save();

            return $appointment;
        });

        $this->notifyRequested($appointment);

        return $appointment->refresh();
    }

    /**
     * @throws ValidationException when the new slot is no longer available
     */
    public function reschedule(Appointment $appointment, string $date, string $time, ?string $note = null): Appointment
    {
        $day = Carbon::parse($date)->startOfDay();
        $service = $appointment->service;

        DB::transaction(function () use ($appointment, $service, $day, $time, $note) {
            $this->lockDay($day);

            // The appointment itself is ignored: moving a booking to the hour it
            // already occupies must not read as a clash with itself.
            if (! $this->availability->isBookable($service, $day, $time, $appointment->id)) {
                throw ValidationException::withMessages(['appointment_date' => __('admin.appointments.slot_taken')]);
            }

            $start = $day->copy()->setTimeFromTimeString($time);

            $appointment->forceFill([
                'appointment_date' => $day->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $start->copy()->addMinutes((int) $service->duration_min)->format('H:i:s'),
                'status' => AppointmentStatus::Rescheduled,
                'admin_notes' => $note ?? $appointment->admin_notes,
            ])->save();
        });

        $this->notifyStatus($appointment);

        return $appointment->refresh();
    }

    /**
     * Approve, complete or cancel an existing booking.
     *
     * @throws ValidationException when the move is not allowed from this state
     */
    public function transition(Appointment $appointment, AppointmentStatus $to, ?string $note = null): Appointment
    {
        if (! $this->canTransition($appointment->status, $to)) {
            throw ValidationException::withMessages([
                'status' => __('admin.appointments.transition_refused', [
                    'from' => $appointment->status->label(),
                    'to' => $to->label(),
                ]),
            ]);
        }

        $appointment->forceFill([
            'status' => $to,
            'admin_notes' => $note ?? $appointment->admin_notes,
        ])->save();

        $this->notifyStatus($appointment);

        return $appointment->refresh();
    }

    public function canTransition(AppointmentStatus $from, AppointmentStatus $to): bool
    {
        if ($from === $to || $from->isFinal()) {
            return false;
        }

        return match ($from) {
            AppointmentStatus::Pending => in_array($to, [AppointmentStatus::Approved, AppointmentStatus::Rescheduled, AppointmentStatus::Cancelled], true),
            AppointmentStatus::Approved => in_array($to, [AppointmentStatus::Rescheduled, AppointmentStatus::Completed, AppointmentStatus::Cancelled], true),
            AppointmentStatus::Rescheduled => in_array($to, [AppointmentStatus::Approved, AppointmentStatus::Rescheduled, AppointmentStatus::Completed, AppointmentStatus::Cancelled], true),
            default => false,
        };
    }

    /**
     * @return list<AppointmentStatus>
     */
    public function nextStatuses(Appointment $appointment): array
    {
        return array_values(array_filter(
            AppointmentStatus::cases(),
            fn (AppointmentStatus $status) => $this->canTransition($appointment->status, $status),
        ));
    }

    /**
     * A row lock over the requested day. InnoDB takes next-key locks on the
     * (appointment_date, start_time) index, so a second booking of that date
     * waits here and then sees the first one in its availability check.
     */
    private function lockDay(Carbon $day): void
    {
        Appointment::query()
            ->where('appointment_date', $day->toDateString())
            ->lockForUpdate()
            ->get(['id']);
    }

    private function notifyRequested(Appointment $appointment): void
    {
        try {
            Mail::to($appointment->user->email)
                ->locale($appointment->user->locale ?? config('app.locale'))
                ->send(new AppointmentRequested($appointment));

            Mail::to(config('saraki.admin_email'))
                ->locale(config('app.locale'))
                ->send(new AppointmentRequested($appointment, forAdmin: true));
        } catch (Throwable $exception) {
            // A failed email must never undo a booking that is already recorded.
            $this->logFailure('Appointment request email failed', $appointment, $exception);
        }
    }

    private function notifyStatus(Appointment $appointment): void
    {
        try {
            Mail::to($appointment->user->email)
                ->locale($appointment->user->locale ?? config('app.locale'))
                ->send(new AppointmentStatusChanged($appointment));

            Mail::to(config('saraki.admin_email'))
                ->locale(config('app.locale'))
                ->send(new AppointmentStatusChanged($appointment, forAdmin: true));
        } catch (Throwable $exception) {
            $this->logFailure('Appointment status email failed', $appointment, $exception);
        }
    }

    private function logFailure(string $message, Appointment $appointment, Throwable $exception): void
    {
        Log::error($message, [
            'appointment' => $appointment->id,
            'reason' => $exception->getMessage(),
        ]);
    }
}
