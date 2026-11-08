<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentServiceRequest;
use App\Models\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppointmentServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.appointments.services.index', [
            'services' => AppointmentService::query()->withCount('appointments')->orderBy('name_fr')->get(),
            'title' => __('admin.services.title'),
        ]);
    }

    public function create(): View
    {
        return view('admin.appointments.services.form', [
            'service' => new AppointmentService(['duration_min' => 45, 'is_active' => true]),
            'title' => __('admin.services.create'),
        ]);
    }

    public function store(AppointmentServiceRequest $request): RedirectResponse
    {
        $service = AppointmentService::create($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', __('admin.services.created', ['name' => $service->name_fr]));
    }

    public function edit(AppointmentService $service): View
    {
        return view('admin.appointments.services.form', [
            'service' => $service,
            'title' => __('admin.services.edit'),
        ]);
    }

    public function update(AppointmentServiceRequest $request, AppointmentService $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()
            ->route('admin.services.index')
            ->with('success', __('admin.services.updated', ['name' => $service->name_fr]));
    }

    public function toggle(AppointmentService $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return back()->with('success', __('admin.services.toggled', ['name' => $service->name_fr]));
    }

    /**
     * Past appointments point at the service, and the foreign key is RESTRICT;
     * the service is deactivated instead of deleted once it has a history.
     */
    public function destroy(AppointmentService $service): RedirectResponse
    {
        if ($service->appointments()->exists()) {
            return back()->with('error', __('admin.services.cannot_delete'));
        }

        $name = $service->name_fr;
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('success', __('admin.services.deleted', ['name' => $name]));
    }
}
