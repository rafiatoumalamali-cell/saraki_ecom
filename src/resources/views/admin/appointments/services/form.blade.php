@php
    $editing = $service->exists;
@endphp

<x-admin-layout :title="$title">
    @include('admin.appointments.nav')

    <a href="{{ route('admin.services.index') }}" class="link text-body-sm">{{ __('admin.common.back') }}</a>

    <form
        method="POST"
        action="{{ $editing ? route('admin.services.update', $service) : route('admin.services.store') }}"
        class="mt-6 max-w-3xl"
    >
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="rounded-sm border border-line bg-canvas p-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="name_fr" :value="__('admin.services.name_fr')" />
                    <x-text-input id="name_fr" name="name_fr" type="text" class="mt-1" :value="old('name_fr', $service->name_fr)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name_fr')" />
                </div>

                <div>
                    <x-input-label for="name_en" :value="__('admin.services.name_en')" />
                    <x-text-input id="name_en" name="name_en" type="text" class="mt-1" :value="old('name_en', $service->name_en)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name_en')" />
                </div>

                <div>
                    <x-input-label for="duration_min" :value="__('admin.services.duration')" />
                    <x-text-input id="duration_min" name="duration_min" type="number" min="15" max="480" step="5" class="mt-1" :value="old('duration_min', $service->duration_min)" required />
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.services.duration_hint') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('duration_min')" />
                </div>

                <div class="flex items-end">
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label for="is_active" class="flex items-center gap-3">
                            <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold" @checked(old('is_active', $service->is_active))>
                            <span class="text-body-md text-ink">{{ __('admin.services.active') }}</span>
                        </label>
                        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <x-primary-button>{{ __('admin.common.save') }}</x-primary-button>
            <a href="{{ route('admin.services.index') }}" class="link text-body-sm">{{ __('admin.common.cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
