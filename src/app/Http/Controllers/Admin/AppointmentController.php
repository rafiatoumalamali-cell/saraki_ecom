<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\WorkingHour;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AppointmentController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', Rule::in(['upcoming', 'past', 'all'])],
            'q' => ['nullable', 'string', 'max:60'],
        ]);

        $view = $filters['view'] ?? 'upcoming';
        $term = trim((string) ($filters['q'] ?? ''));
        $descending = $view === 'past';

        $appointments = Appointment::query()
            ->with(['user', 'service'])
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['date'] ?? null), fn ($query) => $query->where('appointment_date', $filters['date']))
            ->when($view === 'upcoming', fn ($query) => $query->where('appointment_date', '>=', today()->toDateString()))
            ->when($view === 'past', fn ($query) => $query->where('appointment_date', '<', today()->toDateString()))
            ->when($term !== '', fn ($query) => $query->whereHas('user', function ($search) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';

                $search->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            }))
            ->orderBy('appointment_date', $descending ? 'desc' : 'asc')
            ->orderBy('start_time', $descending ? 'desc' : 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'filters' => $filters,
            'view' => $view,
            'statuses' => AppointmentStatus::cases(),
            'counts' => [
                'today' => Appointment::blocking()->where('appointment_date', today()->toDateString())->count(),
                'open' => Appointment::blocking()->count(),
                'pending' => Appointment::where('status', AppointmentStatus::Pending->value)->count(),
            ],
            'title' => __('admin.appointments.title'),
        ]);
    }

    /**
     * A month grid, Monday first, with the atelier's closures marked so the
     * admin can see why a day is empty.
     */
    public function calendar(Request $request): View
    {
        $month = $this->month($request->query('month'));
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $days = [];

        // Carbon: 0 = Sunday. Monday-first grid ⇒ 0..6 blanks before the 1st.
        for ($blank = 0; $blank < ($start->dayOfWeek + 6) % 7; $blank++) {
            $days[] = null;
        }

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = $day->copy();
        }

        while (count($days) % 7 !== 0) {
            $days[] = null;
        }

        return view('admin.appointments.calendar', [
            'month' => $month,
            'days' => $days,
            'appointments' => Appointment::query()
                ->with(['user', 'service'])
                ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                ->where('status', '!=', AppointmentStatus::Cancelled->value)
                ->orderBy('start_time')
                ->get()
                ->groupBy(fn (Appointment $appointment) => $appointment->appointment_date->toDateString()),
            'hours' => WorkingHour::query()->get()->keyBy('day_of_week'),
            'closedDates' => BlockedDate::query()
                ->whereBetween('blocked_date', [$start->toDateString(), $end->toDateString()])
                ->get()
                ->keyBy(fn (BlockedDate $date) => $date->blocked_date->toDateString()),
            'title' => __('admin.appointments.calendar_title', ['month' => localized_month($start)]),
        ]);
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['user', 'service']);

        return view('admin.appointments.show', [
            'appointment' => $appointment,
            'nextStatuses' => $this->bookings->nextStatuses($appointment),
            'title' => __('admin.appointments.detail_title', ['name' => $appointment->user->name]),
        ]);
    }

    public function updateStatus(AppointmentStatusRequest $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validated();
        $status = AppointmentStatus::from($data['status']);

        if ($status === AppointmentStatus::Rescheduled) {
            $this->bookings->reschedule(
                $appointment,
                (string) $data['appointment_date'],
                (string) $data['start_time'],
                $data['note'] ?? null,
            );
        } else {
            $this->bookings->transition($appointment, $status, $data['note'] ?? null);
        }

        return back()->with('success', __('admin.appointments.updated', [
            'name' => $appointment->user->name,
            'status' => $appointment->refresh()->status->label(),
        ]));
    }

    private function month(mixed $value): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfDay();
            } catch (Throwable) {
                // Malformed months fall through to the current one.
            }
        }

        return today()->startOfMonth();
    }
}
