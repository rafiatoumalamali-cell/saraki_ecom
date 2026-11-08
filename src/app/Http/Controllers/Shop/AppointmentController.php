<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookAppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\BlockedDate;
use App\Models\WorkingHour;
use App\Services\Availability;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly Availability $availability,
        private readonly BookingService $bookings,
    ) {}

    public function index(Request $request, string $locale): View
    {
        $mine = $request->user() === null
            ? collect()
            : $request->user()->appointments()
                ->with('service')
                ->orderByDesc('appointment_date')
                ->orderByDesc('start_time')
                ->limit(20)
                ->get();

        return view('shop.appointments.index', [
            'services' => AppointmentService::query()->active()->orderBy('name_fr')->get(),
            'hours' => WorkingHour::query()->orderBy('day_of_week')->get()->keyBy('day_of_week'),
            'closedDates' => BlockedDate::query()
                ->where('blocked_date', '>=', today()->toDateString())
                ->orderBy('blocked_date')
                ->limit(5)
                ->get(),
            'upcoming' => $this->upcoming($mine),
            'history' => $this->history($mine),
            'seo' => [
                'title' => __('shop.appointments.title'),
                'description' => __('shop.appointments.description'),
                'canonical' => route('appointments.index', $locale),
                'alternates' => collect(config('saraki.supported_locales'))
                    ->mapWithKeys(fn (string $target) => [$target => route('appointments.index', $target)])
                    ->all(),
            ],
        ]);
    }

    public function create(Request $request, string $locale): View
    {
        $services = AppointmentService::query()->active()->orderBy('name_fr')->get();

        $service = $services->firstWhere('id', (int) $request->query('service')) ?? $services->first();

        if ($service === null) {
            return view('shop.appointments.book', [
                'services' => $services,
                'service' => null,
                'date' => null,
                'slots' => [],
                'closed' => false,
                'blocked' => false,
                'seo' => $this->seo($locale),
            ]);
        }

        $date = $this->parseDate($request->query('date'));

        // A hand-typed query string cannot walk outside the booking window.
        if ($date === null || $date->lt(today()) || $date->gt(today()->addDays((int) config('saraki.appointment_horizon_days')))) {
            $date = $this->availability->nextOpenDate($service, today()) ?? today();
        }

        return view('shop.appointments.book', [
            'services' => $services,
            'service' => $service,
            'date' => $date,
            'slots' => $this->availability->slots($service, $date),
            'closed' => $this->availability->hoursFor($date) === null,
            'blocked' => $this->availability->isBlocked($date),
            'seo' => $this->seo($locale),
        ]);
    }

    public function store(BookAppointmentRequest $request, string $locale): RedirectResponse
    {
        $service = AppointmentService::query()->active()->findOrFail($request->integer('service_id'));

        $appointment = $this->bookings->book(
            $request->user(),
            $service,
            (string) $request->input('appointment_date'),
            (string) $request->input('start_time'),
            $request->input('notes'),
        );

        return redirect()
            ->route('appointments.index', $locale)
            ->with('success', __('shop.appointments.booked', [
                'date' => localized_date($appointment->appointment_date),
                'time' => localized_time($appointment->start_time),
            ]));
    }

    /**
     * A customer may release their own slot; the atelier can still do it from
     * the admin side. Anything already completed or cancelled is left alone.
     */
    public function cancel(Request $request, string $locale, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->user_id === $request->user()->id, 404);

        if (! $this->bookings->canTransition($appointment->status, AppointmentStatus::Cancelled)) {
            return redirect()
                ->route('appointments.index', $locale)
                ->with('error', __('shop.appointments.cannot_cancel'));
        }

        $this->bookings->transition($appointment, AppointmentStatus::Cancelled);

        return redirect()
            ->route('appointments.index', $locale)
            ->with('success', __('shop.appointments.cancelled'));
    }

    /**
     * @param  Collection<int, Appointment>  $appointments
     * @return Collection<int, Appointment>
     */
    private function upcoming(Collection $appointments): Collection
    {
        return $appointments
            ->filter(fn (Appointment $appointment) => $appointment->status->isBlocking()
                && $appointment->appointment_date->gte(today()))
            ->sortBy(fn (Appointment $appointment) => $appointment->startsAt()->getTimestamp())
            ->values();
    }

    /**
     * @param  Collection<int, Appointment>  $appointments
     * @return Collection<int, Appointment>
     */
    private function history(Collection $appointments): Collection
    {
        return $appointments
            ->reject(fn (Appointment $appointment) => $appointment->status->isBlocking()
                && $appointment->appointment_date->gte(today()))
            ->take(5)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function seo(string $locale): array
    {
        return [
            'title' => __('shop.appointments.book_title'),
            'description' => __('shop.appointments.description'),
            'canonical' => route('appointments.create', $locale),
            'robots' => 'noindex,follow',
            'alternates' => [],
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
