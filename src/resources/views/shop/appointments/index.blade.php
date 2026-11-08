@php
    $locale = app()->getLocale();
@endphp

<x-app-layout :title="$seo['title']">
    <x-ui.seo
        :title="$seo['title']"
        :description="$seo['description']"
        :canonical="$seo['canonical']"
        :alternates="$seo['alternates']"
    />

    <header class="max-w-2xl">
        <p class="label-upper text-gold-700">{{ __('shop.appointments.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-display-md">{{ __('shop.appointments.title') }}</h1>
        <p class="mt-3 text-body-md text-ink-soft">{{ __('shop.appointments.description') }}</p>
    </header>

    <div class="mt-12 grid gap-12 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-16">
        <div class="space-y-14">
            <section aria-labelledby="services-heading">
                <h2 id="services-heading" class="font-display text-heading-md">{{ __('shop.appointments.services_title') }}</h2>

                @if ($services->isEmpty())
                    <p class="mt-5 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
                        {{ __('shop.appointments.empty_services') }}
                    </p>
                @else
                    <ul class="mt-5 divide-y divide-line border-y border-line">
                        @foreach ($services as $offering)
                            <li class="flex flex-wrap items-center justify-between gap-4 py-6">
                                <div class="min-w-0">
                                    <p class="font-display text-heading-sm">{{ $offering->translate('name') }}</p>
                                    <p class="mt-1 text-body-sm text-ink-muted">
                                        {{ trans_choice('shop.appointments.duration', (int) $offering->duration_min) }}
                                    </p>
                                </div>

                                <x-ui.button
                                    :href="route('appointments.create', ['locale' => $locale, 'service' => $offering->id])"
                                    variant="secondary"
                                    size="compact"
                                >
                                    {{ __('shop.appointments.book') }}
                                </x-ui.button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @auth
                <section aria-labelledby="bookings-heading">
                    <h2 id="bookings-heading" class="font-display text-heading-md">{{ __('shop.appointments.my_bookings') }}</h2>

                    @if ($upcoming->isEmpty())
                        <p class="mt-5 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
                            {{ __('shop.appointments.no_bookings') }}
                        </p>
                    @else
                        <ul class="mt-5 space-y-4">
                            @foreach ($upcoming as $appointment)
                                <li class="rounded-sm border border-line bg-canvas p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-display text-heading-sm">{{ $appointment->service?->translate('name') }}</p>
                                            <p class="mt-1 text-body-sm text-ink-soft">
                                                {{ localized_date($appointment->appointment_date) }}
                                                · {{ localized_time($appointment->start_time) }} — {{ localized_time($appointment->end_time) }}
                                            </p>
                                        </div>

                                        <x-ui.badge :tone="$appointment->status->badgeTone()">{{ $appointment->status->label() }}</x-ui.badge>
                                    </div>

                                    @if (filled($appointment->notes))
                                        <p class="mt-3 text-body-sm text-ink-muted">{{ $appointment->notes }}</p>
                                    @endif

                                    @if (filled($appointment->admin_notes))
                                        <div class="mt-3 border-t border-line pt-3">
                                            <p class="label-upper text-ink-muted">{{ __('shop.appointments.admin_note') }}</p>
                                            <p class="mt-1 text-body-sm text-ink-soft">{{ $appointment->admin_notes }}</p>
                                        </div>
                                    @endif

                                    @if ($appointment->status->isBlocking())
                                        <form
                                            method="POST"
                                            action="{{ route('appointments.cancel', ['locale' => $locale, 'appointment' => $appointment]) }}"
                                            class="mt-4"
                                            data-confirm="{{ __('shop.appointments.cancel_confirm') }}"
                                            onsubmit="return confirm(this.dataset.confirm)"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                                                {{ __('shop.appointments.cancel') }}
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                @if ($history->isNotEmpty())
                    <section aria-labelledby="history-heading">
                        <h2 id="history-heading" class="font-display text-heading-md">{{ __('shop.appointments.history') }}</h2>

                        <ul class="mt-5 divide-y divide-line border-y border-line">
                            @foreach ($history as $appointment)
                                <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                                    <div class="min-w-0">
                                        <p class="text-body-md text-ink">{{ $appointment->service?->translate('name') }}</p>
                                        <p class="mt-1 text-body-sm text-ink-muted">
                                            {{ localized_date($appointment->appointment_date) }}
                                            · {{ localized_time($appointment->start_time) }}
                                        </p>
                                    </div>

                                    <x-ui.badge :tone="$appointment->status->badgeTone()">{{ $appointment->status->label() }}</x-ui.badge>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @else
                <x-ui.alert type="info">
                    <p>{{ __('shop.appointments.login') }}</p>

                    <x-ui.button :href="route('login')" size="compact" class="mt-3">
                        {{ __('shop.appointments.login_cta') }}
                    </x-ui.button>
                </x-ui.alert>
            @endauth
        </div>

        <aside class="space-y-6">
            <section class="rounded-sm border border-line bg-canvas p-6">
                <h2 class="font-display text-heading-sm">{{ __('shop.appointments.hours_title') }}</h2>

                <dl class="mt-4 space-y-2 text-body-sm">
                    @foreach (range(0, 6) as $day)
                        @php
                            $hour = $hours->get($day);
                            $open = $hour !== null && $hour->isOpenDay();
                        @endphp

                        <div class="flex items-baseline justify-between gap-4 border-b border-line pb-2 last:border-0 last:pb-0">
                            <dt @class(['text-ink', 'font-semibold' => $open])>{{ __('shop.appointments.days.'.$day) }}</dt>
                            <dd class="tabular text-ink-muted">
                                @if ($open)
                                    {{ localized_time($hour->open_time) }} — {{ localized_time($hour->close_time) }}
                                @else
                                    {{ __('shop.appointments.closed') }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="rounded-sm border border-line bg-canvas p-6">
                <h2 class="font-display text-heading-sm">{{ __('shop.appointments.closures_title') }}</h2>

                @if ($closedDates->isEmpty())
                    <p class="mt-3 text-body-sm text-ink-muted">{{ __('shop.appointments.no_closures') }}</p>
                @else
                    <ul class="mt-4 space-y-2 text-body-sm">
                        @foreach ($closedDates as $closedDate)
                            <li>
                                <span class="block text-ink">{{ localized_date($closedDate->blocked_date) }}</span>
                                @if (filled($closedDate->reason))
                                    <span class="block text-ink-muted">{{ $closedDate->reason }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
</x-app-layout>
