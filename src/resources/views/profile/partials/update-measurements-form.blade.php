@php
    $groups = \App\Models\CustomerMeasurement::grouped();
    $sheets = \App\Models\CustomerMeasurement::BODY_FIELDS;
    $recorded = $measurements->exists ? $measurements->recordedCount() : 0;
@endphp

<section>
    <header>
        <h2 class="font-display text-heading-md">{{ __('measurements.title') }}</h2>
        <p class="mt-2 text-body-sm text-ink-soft">{{ __('measurements.intro') }}</p>

        <p class="label-upper mt-3 text-gold-700">
            {{ __('measurements.progress', ['count' => $recorded, 'total' => count($sheets)]) }}
        </p>
    </header>

    <p class="mt-4 rounded-sm bg-tint-info p-4 text-body-sm text-info">
        {{ __('measurements.optional_hint') }}
    </p>

    <form method="POST" action="{{ route('profile.measurements.update') }}" class="mt-6 space-y-10">
        @csrf
        @method('patch')

        @foreach ($groups as $group => $fields)
            <fieldset>
                <legend class="label-upper text-ink">{{ __('measurements.groups.'.$group) }}</legend>

                <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($fields as $field)
                        <div>
                            <x-input-label :for="$field" :value="\App\Models\CustomerMeasurement::label($field)" />

                            <div class="relative mt-1">
                                <input
                                    id="{{ $field }}"
                                    name="{{ $field }}"
                                    type="number"
                                    inputmode="decimal"
                                    step="0.1"
                                    min="{{ $sheets[$field]['min'] }}"
                                    max="{{ $sheets[$field]['max'] }}"
                                    value="{{ old($field, $measurements->{$field}) }}"
                                    class="field pe-12 tabular"
                                >

                                <span class="pointer-events-none absolute inset-y-0 end-3 flex items-center text-body-sm text-ink-muted">
                                    {{ __('measurements.unit') }}
                                </span>
                            </div>

                            <p class="mt-2 text-body-sm text-ink-muted">{{ __('measurements.hints.'.$field) }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get($field)" />
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <fieldset>
            <legend class="label-upper text-ink">{{ __('measurements.metadata') }}</legend>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="fit_preference" :value="__('measurements.labels.fit_preference')" />
                    <select id="fit_preference" name="fit_preference" class="field mt-1">
                        <option value="">{{ __('measurements.fit_placeholder') }}</option>
                        @foreach (\App\Enums\FitPreference::cases() as $fit)
                            <option value="{{ $fit->value }}" @selected(old('fit_preference', $measurements->fit_preference?->value) === $fit->value)>
                                {{ $fit->label() }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('fit_preference')" />
                </div>

                <div>
                    <x-input-label for="taken_at" :value="__('measurements.labels.taken_at')" />
                    <input
                        id="taken_at"
                        name="taken_at"
                        type="date"
                        max="{{ now()->toDateString() }}"
                        value="{{ old('taken_at', $measurements->taken_at?->toDateString()) }}"
                        class="field mt-1"
                    >
                    <x-input-error class="mt-2" :messages="$errors->get('taken_at')" />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="notes" :value="__('measurements.labels.notes')" />
                <textarea id="notes" name="notes" rows="3" class="field mt-1">{{ old('notes', $measurements->notes) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>
        </fieldset>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('measurements.record') }}</x-primary-button>

            @if (session('status') === 'measurements-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-body-sm text-ink-soft"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
