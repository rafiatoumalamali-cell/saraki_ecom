@php
    $editing = $page->exists;
    $fixed = $editing && in_array($page->slug, \App\Models\Page::FIXED_SLUGS, true);
@endphp

<x-admin-layout :title="$editing ? __('admin.pages.edit') : __('admin.pages.create')">
    @include('admin.content.nav')

    <a href="{{ route('admin.content.pages.index') }}" class="link text-body-sm">{{ __('admin.pages.back_to_list') }}</a>

    <form
        method="POST"
        action="{{ $editing ? route('admin.content.pages.update', $page->id) : route('admin.content.pages.store') }}"
        class="mt-6"
    >
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.pages.heading') }}</h2>

                    <div class="mt-4 grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="title_fr" :value="__('admin.pages.title_fr')" />
                            <x-text-input id="title_fr" name="title_fr" type="text" class="mt-1" :value="old('title_fr', $page->title_fr)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title_fr')" />
                        </div>

                        <div>
                            <x-input-label for="title_en" :value="__('admin.pages.title_en')" />
                            <x-text-input id="title_en" name="title_en" type="text" class="mt-1" :value="old('title_en', $page->title_en)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('title_en')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="slug" :value="__('admin.pages.slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 font-mono" :value="old('slug', $page->slug)" />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.pages.slug_hint') }}</p>

                            @if ($fixed)
                                <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.pages.slug_locked_hint') }}</p>
                            @endif

                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.pages.body') }}</h2>

                    <div class="mt-4 space-y-6">
                        <div>
                            <x-input-label for="body_fr" :value="__('admin.pages.body_fr')" />
                            <textarea id="body_fr" name="body_fr" rows="16" class="field mt-1" required>{{ old('body_fr', $page->body_fr) }}</textarea>
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.pages.body_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('body_fr')" />
                        </div>

                        <div>
                            <x-input-label for="body_en" :value="__('admin.pages.body_en')" />
                            <textarea id="body_en" name="body_en" rows="16" class="field mt-1" required>{{ old('body_en', $page->body_en) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('body_en')" />
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.common.status') }}</h2>

                    <div class="mt-4">
                        <input type="hidden" name="is_published" value="0">
                        <label for="is_published" class="flex items-center gap-3">
                            <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold" @checked(old('is_published', $page->is_published))>
                            <span class="text-body-md text-ink">{{ __('admin.pages.visible') }}</span>
                        </label>
                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.pages.visible_hint') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('is_published')" />
                    </div>
                </section>

                @if ($editing)
                    <section class="rounded-sm border border-line bg-canvas p-6">
                        <ul class="space-y-3 text-body-sm">
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-ink-muted">{{ __('admin.pages.view_in_store') }}</span>
                                @if ($page->isPublished())
                                    <a href="{{ $page->url() }}" class="link" target="_blank" rel="noopener">{{ __('admin.pages.view') }}</a>
                                @else
                                    <span class="text-ink-muted">{{ __('admin.pages.offline') }}</span>
                                @endif
                            </li>
                        </ul>
                    </section>
                @endif

                <div class="flex flex-col gap-3">
                    <x-ui.button type="submit">{{ __('admin.common.save') }}</x-ui.button>
                    <a href="{{ route('admin.content.pages.index') }}" class="label-upper text-center text-ink-muted hover:text-ink">
                        {{ __('admin.common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if ($editing && ! $fixed)
        <form
            method="POST"
            action="{{ route('admin.content.pages.destroy', $page->id) }}"
            class="mt-10 border-t border-line pt-6"
            data-confirm="{{ __('admin.pages.delete_confirm', ['title' => $page->title_fr]) }}"
            onsubmit="return confirm(this.dataset.confirm)"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                {{ __('admin.pages.delete') }}
            </button>
        </form>
    @elseif ($fixed)
        <p class="mt-10 border-t border-line pt-6 text-body-sm text-ink-muted">
            {{ __('admin.pages.fixed_notice') }}
        </p>
    @endif
</x-admin-layout>
