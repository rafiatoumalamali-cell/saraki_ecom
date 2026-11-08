@php
    $total = array_sum($counts);
    $chips = [
        ['value' => null, 'label' => __('admin.messages.all'), 'count' => $total],
    ];

    foreach ($statuses as $case) {
        $chips[] = ['value' => $case->value, 'label' => $case->label(), 'count' => $counts[$case->value] ?? 0];
    }
@endphp

<x-admin-layout :title="__('admin.messages.title')">
    <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.messages.subtitle') }}</p>

    <nav aria-label="{{ __('admin.messages.filter_label') }}" class="mt-8 flex flex-wrap gap-2">
        @foreach ($chips as $chip)
            <a
                href="{{ route('admin.messages.index', array_filter(['status' => $chip['value'], 'q' => $search ?: null])) }}"
                @class([
                    'label-upper inline-flex h-10 items-center gap-2 rounded-xs border px-4 transition-colors duration-150 ease-brand',
                    'border-charcoal bg-charcoal text-beige' => $status?->value === $chip['value'],
                    'border-line text-ink hover:border-charcoal' => $status?->value !== $chip['value'],
                ])
                @if ($status?->value === $chip['value']) aria-current="page" @endif
            >
                {{ $chip['label'] }}
                <span class="tabular">{{ $chip['count'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.messages.index') }}" class="mt-6 rounded-sm border border-line bg-canvas p-4">
        @if ($status !== null)
            <input type="hidden" name="status" value="{{ $status->value }}">
        @endif

        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
            <div>
                <x-input-label for="q" :value="__('admin.messages.search_label')" />
                <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('admin.messages.search_placeholder') }}" class="field mt-1">
            </div>

            <x-primary-button>{{ __('admin.messages.search_label') }}</x-primary-button>
        </div>
    </form>

    @if ($messages->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ $total === 0 && $search === '' && $status === null ? __('admin.messages.empty') : __('admin.messages.no_results') }}
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-sm border border-line bg-canvas">
            <table class="w-full min-w-[720px] text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.messages.from') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.messages.subject') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.messages.received') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.common.status') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-right text-ink-muted">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($messages as $message)
                        <tr class="border-t border-line align-middle transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.messages.show', $message) }}" class="font-semibold text-ink hover:underline">
                                    {{ $message->name }}
                                </a>
                                <span class="mt-1 block truncate text-ink-muted">{{ $message->email }}</span>
                            </td>

                            <td class="max-w-[28ch] px-4 py-3 text-ink-soft">
                                <span class="block truncate">{{ $message->subject ?: __('admin.messages.no_subject') }}</span>
                            </td>

                            <td class="px-4 py-3 text-ink-soft">
                                <span class="block tabular">{{ localized_date($message->created_at) }}</span>
                                <span class="block tabular text-ink-muted">{{ localized_time($message->created_at) }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <x-ui.badge :tone="$message->status->badgeTone()">{{ $message->status->label() }}</x-ui.badge>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('admin.messages.show', $message) }}" class="link text-body-sm">
                                        {{ __('admin.messages.open') }}
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.messages.destroy', $message) }}"
                                        data-confirm="{{ __('admin.messages.delete_confirm', ['name' => $message->name]) }}"
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

        <div class="mt-8">
            {{ $messages->links() }}
        </div>
    @endif
</x-admin-layout>
