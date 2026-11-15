@php
    $locale = app()->getLocale();
    $horizon = today()->addDays((int) config('saraki.appointment_horizon_days'));
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :robots="$seo['robots']"
    />

    <a href="{{ route('appointments.index', $locale) }}" class="link text-body-sm">&larr; {{ __('shop.appointments.back') }}</a>

    <header class="mt-5 max-w-2xl">
        <h1 class="font-display text-display-md">{{ __('shop.appointments.book_title') }}</h1>
    </header>

    @if ($services->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('shop.appointments.empty_services') }}
        </p>
    @else
        {{-- Step one: the service and the day. Slots are recomputed server-side. --}}
        <form method="GET" action="{{ route('appointments.create', $locale) }}" class="mt-8 grid gap-5 rounded-sm border border-line bg-canvas p-6 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
            <div>
                <label for="service" class="label-upper text-ink">{{ __('shop.appointments.choose_service') }}</label>
                <select id="service" name="service" class="field mt-2">
                    @foreach ($services as $offering)
                        <option value="{{ $offering->id }}" @selected($service?->id === $offering->id)>
                            {{ $offering->translate('name') }} — {{ trans_choice('shop.appointments.duration', (int) $offering->duration_min) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="label-upper text-ink">{{ __('shop.appointments.choose_date') }}</label>
                <input
                    id="date"
                    name="date"
                    type="date"
                    value="{{ $date?->toDateString() }}"
                    min="{{ today()->toDateString() }}"
                    max="{{ $horizon->toDateString() }}"
                    class="field mt-2 tabular"
                >
            </div>

            <x-ui.button type="submit" variant="secondary" size="compact" class="lg:mb-0.5">
                {{ __('shop.appointments.show_slots') }}
            </x-ui.button>
        </form>

        @if ($closed)
            <x-ui.alert type="warning" class="mt-6">{{ __('shop.appointments.closed_day') }}</x-ui.alert>
        @elseif ($blocked)
            <x-ui.alert type="warning" class="mt-6">{{ __('shop.appointments.blocked_day') }}</x-ui.alert>
        @endif

        <form
            method="POST"
            action="{{ route('appointments.store', $locale) }}"
            class="mt-8"
            x-data="{ slot: @js(old('start_time', '')) }"
        >
            @csrf
            <input type="hidden" name="service_id" value="{{ $service?->id }}">
            <input type="hidden" name="appointment_date" value="{{ $date?->toDateString() }}">

            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-14">
                <div class="space-y-8">
                    <fieldset>
                        <legend class="font-display text-heading-md">{{ __('shop.appointments.choose_slot') }}</legend>

                        @error('start_time')
                            <p class="mt-3 text-body-sm text-danger">{{ $message }}</p>
                        @enderror

                        @php
                            $free = collect($slots)->where('available', true);
                        @endphp

                        @if ($free->isEmpty())
                            <p class="mt-4 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
                                {{ __('shop.appointments.no_slots') }}
                            </p>
                        @else
                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                @foreach ($slots as $item)
                                    <label @class([
                                        'flex h-14 items-center justify-center rounded-xs border border-line text-body-sm transition-colors duration-150 ease-brand',
                                        'cursor-pointer bg-canvas text-ink hover:border-charcoal has-[:checked]:border-charcoal has-[:checked]:bg-charcoal has-[:checked]:text-beige' => $item['available'],
                                        'cursor-not-allowed bg-beige-50 text-ink-muted line-through' => ! $item['available'],
                                    ])>
                                        <input
                                            type="radio"
                                            name="start_time"
                                            value="{{ $item['time'] }}"
                                            class="sr-only"
                                            x-model="slot"
                                            @checked(old('start_time') === $item['time'])
                                            @disabled(! $item['available'])
                                        >
                                        <span class="tabular">{{ $item['time'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </fieldset>

                    <div>
                        <label for="notes" class="label-upper text-ink">{{ __('shop.appointments.notes') }}</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            @class(['field mt-2', 'field-error' => $errors->has('notes')])
                        >{{ old('notes') }}</textarea>
                        <p class="mt-2 text-body-sm text-ink-muted">{{ __('shop.appointments.notes_hint') }}</p>
                        @error('notes')
                            <p class="mt-2 text-body-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <aside aria-labelledby="appointment-summary">
                    <div class="rounded-sm border border-line bg-canvas p-6 lg:sticky lg:top-24">
                        <h2 id="appointment-summary" class="font-display text-heading-sm">{{ __('shop.appointments.summary_title') }}</h2>

                        <dl class="mt-5 space-y-3 border-t border-line pt-4 text-body-sm">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('shop.appointments.summary_service') }}</dt>
                                <dd class="text-right text-ink">{{ $service?->translate('name') }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('shop.appointments.summary_date') }}</dt>
                                <dd class="text-right text-ink">{{ $date ? localized_date($date) : '—' }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('shop.appointments.summary_length') }}</dt>
                                <dd class="text-right tabular text-ink">
                                    {{ $service ? trans_choice('shop.appointments.duration', (int) $service->duration_min) : '—' }}
                                </dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('shop.appointments.choose_slot') }}</dt>
                                <dd class="text-right tabular text-ink" x-text="slot || '\u2014'">{{ old('start_time', '—') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 border-t border-line pt-4">
                            <p class="label-upper text-ink-muted">{{ __('shop.appointments.summary_customer') }}</p>
                            <p class="mt-1 text-body-sm text-ink">{{ auth()->user()->name }}</p>
                            <p class="text-body-sm text-ink-muted tabular">{{ auth()->user()->phone }}</p>
                            <p class="text-body-sm text-ink-muted">{{ auth()->user()->email }}</p>
                        </div>

                        <x-ui.button type="submit" class="mt-6 w-full" size="checkout" :disabled="$free->isEmpty()">
                            {{ __('shop.appointments.submit') }}
                        </x-ui.button>

                        <p class="mt-4 text-body-sm text-ink-muted">{{ __('shop.appointments.summary_note') }}</p>
                    </div>
                </aside>
            </div>
        </form>
    @endif
</x-app-layout>
