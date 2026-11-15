<x-admin-layout :title="__('admin.posts.title')">
    @include('admin.content.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.posts.subtitle') }}</p>

        <x-ui.button :href="route('admin.content.posts.create')" size="compact">
            {{ __('admin.posts.create') }}
        </x-ui.button>
    </div>

    <form method="GET" action="{{ route('admin.content.posts.index') }}" class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-4">
        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,13rem)_auto] md:items-end">
            <div>
                <x-input-label for="q" :value="__('admin.posts.search_label')" />
                <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('admin.posts.search_placeholder') }}" class="field mt-1">
            </div>

            <div>
                <x-input-label for="status" :value="__('admin.common.status')" />
                <select id="status" name="status" class="field mt-1">
                    <option value="">{{ __('admin.posts.any_status') }}</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option->value }}" @selected($status?->value === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>

            <x-primary-button>{{ __('admin.posts.search_label') }}</x-primary-button>
        </div>
    </form>

    @if ($posts->isEmpty())
        <p class="mt-8 rounded-xl border border-line bg-canvas shadow-card p-6 text-body-sm text-ink-muted">
            {{ $posts->total() === 0 && $search === '' && $status === null ? __('admin.posts.empty') : __('admin.posts.no_results') }}
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-xl border border-line bg-canvas shadow-card">
            <table class="w-full min-w-[860px] text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.posts.post') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.posts.author') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.posts.published_at') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.common.status') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-right text-ink-muted">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr class="border-t border-line align-middle transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-16 w-24 shrink-0 overflow-hidden rounded-xs bg-beige-200">
                                        @if ($post->coverUrl())
                                            <img src="{{ $post->coverThumbUrl() }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <a href="{{ route('admin.content.posts.edit', $post->id) }}" class="font-semibold text-ink hover:underline">
                                            {{ $post->title_fr }}
                                        </a>
                                        <span class="mt-1 block truncate text-ink-muted">{{ $post->title_en }}</span>
                                        <span class="mt-1 block truncate font-mono text-body-sm text-ink-muted">{{ $post->slug }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-ink-soft">{{ $post->author?->name ?? '—' }}</td>

                            <td class="px-4 py-3 text-ink-soft">
                                {{ $post->publishedOn() ? localized_date($post->publishedOn()) : '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <x-ui.badge :tone="$post->status->badgeTone()">{{ $post->status->label() }}</x-ui.badge>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('admin.content.posts.edit', $post->id) }}" class="link text-body-sm">
                                        {{ __('admin.common.edit') }}
                                    </a>

                                    @if ($post->isPublished())
                                        <a href="{{ $post->url() }}" target="_blank" rel="noopener" class="link text-body-sm">
                                            {{ __('admin.posts.view') }}
                                        </a>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.content.posts.destroy', $post->id) }}"
                                        data-confirm="{{ __('admin.posts.delete_confirm', ['title' => $post->title_fr]) }}"
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
            {{ $posts->links() }}
        </div>
    @endif
</x-admin-layout>
