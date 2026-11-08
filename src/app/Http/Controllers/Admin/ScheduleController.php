<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockedDateRequest;
use App\Http\Requests\Admin\WorkingHoursRequest;
use App\Models\BlockedDate;
use App\Models\WorkingHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.appointments.schedule', [
            'hours' => WorkingHour::query()->orderBy('day_of_week')->get()->keyBy('day_of_week'),
            'blockedDates' => BlockedDate::query()
                ->where('blocked_date', '>=', today()->toDateString())
                ->orderBy('blocked_date')
                ->get(),
            'title' => __('admin.schedule.title'),
        ]);
    }

    public function update(WorkingHoursRequest $request): RedirectResponse
    {
        foreach ($request->validated()['hours'] as $row) {
            $closed = (bool) ($row['is_closed'] ?? false);

            WorkingHour::updateOrCreate(
                ['day_of_week' => (int) $row['day_of_week']],
                [
                    'is_closed' => $closed,
                    // The CHECK constraint wants both ends null on a closed day.
                    'open_time' => $closed ? null : $row['open_time'],
                    'close_time' => $closed ? null : $row['close_time'],
                ],
            );
        }

        return back()->with('success', __('admin.schedule.hours_saved'));
    }

    public function storeBlockedDate(BlockedDateRequest $request): RedirectResponse
    {
        BlockedDate::create($request->validated());

        return back()->with('success', __('admin.schedule.blocked_saved'));
    }

    public function destroyBlockedDate(BlockedDate $blockedDate): RedirectResponse
    {
        $blockedDate->delete();

        return back()->with('success', __('admin.schedule.blocked_removed'));
    }
}
