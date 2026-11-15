@php
    $defaultStatus = old('status', $nextStatuses[0]?->value ?? '');
    $duration = (int) $appointment->startsAt()->diffInMinutes($appointment->endsAt());
@endphp

<x-admin-layout :title="$title">
    @include('admin.appointments.nav')

    <a href="{{ route('admin.appointments.index') }}" class="link text-body-sm">&larr; {{ __('admin.appointments.back_to_list') }}</a>

    <header class="mt-5 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-heading-md">{{ $appointment->user?->name }}</h2>
            <p class="mt-1 text-body-sm text-ink-muted">
                {{ localized_date($appointment->appointment_date) }}
                · {{ localized_time($appointment->start_time) }} — {{ localized_time($appointment->end_time) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-ui.badge :tone="$appointment->status->badgeTone()">{{ $appointment->status->label() }}</x-ui.badge>
            <a href="{{ route('admin.appointments.calendar', ['month' => $appointment->appointment_date->format('Y-m')]) }}" class="link text-body-sm">
                {{ __('admin.appointments.back_to_calendar') }}
            </a>
        </div>
    </header>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-xl border border-line bg-canvas shadow-card">
                <h3 class="border-b border-line px-6 py-4 font-display text-heading-sm">{{ __('admin.appointments.service') }}</h3>

                <dl class="space-y-3 px-6 py-5 text-body-sm">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('admin.appointments.service') }}</dt>
                        <dd class="text-right text-ink">{{ $appointment->service?->translate('name') }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('shop.appointments.summary_length') }}</dt>
                        <dd class="text-right tabular text-ink">{{ trans_choice('shop.appointments.duration', max(1, $duration)) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('admin.appointments.date') }}</dt>
                        <dd class="text-right text-ink">{{ localized_date($appointment->appointment_date) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('admin.appointments.time') }}</dt>
                        <dd class="text-right tabular text-ink">
                            {{ localized_time($appointment->start_time) }} — {{ localized_time($appointment->end_time) }}
                        </dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-ink-muted">{{ __('admin.appointments.requested_on') }}</dt>
                        <dd class="text-right text-ink">
                            {{ localized_date($appointment->created_at) }} · {{ localized_time($appointment->created_at) }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-line bg-canvas shadow-card">
                <h3 class="border-b border-line px-6 py-4 font-display text-heading-sm">{{ __('admin.appointments.customer_notes') }}</h3>

                <p class="px-6 py-5 text-body-sm {{ filled($appointment->notes) ? 'text-ink-soft' : 'text-ink-muted' }}">
                    {{ filled($appointment->notes) ? $appointment->notes : __('admin.appointments.no_notes') }}
                </p>
            </section>

            @if (filled($appointment->admin_notes))
                <section class="rounded-xl border border-line bg-canvas shadow-card">
                    <h3 class="border-b border-line px-6 py-4 font-display text-heading-sm">{{ __('admin.appointments.admin_notes') }}</h3>

                    <p class="px-6 py-5 text-body-sm text-ink-soft">{{ $appointment->admin_notes }}</p>
                </section>
            @endif
        </div>

        <div class="space-y-6">
            <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.appointments.update_status') }}</h3>

                @if ($nextStatuses === [])
                    <p class="mt-3 text-body-sm text-ink-muted">{{ __('admin.appointments.no_transitions') }}</p>
                @else
                    <form
                        method="POST"
                        action="{{ route('admin.appointments.status', $appointment) }}"
                        class="mt-4 space-y-4"
                        x-data="{ status: @js($defaultStatus) }"
                    >
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="status" class="label-upper text-ink">{{ __('admin.appointments.next_status') }}</label>
                            <select id="status" name="status" class="field mt-2" x-model="status">
                                @foreach ($nextStatuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <fieldset x-show="status === 'rescheduled'" x-cloak class="space-y-4">
                            <legend class="label-upper text-ink">{{ __('admin.appointments.reschedule_hint') }}</legend>

                            <div>
                                <label for="appointment_date" class="label-upper text-ink">{{ __('admin.appointments.reschedule_date') }}</label>
                                <input
                                    id="appointment_date"
                                    name="appointment_date"
                                    type="date"
                                    value="{{ old('appointment_date', $appointment->appointment_date->toDateString()) }}"
                                    x-bind:required="status === 'rescheduled'"
                                    @class(['field mt-2 tabular', 'field-error' => $errors->has('appointment_date')])
                                >
                                @error('appointment_date')
                                    <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="start_time" class="label-upper text-ink">{{ __('admin.appointments.reschedule_time') }}</label>
                                <input
                                    id="start_time"
                                    name="start_time"
                                    type="time"
                                    value="{{ old('start_time', localized_time($appointment->start_time)) }}"
                                    x-bind:required="status === 'rescheduled'"
                                    @class(['field mt-2 tabular', 'field-error' => $errors->has('start_time')])
                                >
                                @error('start_time')
                                    <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </fieldset>

                        <div>
                            <label for="note" class="label-upper text-ink">{{ __('admin.appointments.status_note') }}</label>
                            <textarea id="note" name="note" rows="2" class="field mt-2">{{ old('note') }}</textarea>
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.appointments.status_note_hint') }}</p>
                            @error('note')
                                <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-ui.button type="submit" size="compact" class="w-full">
                            {{ __('admin.appointments.save_status') }}
                        </x-ui.button>
                    </form>
                @endif
            </section>

            <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.appointments.customer') }}</h3>

                <address class="mt-3 text-body-sm not-italic text-ink-soft">
                    <span class="block text-ink">{{ $appointment->user?->name }}</span>
                    <span class="block tabular">{{ $appointment->user?->phone }}</span>
                    <span class="block">{{ $appointment->user?->email }}</span>
                </address>

                @if ($appointment->user)
                    <a href="{{ route('admin.customers.show', $appointment->user) }}" class="link mt-4 inline-block text-body-sm">
                        {{ __('admin.appointments.view_customer') }}
                    </a>
                @endif
            </section>
        </div>
    </div>
</x-admin-layout>
