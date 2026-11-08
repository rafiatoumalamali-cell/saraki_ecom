@php
    $fixedSlugs = \App\Models\Page::FIXED_SLUGS;
@endphp

<x-admin-layout :title="__('admin.pages.title')">
    @include('admin.content.nav')

    <div class="flex flex-wrap items-start justify-between gap-4">
        <p class="max-w-prose text-body-md text-ink-soft">{{ __('admin.pages.subtitle') }}</p>

        <x-ui.button :href="route('admin.content.pages.create')" size="compact">
            {{ __('admin.pages.create') }}
        </x-ui.button>
    </div>

    @if ($pages->isEmpty())
        <p class="mt-8 rounded-sm border border-line bg-canvas p-6 text-body-sm text-ink-muted">
            {{ __('admin.pages.empty') }}
        </p>
    @else
        <div class="mt-8 overflow-x-auto rounded-sm border border-line bg-canvas">
            <table class="w-full min-w-[720px] text-left text-body-sm">
                <thead class="bg-beige">
                    <tr>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.pages.page') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.pages.slug') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-ink-muted">{{ __('admin.common.status') }}</th>
                        <th scope="col" class="label-upper px-4 py-4 text-right text-ink-muted">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        @php
                            $fixed = in_array($page->slug, $fixedSlugs, true);
                        @endphp

                        <tr class="border-t border-line align-middle transition-colors duration-150 ease-brand hover:bg-beige-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.content.pages.edit', $page->id) }}" class="font-semibold text-ink hover:underline">
                                    {{ $page->title_fr }}
                                </a>
                                <span class="mt-1 block truncate text-ink-muted">{{ $page->title_en }}</span>
                            </td>

                            <td class="px-4 py-3">
                                <span class="font-mono text-body-sm text-ink-soft">{{ $page->slug }}</span>

                                @if ($fixed)
                                    <span class="mt-1 block text-body-sm text-ink-muted">{{ __('admin.pages.slug_locked') }}</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <x-ui.badge :tone="$page->isPublished() ? 'success' : 'neutral'">
                                    {{ $page->isPublished() ? __('admin.pages.published') : __('admin.pages.hidden') }}
                                </x-ui.badge>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    <a href="{{ route('admin.content.pages.edit', $page->id) }}" class="link text-body-sm">
                                        {{ __('admin.common.edit') }}
                                    </a>

                                    @if ($page->isPublished())
                                        <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="link text-body-sm">
                                            {{ __('admin.pages.view_in_store') }}
                                        </a>
                                    @endif

                                    @unless ($fixed)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.content.pages.destroy', $page->id) }}"
                                            data-confirm="{{ __('admin.pages.delete_confirm', ['title' => $page->title_fr]) }}"
                                            onsubmit="return confirm(this.dataset.confirm)"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                                                {{ __('admin.common.delete') }}
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-4 max-w-prose text-body-sm text-ink-muted">{{ __('admin.pages.slug_locked_hint') }}</p>
    @endif
</x-admin-layout>
