<x-admin-layout :title="__('admin.nav.'.$section)">
    <x-ui.alert type="info" class="mt-6 max-w-prose">{{ __('admin.placeholder.text') }}</x-ui.alert>

    <x-ui.button :href="route('admin.dashboard')" variant="secondary" class="mt-6">
        {{ __('admin.placeholder.back_to_dashboard') }}
    </x-ui.button>
</x-admin-layout>
