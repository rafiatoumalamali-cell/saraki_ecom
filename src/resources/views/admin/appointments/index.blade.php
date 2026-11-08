<x-admin-layout :title="$title">
    @include('admin.appointments.nav')

    <p class="text-body-md text-ink-soft">{{ __('admin.appointments.subtitle') }}</p>

    <dl class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-sm border border-line bg-canvas p-5">
            <dt class="label-upper text-ink-muted">{{ __('admin.appointments.today') }}</dt>
            <dd class="mt-2 font-display text-heading-md tabular">{{ $counts['today'] }}</dd>
        </div>
        <div class="rounded-sm border border-line bg-canvas p-5">
            <dt class="label-upper text-ink-muted">{{ __('admin.appointments.open') }}</dt>
            <dd class="mt-2 font-display text-heading-md tabular">{{ $counts['open'] }}</dd>
        </div>
        <div class="rounded-sm border border-line bg-canvas p-5">
            <dt class="label-upper text-ink-muted">{{ __('admin.appointments.pending') }}</dt>
            <dd class="mt-2 font-display text-heading-md tabular">{{ $counts['pending'] }}</dd>
        </div>
    </dl>

    <form method="GET" action="{{ route('admin.appointments.index') }}" class="mt-8 flex flex-wrap items-end gap-4 rounded-sm border border-line bg-canvas p-5">
        <div class="min-w-[150px]">
            <label for="view" class="label-upper text-ink">{{ __('admin.appointments.view') }}</label>
            <select id="view" name="view" class="field mt-2">
                @foreach (['upcoming' => 'view_upcoming', 'past' => 'view_past', 'all' => 'view_all'] as $key => $label)
                    <option value="{{ $key }}" @selected($view === $key)>{{ __('admin.appointments.'.$label) }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[150px]">
            <label for="status" class="label-upper text-ink">{{ __('admin.appointments.filter_status') }}</label>
            <select id="status" name="status" class="field mt-2">
                <option value="">{{ __('admin.appointments.all_statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[150px]">
            <label for="date" class="label-upper text-ink">{{ __('admin.appointments.filter_date') }}</label>
            <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}" class="field mt-2 tabular">
        </div>

        <div class="min-w-[200px] grow">
            <label for="q" class="label-upper text-ink">{{ __('admin.appointments.search_label') }}</label>
            <input
                id="q"
                name="q"
                type="search"
                value="{{ $filters['q'] ?? '' }}"
                placeholder="{{ __('admin.appointments.search_placeholder') }}"
                class="field mt-2"
            >
        </div>

        <x-ui.button type="submit" variant="secondary" size="compact">{{ __('admin.appointments.apply') }}</x-ui.button>

        @if (filled($filters['status'] ?? null) || filled($filters['date'] ?? null) || filled($filters['q'] ?? null) || $view !== 'upcoming')
            <a href="{{ route('admin.appointments.index') }}" class="link pb-3 text-body-sm">{{ __('admin.appointments.clear') }}</a>
        @endif
    </form>

    @if ($appointments->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('admin.appointments.no_results') }}
        </p>
    @else
        <div class="mt-8 hidden overflow-hidden rounded-sm border border-line bg-canvas md:block">
            <table class="w-full text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.appointments.date') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.appointments.time') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.appointments.customer') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.appointments.service') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.appointments.status') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr class="border-t border-line transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-6 py-4">{{ localized_date($appointment->appointment_date) }}</td>
                            <td class="px-6 py-4 tabular">
                                {{ localized_time($appointment->start_time) }} — {{ localized_time($appointment->end_time) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="block text-ink">{{ $appointment->user?->name }}</span>
                                <span class="block text-ink-muted tabular">{{ $appointment->user?->phone }}</span>
                            </td>
                            <td class="px-6 py-4">{{ $appointment->service?->translate('name') }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge :tone="$appointment->status->badgeTone()">{{ $appointment->status->label() }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="link">
                                    {{ __('admin.appointments.open_appointment') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="mt-8 space-y-4 md:hidden">
            @foreach ($appointments as $appointment)
                <li class="rounded-sm border border-line bg-canvas p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-ink">{{ $appointment->user?->name }}</p>
                            <p class="mt-1 text-body-sm text-ink-muted">
                                {{ localized_date($appointment->appointment_date) }}
                                · {{ localized_time($appointment->start_time) }}
                            </p>
                        </div>

                        <x-ui.badge :tone="$appointment->status->badgeTone()">{{ $appointment->status->label() }}</x-ui.badge>
                    </div>

                    <p class="mt-2 text-body-sm text-ink-muted">{{ $appointment->service?->translate('name') }}</p>

                    <a href="{{ route('admin.appointments.show', $appointment) }}" class="link mt-3 inline-block text-body-sm">
                        {{ __('admin.appointments.open_appointment') }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $appointments->links() }}
        </div>
    @endif
</x-admin-layout>
