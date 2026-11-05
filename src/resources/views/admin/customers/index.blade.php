<x-admin-layout :title="__('admin.customers.title')">
    <p class="text-body-md text-ink-soft">{{ __('admin.customers.subtitle') }}</p>

    <form method="GET" action="{{ route('admin.customers.index') }}" class="mt-8 flex max-w-xl flex-wrap items-end gap-4">
        <div class="min-w-[220px] grow">
            <x-input-label for="q" :value="__('admin.customers.search_label')" />
            <input id="q" name="q" type="search" value="{{ $search }}"
                   placeholder="{{ __('admin.customers.search_placeholder') }}" class="field mt-1">
        </div>

        <x-primary-button>{{ __('admin.customers.search_label') }}</x-primary-button>
    </form>

    @if ($customers->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('admin.customers.no_results') }}
        </p>
    @else
        <div class="mt-8 hidden overflow-hidden rounded-sm border border-line bg-canvas md:block">
            <table class="w-full text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.customers.customer') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.customers.contact') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.customers.sheet') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-ink-muted">{{ __('admin.customers.created') }}</th>
                        <th scope="col" class="label-upper px-6 py-4 text-right text-ink-muted">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr class="h-14 border-t border-line transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-6">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$customer" size="sm" />
                                    <span class="font-semibold text-ink">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="px-6">
                                <span class="block">{{ $customer->email }}</span>
                                <span class="block text-ink-muted">{{ $customer->phone ?? '—' }}</span>
                            </td>
                            <td class="px-6 tabular">
                                {{ $customer->measurements
                                    ? __('measurements.progress', ['count' => $customer->measurements->recordedCount(), 'total' => count(\App\Models\CustomerMeasurement::BODY_FIELDS)])
                                    : __('measurements.not_recorded') }}
                            </td>
                            <td class="px-6">{{ localized_date($customer->created_at) }}</td>
                            <td class="px-6 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="link">
                                    {{ __('admin.customers.open') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="mt-8 space-y-4 md:hidden">
            @foreach ($customers as $customer)
                <li class="rounded-sm border border-line bg-canvas p-4">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$customer" size="md" />
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-ink">{{ $customer->name }}</p>
                            <p class="truncate text-body-sm text-ink-muted">{{ $customer->email }}</p>
                        </div>
                    </div>

                    <dl class="mt-4 space-y-3 text-body-sm">
                        <div>
                            <dt class="label-upper text-ink-muted">{{ __('admin.customers.sheet') }}</dt>
                            <dd class="mt-1 tabular">
                                {{ $customer->measurements
                                    ? __('measurements.progress', ['count' => $customer->measurements->recordedCount(), 'total' => count(\App\Models\CustomerMeasurement::BODY_FIELDS)])
                                    : __('measurements.not_recorded') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="label-upper text-ink-muted">{{ __('admin.customers.created') }}</dt>
                            <dd class="mt-1">{{ localized_date($customer->created_at) }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('admin.customers.show', $customer) }}" class="link mt-4 inline-block">
                        {{ __('admin.customers.open') }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-8">
            {{ $customers->links() }}
        </div>
    @endif
</x-admin-layout>
