@php
    $editing = $post->exists;
@endphp

<x-admin-layout :title="$editing ? __('admin.posts.edit') : __('admin.posts.create')">
    @include('admin.content.nav')

    <a href="{{ route('admin.content.posts.index') }}" class="link text-body-sm">{{ __('admin.posts.back_to_list') }}</a>

    <form
        method="POST"
        action="{{ $editing ? route('admin.content.posts.update', $post->id) : route('admin.content.posts.store') }}"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.posts.article') }}</h2>

                    <div class="mt-4 grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="title_fr" :value="__('admin.posts.title_fr')" />
                            <x-text-input id="title_fr" name="title_fr" type="text" class="mt-1" :value="old('title_fr', $post->title_fr)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title_fr')" />
                        </div>

                        <div>
                            <x-input-label for="title_en" :value="__('admin.posts.title_en')" />
                            <x-text-input id="title_en" name="title_en" type="text" class="mt-1" :value="old('title_en', $post->title_en)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('title_en')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="slug" :value="__('admin.posts.slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 font-mono" :value="old('slug', $post->slug)" />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.posts.slug_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>

                        <div>
                            <x-input-label for="excerpt_fr" :value="__('admin.posts.excerpt_fr')" />
                            <textarea id="excerpt_fr" name="excerpt_fr" rows="3" class="field mt-1">{{ old('excerpt_fr', $post->excerpt_fr) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('excerpt_fr')" />
                        </div>

                        <div>
                            <x-input-label for="excerpt_en" :value="__('admin.posts.excerpt_en')" />
                            <textarea id="excerpt_en" name="excerpt_en" rows="3" class="field mt-1">{{ old('excerpt_en', $post->excerpt_en) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('excerpt_en')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.posts.body') }}</h2>

                    <div class="mt-4 space-y-6">
                        <div>
                            <x-input-label for="body_fr" :value="__('admin.posts.body_fr')" />
                            <textarea id="body_fr" name="body_fr" rows="14" class="field mt-1" required>{{ old('body_fr', $post->body_fr) }}</textarea>
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.posts.body_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('body_fr')" />
                        </div>

                        <div>
                            <x-input-label for="body_en" :value="__('admin.posts.body_en')" />
                            <textarea id="body_en" name="body_en" rows="14" class="field mt-1" required>{{ old('body_en', $post->body_en) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('body_en')" />
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.common.status') }}</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <x-input-label for="status" :value="__('admin.posts.status')" />
                            <select id="status" name="status" class="field mt-1" required>
                                @foreach ($statuses as $option)
                                    <option value="{{ $option->value }}" @selected(old('status', $post->status->value) === $option->value)>
                                        {{ $option->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div>
                            <x-input-label for="published_at" :value="__('admin.posts.published_at')" />
                            <x-text-input
                                id="published_at"
                                name="published_at"
                                type="date"
                                class="mt-1 tabular"
                                :value="old('published_at', $post->published_at?->format('Y-m-d'))"
                            />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.posts.published_at_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('published_at')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-sm border border-line bg-canvas p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.posts.cover') }}</h2>

                    <div class="mt-4">
                        <x-input-label for="cover" :value="__('admin.posts.choose_cover')" />
                        <input
                            id="cover"
                            name="cover"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="field mt-1 file:me-3 file:border-0 file:bg-transparent file:text-body-sm file:font-semibold"
                        >
                        <p class="mt-2 text-body-sm text-ink-soft">{{ __('admin.posts.cover_hint') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('cover')" />
                    </div>

                    @if ($editing && $post->coverUrl())
                        <img
                            src="{{ $post->coverUrl() }}"
                            alt="{{ $post->title_fr }}"
                            class="mt-4 aspect-[4/3] w-full rounded-xs object-cover"
                        >
                    @endif
                </section>

                @if ($editing)
                    <section class="rounded-sm border border-line bg-canvas p-6">
                        <ul class="space-y-3 text-body-sm">
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-ink-muted">{{ __('admin.posts.author') }}</span>
                                <span class="text-ink">{{ $post->author?->name ?? '—' }}</span>
                            </li>
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-ink-muted">{{ __('admin.posts.view_in_journal') }}</span>
                                @if ($post->isPublished())
                                    <a href="{{ $post->url() }}" class="link" target="_blank" rel="noopener">{{ __('admin.posts.view') }}</a>
                                @else
                                    <span class="text-ink-muted">{{ __('admin.posts.draft_not_public') }}</span>
                                @endif
                            </li>
                        </ul>
                    </section>
                @endif

                <div class="flex flex-col gap-3">
                    <x-ui.button type="submit">{{ __('admin.common.save') }}</x-ui.button>
                    <a href="{{ route('admin.content.posts.index') }}" class="label-upper text-center text-ink-muted hover:text-ink">
                        {{ __('admin.common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        <form
            method="POST"
            action="{{ route('admin.content.posts.destroy', $post->id) }}"
            class="mt-10 border-t border-line pt-6"
            data-confirm="{{ __('admin.posts.delete_confirm', ['title' => $post->title_fr]) }}"
            onsubmit="return confirm(this.dataset.confirm)"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                {{ __('admin.posts.delete') }}
            </button>
        </form>
    @endif
</x-admin-layout>
