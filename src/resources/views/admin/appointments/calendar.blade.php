@php
    $previous = $month->copy()->subMonth()->format('Y-m');
    $next = $month->copy()->addMonth()->format('Y-m');

    // Carbon: 0 = Sunday. The grid starts on Monday, hence the rotation.
    $weekdayLabels = collect([1, 2, 3, 4, 5, 6, 0])->map(fn (int $day) => __('admin.schedule.days.'.$day));
@endphp

<x-admin-layout :title="$title">
    @include('admin.appointments.nav')

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="font-display text-heading-md">{{ localized_month($month) }}</h2>

        <div class="flex items-center gap-4">
            <a href="{{ route('admin.appointments.calendar', ['month' => $previous]) }}" class="link text-body-sm">
                &larr; {{ __('admin.appointments.previous_month') }}
            </a>
            <a href="{{ route('admin.appointments.calendar') }}" class="link text-body-sm">
                {{ __('admin.appointments.today') }}
            </a>
            <a href="{{ route('admin.appointments.calendar', ['month' => $next]) }}" class="link text-body-sm">
                {{ __('admin.appointments.next_month') }} &rarr;
            </a>
        </div>
    </div>

    <ul class="mt-4 flex flex-wrap items-center gap-4 text-body-sm text-ink-muted">
        <li class="flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-success" aria-hidden="true"></span>
            {{ __('admin.appointments.legend_open') }}
        </li>
        <li class="flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-beige" aria-hidden="true"></span>
            {{ __('admin.appointments.legend_closed') }}
        </li>
        <li class="flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-danger" aria-hidden="true"></span>
            {{ __('admin.appointments.legend_blocked') }}
        </li>
    </ul>

    <div class="mt-6 overflow-x-auto rounded-sm border border-line bg-canvas">
        <table class="w-full min-w-[860px] table-fixed border-collapse">
            <caption class="sr-only">{{ $title }}</caption>

            <thead>
                <tr class="bg-beige">
                    @foreach ($weekdayLabels as $label)
                        <th scope="col" class="label-upper px-3 py-3 text-left text-ink-muted">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach (array_chunk($days, 7) as $week)
                    <tr class="border-t border-line">
                        @foreach ($week as $day)
                            @if ($day === null)
                                <td class="h-32 bg-beige-50" aria-hidden="true"></td>
                                @continue
                            @endif

                            @php
                                $key = $day->toDateString();
                                $dayAppointments = $appointments->get($key, collect());
                                $closed = $closedDates->has($key);
                                $hour = $hours->get($day->dayOfWeek);
                                $open = $hour !== null && $hour->isOpenDay() && ! $closed;
                                $isToday = $day->isToday();
                            @endphp

                            <td class="h-32 align-top">
                                <div class="flex h-full flex-col p-2">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <span @class([
                                            'tabular text-body-sm',
                                            'font-semibold text-charcoal' => $isToday,
                                            'text-ink' => ! $isToday,
                                        ])>{{ $day->day }}</span>

                                        <span
                                            @class([
                                                'inline-block h-2 w-2 rounded-full',
                                                'bg-danger' => $closed,
                                                'bg-success' => $open,
                                                'bg-beige' => ! $open && ! $closed,
                                            ])
                                            aria-hidden="true"
                                        ></span>
                                    </div>

                                    @if ($closed)
                                        <p class="mt-1 text-body-sm text-danger">{{ __('admin.appointments.legend_blocked') }}</p>
                                    @elseif (! $open)
                                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.appointments.legend_closed') }}</p>
                                    @endif

                                    <ul class="mt-2 space-y-1">
                                        @foreach ($dayAppointments as $appointment)
                                            <li>
                                                <a
                                                    href="{{ route('admin.appointments.show', $appointment) }}"
                                                    class="flex items-baseline gap-1.5 rounded-xs px-1.5 py-1 text-body-sm transition-colors duration-150 ease-brand hover:bg-beige"
                                                    title="{{ $appointment->user?->name }} · {{ $appointment->service?->translate('name') }}"
                                                >
                                                    <span class="tabular text-ink">{{ localized_time($appointment->start_time) }}</span>
                                                    <span class="truncate text-ink-muted">{{ $appointment->user?->name }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
