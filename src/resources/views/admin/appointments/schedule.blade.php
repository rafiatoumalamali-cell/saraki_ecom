<x-admin-layout :title="$title">
    @include('admin.appointments.nav')

    <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.schedule.subtitle') }}</p>

    <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-sm border border-line bg-canvas">
            <div class="border-b border-line px-6 py-4">
                <h2 class="font-display text-heading-sm">{{ __('admin.schedule.hours_heading') }}</h2>
                <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.schedule.hours_hint') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.schedule.update') }}" class="px-6 py-5">
                @csrf
                @method('PUT')

                <table class="w-full text-left text-body-sm">
                    <thead>
                        <tr>
                            <th scope="col" class="label-upper pb-3 text-ink-muted">{{ __('admin.schedule.day') }}</th>
                            <th scope="col" class="label-upper pb-3 text-ink-muted">{{ __('admin.schedule.closed') }}</th>
                            <th scope="col" class="label-upper pb-3 text-ink-muted">{{ __('admin.schedule.open_time') }}</th>
                            <th scope="col" class="label-upper pb-3 text-ink-muted">{{ __('admin.schedule.close_time') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach (range(0, 6) as $day)
                            @php
                                $hour = $hours->get($day);
                                $closed = old("hours.{$day}.is_closed", $hour?->is_closed ?? $day === 0);
                                $opens = old("hours.{$day}.open_time", $hour?->open_time ? localized_time($hour->open_time) : '09:00');
                                $closes = old("hours.{$day}.close_time", $hour?->close_time ? localized_time($hour->close_time) : '18:00');
                            @endphp

                            <tr class="border-t border-line" x-data="{ closed: @js((bool) $closed) }">
                                <th scope="row" class="py-3 text-left font-normal text-ink">
                                    {{ __('admin.schedule.days.'.$day) }}
                                    <input type="hidden" name="hours[{{ $day }}][day_of_week]" value="{{ $day }}">
                                </th>

                                <td class="py-3">
                                    <input type="hidden" name="hours[{{ $day }}][is_closed]" value="0">
                                    <label for="closed-{{ $day }}" class="flex items-center gap-2">
                                        <input
                                            id="closed-{{ $day }}"
                                            name="hours[{{ $day }}][is_closed]"
                                            type="checkbox"
                                            value="1"
                                            class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold"
                                            x-model="closed"
                                            @checked((bool) $closed)
                                        >
                                        <span class="text-ink-muted">{{ __('admin.schedule.closed') }}</span>
                                    </label>
                                </td>

                                <td class="py-3 pe-3">
                                    <input
                                        id="open-{{ $day }}"
                                        name="hours[{{ $day }}][open_time]"
                                        type="time"
                                        value="{{ $opens }}"
                                        x-bind:disabled="closed"
                                        @class(['field tabular', 'field-error' => $errors->has("hours.{$day}.open_time")])
                                    >
                                    @error("hours.{$day}.open_time")
                                        <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </td>

                                <td class="py-3">
                                    <input
                                        id="close-{{ $day }}"
                                        name="hours[{{ $day }}][close_time]"
                                        type="time"
                                        value="{{ $closes }}"
                                        x-bind:disabled="closed"
                                        @class(['field tabular', 'field-error' => $errors->has("hours.{$day}.close_time")])
                                    >
                                    @error("hours.{$day}.close_time")
                                        <p class="mt-1 text-body-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-6">
                    <x-ui.button type="submit" size="compact">{{ __('admin.schedule.save_hours') }}</x-ui.button>
                </div>
            </form>
        </section>

        <section class="rounded-sm border border-line bg-canvas">
            <div class="border-b border-line px-6 py-4">
                <h2 class="font-display text-heading-sm">{{ __('admin.schedule.blocked_heading') }}</h2>
                <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.schedule.blocked_hint') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.schedule.blocked-dates.store') }}" class="space-y-4 px-6 py-5">
                @csrf

                <div>
                    <label for="blocked_date" class="label-upper text-ink">{{ __('admin.schedule.blocked_date') }}</label>
                    <input
                        id="blocked_date"
                        name="blocked_date"
                        type="date"
                        value="{{ old('blocked_date') }}"
                        min="{{ today()->toDateString() }}"
                        required
                        @class(['field mt-2 tabular', 'field-error' => $errors->has('blocked_date')])
                    >
                    @error('blocked_date')
                        <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reason" class="label-upper text-ink">{{ __('admin.schedule.reason') }}</label>
                    <input
                        id="reason"
                        name="reason"
                        type="text"
                        value="{{ old('reason') }}"
                        placeholder="{{ __('admin.schedule.reason_placeholder') }}"
                        @class(['field mt-2', 'field-error' => $errors->has('reason')])
                    >
                    @error('reason')
                        <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.button type="submit" variant="secondary" size="compact" class="w-full">
                    {{ __('admin.schedule.add_blocked') }}
                </x-ui.button>
            </form>

            <div class="border-t border-line px-6 py-5">
                @if ($blockedDates->isEmpty())
                    <p class="text-body-sm text-ink-muted">{{ __('admin.schedule.no_blocked') }}</p>
                @else
                    <ul class="divide-y divide-line text-body-sm">
                        @foreach ($blockedDates as $blockedDate)
                            <li class="flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <span class="block text-ink">{{ localized_date($blockedDate->blocked_date) }}</span>
                                    @if (filled($blockedDate->reason))
                                        <span class="block text-ink-muted">{{ $blockedDate->reason }}</span>
                                    @endif
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('admin.schedule.blocked-dates.destroy', $blockedDate) }}"
                                    data-confirm="{{ __('admin.schedule.blocked_removed') }}"
                                    onsubmit="return confirm(this.dataset.confirm)"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-admin-layout>
