@php
    $sheet = $customer->measurements;
    $groups = \App\Models\CustomerMeasurement::grouped();
    $fields = \App\Models\CustomerMeasurement::BODY_FIELDS;
@endphp

<x-admin-layout :title="$customer->name">
    <a href="{{ route('admin.customers.index') }}" class="link text-body-sm">{{ __('admin.customers.back_to_list') }}</a>

    <header class="mt-4 flex flex-wrap items-center gap-6">
        <x-ui.avatar :user="$customer" size="xl" />

        <div class="min-w-0">
            <h2 class="font-display text-heading-md">{{ $customer->name }}</h2>

            <p class="mt-1 text-body-sm text-ink-soft">
                {{ $customer->email }}
                @if ($customer->phone)
                    <span aria-hidden="true">·</span> <span class="tabular">{{ $customer->phone }}</span>
                @endif
            </p>

            <p class="mt-1 text-body-sm text-ink-muted">
                {{ __('admin.customers.member_since', ['date' => localized_date($customer->created_at)]) }}
            </p>
        </div>
    </header>

    @if (! $sheet)
        <p class="mt-10 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('measurements.empty') }}
        </p>
    @else
        <section class="mt-10">
            <div class="flex flex-wrap items-center gap-4">
                <h3 class="font-display text-heading-md">{{ __('measurements.title') }}</h3>

                <x-ui.badge tone="neutral">
                    {{ __('measurements.progress', ['count' => $sheet->recordedCount(), 'total' => count($fields)]) }}
                </x-ui.badge>
            </div>

            <dl class="mt-6 grid gap-6 rounded-sm border border-line bg-canvas p-6 sm:grid-cols-3">
                <div>
                    <dt class="label-upper text-ink-muted">{{ __('measurements.labels.fit_preference') }}</dt>
                    <dd class="mt-1 text-body-md">{{ $sheet->fit_preference?->label() ?? __('measurements.fit_placeholder') }}</dd>
                </div>
                <div>
                    <dt class="label-upper text-ink-muted">{{ __('measurements.labels.taken_at') }}</dt>
                    <dd class="mt-1 text-body-md">
                        {{ $sheet->taken_at ? __('measurements.taken_on', ['date' => localized_date($sheet->taken_at)]) : __('measurements.not_taken') }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="label-upper text-ink-muted">{{ __('admin.customers.notes') }}</dt>
                    <dd class="mt-1 text-body-md text-ink-soft">{{ $sheet->notes ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        @foreach ($groups as $group => $groupFields)
            <section class="mt-8">
                <h4 class="label-upper text-ink">{{ __('measurements.groups.'.$group) }}</h4>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($groupFields as $field)
                        <div class="rounded-sm border border-line bg-canvas p-4">
                            <p class="label-upper text-ink-muted">{{ \App\Models\CustomerMeasurement::label($field) }}</p>
                            <p class="mt-2 font-display text-display-md tabular">
                                {{ $sheet->display($field) ?? __('measurements.not_recorded') }}
                            </p>
                            <p class="mt-2 text-body-sm text-ink-muted">{{ __('measurements.hints.'.$field) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif
</x-admin-layout>
