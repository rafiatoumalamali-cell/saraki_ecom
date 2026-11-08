<x-admin-layout :title="$title">
    @include('admin.appointments.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.services.subtitle') }}</p>

        <x-ui.button :href="route('admin.services.create')" size="compact">
            {{ __('admin.services.create') }}
        </x-ui.button>
    </div>

    @if ($services->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('admin.services.empty') }}
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-sm border border-line bg-canvas">
            <table class="w-full min-w-[760px] text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.services.name_fr') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.services.duration') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.services.appointments_count') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.common.status') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-right text-ink-muted">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr class="border-t border-line transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.services.edit', $service) }}" class="font-semibold text-ink hover:underline">
                                    {{ $service->name_fr }}
                                </a>
                                <span class="mt-1 block text-ink-muted">{{ $service->name_en }}</span>
                            </td>

                            <td class="px-4 py-3 tabular">{{ trans_choice('shop.appointments.duration', (int) $service->duration_min) }}</td>

                            <td class="px-4 py-3 tabular">{{ $service->appointments_count }}</td>

                            <td class="px-4 py-3">
                                <x-ui.badge :tone="$service->is_active ? 'success' : 'neutral'">
                                    {{ $service->is_active ? __('admin.common.active') : __('admin.common.inactive') }}
                                </x-ui.badge>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="link text-body-sm">
                                        {{ __('admin.common.edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="link text-body-sm">
                                            {{ $service->is_active ? __('admin.services.deactivate') : __('admin.services.activate') }}
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.services.destroy', $service) }}"
                                        data-confirm="{{ __('admin.services.delete_confirm', ['name' => $service->name_fr]) }}"
                                        onsubmit="return confirm(this.dataset.confirm)"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                                            {{ __('admin.common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
