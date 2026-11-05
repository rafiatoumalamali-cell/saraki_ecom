<x-app-layout :title="__('ui.profile')">
    <h1 class="font-display text-display-md">{{ __('ui.profile') }}</h1>

    <div class="mt-10 max-w-2xl space-y-6">
        <div class="rounded-sm border border-line bg-canvas p-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-sm border border-line bg-canvas p-6">
            @include('profile.partials.update-photo-form')
        </div>

        <div class="rounded-sm border border-line bg-canvas p-6">
            @include('profile.partials.update-measurements-form')
        </div>

        <div class="rounded-sm border border-line bg-canvas p-6">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-sm border border-line bg-canvas p-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
