<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\BlockedDate;
use App\Models\WorkingHour;
use Illuminate\Support\Carbon;

/**
 * Works out which start times the atelier can actually take on a given day:
 * fixed weekly hours, minus exceptional closures, minus the bookings that still
 * hold their slot, laid out on each service's own duration.
 */
class Availability
{
    private readonly int $leadMinutes;

    private readonly int $horizonDays;

    public function __construct(?int $leadMinutes = null, ?int $horizonDays = null)
    {
        $this->leadMinutes = $leadMinutes ?? (int) config('saraki.appointment_lead_minutes');
        $this->horizonDays = $horizonDays ?? (int) config('saraki.appointment_horizon_days');
    }

    /**
     * @return array<int, array{time: string, end: string, available: bool}>
     */
    public function slots(AppointmentService $service, Carbon $date): array
    {
        $hours = $this->hoursFor($date);

        if ($hours === null || $this->isBlocked($date)) {
            return [];
        }

        $duration = max(1, (int) $service->duration_min);
        $open = $this->at($date, $hours->open_time);
        $close = $this->at($date, $hours->close_time);
        $earliest = now()->addMinutes($this->leadMinutes);

        $booked = Appointment::query()
            ->blocking()
            ->where('appointment_date', $date->toDateString())
            ->get(['start_time', 'end_time']);

        $slots = [];

        // Slots sit on the service's own grid, so a 45-minute fitting proposes
        // 10:00 and 10:45 rather than a 10:30 start that would overlap itself.
        for ($start = $open->copy(); $start->copy()->addMinutes($duration)->lte($close); $start->addMinutes($duration)) {
            if ($start->lt($earliest)) {
                continue;
            }

            $end = $start->copy()->addMinutes($duration);

            $slots[] = [
                'time' => $start->format('H:i'),
                'end' => $end->format('H:i'),
                'available' => ! $booked->contains(fn (Appointment $row) => $this->overlaps($start, $end, $row)),
            ];
        }

        return $slots;
    }

    /**
     * The first day with at least one free slot, so the booking screen never
     * opens on a Sunday with nothing to offer.
     */
    public function nextOpenDate(AppointmentService $service, Carbon $from): ?Carbon
    {
        $date = $from->copy()->startOfDay();
        $last = $date->copy()->addDays($this->horizonDays);

        while ($date->lte($last)) {
            if ($this->isOpen($date) && $this->slots($service, $date) !== []) {
                return $date->copy();
            }

            $date->addDay();
        }

        return null;
    }

    /**
     * The single rule the booking lock re-checks inside its transaction.
     */
    public function isBookable(AppointmentService $service, Carbon $date, string $time, ?int $ignoreId = null): bool
    {
        $hours = $this->hoursFor($date);

        if ($hours === null || $this->isBlocked($date)) {
            return false;
        }

        if ($date->lt(today()) || $date->gt(today()->addDays($this->horizonDays))) {
            return false;
        }

        $duration = max(1, (int) $service->duration_min);
        $start = $this->at($date, $time);
        $end = $start->copy()->addMinutes($duration);

        if ($start->lt(now()->addMinutes($this->leadMinutes)) || $end->gt($this->at($date, $hours->close_time))) {
            return false;
        }

        return ! Appointment::query()
            ->blocking()
            ->where('appointment_date', $date->toDateString())
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->get(['start_time', 'end_time'])
            ->contains(fn (Appointment $row) => $this->overlaps($start, $end, $row));
    }

    public function isOpen(Carbon $date): bool
    {
        return $this->hoursFor($date) !== null
            && ! $this->isBlocked($date)
            && ! $date->lt(today())
            && ! $date->gt(today()->addDays($this->horizonDays));
    }

    public function isBlocked(Carbon $date): bool
    {
        return BlockedDate::query()->where('blocked_date', $date->toDateString())->exists();
    }

    public function hoursFor(Carbon $date): ?WorkingHour
    {
        $hours = WorkingHour::query()->where('day_of_week', $date->dayOfWeek)->first();

        if ($hours === null || $hours->is_closed || $hours->open_time === null || $hours->close_time === null) {
            return null;
        }

        return $hours;
    }

    /**
     * Two bookings collide when each one starts before the other ends.
     */
    private function overlaps(Carbon $start, Carbon $end, Appointment $row): bool
    {
        $rowStart = $this->at($start, $row->start_time);
        $rowEnd = $this->at($start, $row->end_time);

        return $start->lt($rowEnd) && $end->gt($rowStart);
    }

    /**
     * Puts a wall-clock time on the requested day. The time columns are stored
     * as TIME, but their casts hand back a Carbon carrying today's date.
     */
    private function at(Carbon $date, Carbon|string $time): Carbon
    {
        $clock = $time instanceof Carbon ? $time->format('H:i') : $time;

        return $date->copy()->setTimeFromTimeString($clock);
    }
}
