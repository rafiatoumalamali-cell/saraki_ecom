@php
    $editing = $item->exists;
@endphp

<x-admin-layout :title="$editing ? __('admin.gallery.edit') : __('admin.gallery.create')">
    @include('admin.content.nav')

    <a href="{{ route('admin.content.gallery.index') }}" class="link text-body-sm">{{ __('admin.gallery.back_to_list') }}</a>

    <form
        method="POST"
        action="{{ $editing ? route('admin.content.gallery.update', $item) : route('admin.content.gallery.store') }}"
        enctype="multipart/form-data"
        class="mt-6"
    >
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div class="space-y-6">
                <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.gallery.photo_details') }}</h2>

                    <div class="mt-4 grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="title_fr" :value="__('admin.gallery.title_fr')" />
                            <x-text-input id="title_fr" name="title_fr" type="text" class="mt-1" :value="old('title_fr', $item->title_fr)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title_fr')" />
                        </div>

                        <div>
                            <x-input-label for="title_en" :value="__('admin.gallery.title_en')" />
                            <x-text-input id="title_en" name="title_en" type="text" class="mt-1" :value="old('title_en', $item->title_en)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('title_en')" />
                        </div>

                        <div>
                            <x-input-label for="category" :value="__('admin.gallery.category')" />
                            <select id="category" name="category" class="field mt-1" required>
                                @foreach ($categories as $option)
                                    <option value="{{ $option->value }}" @selected(old('category', $item->category->value) === $option->value)>
                                        {{ $option->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>

                        <div>
                            <x-input-label for="sort_order" :value="__('admin.gallery.sort_order')" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="mt-1 tabular" :value="old('sort_order', $item->sort_order ?? 0)" />
                            <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.gallery.sort_order_hint') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.gallery.image') }}</h2>

                    <div class="mt-4">
                        <x-input-label for="image" :value="__('admin.gallery.choose_image')" />
                        <input
                            id="image"
                            name="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="field mt-1 file:me-3 file:border-0 file:bg-transparent file:text-body-sm file:font-semibold"
                            @required(! $editing)
                        >
                        <p class="mt-2 text-body-sm text-ink-soft">{{ __('admin.gallery.image_hint') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                    <h2 class="font-display text-heading-sm">{{ __('admin.common.status') }}</h2>

                    <div class="mt-4">
                        <input type="hidden" name="is_published" value="0">
                        <label for="is_published" class="flex items-center gap-3">
                            <input id="is_published" name="is_published" type="checkbox" value="1" class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold" @checked(old('is_published', $item->is_published))>
                            <span class="text-body-md text-ink">{{ __('admin.gallery.visible') }}</span>
                        </label>
                        <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.gallery.visible_hint') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('is_published')" />
                    </div>
                </section>

                @if ($editing)
                    <section class="rounded-xl border border-line bg-canvas shadow-card p-6">
                        <h2 class="font-display text-heading-sm">{{ __('admin.gallery.current_image') }}</h2>

                        <img
                            src="{{ $item->url() }}"
                            alt="{{ $item->title_fr }}"
                            class="mt-4 aspect-square w-full rounded-xs object-cover"
                        >
                    </section>
                @endif

                <div class="flex flex-col gap-3">
                    <x-ui.button type="submit">{{ __('admin.common.save') }}</x-ui.button>
                    <a href="{{ route('admin.content.gallery.index') }}" class="label-upper text-center text-ink-muted hover:text-ink">
                        {{ __('admin.common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        <form
            method="POST"
            action="{{ route('admin.content.gallery.destroy', $item) }}"
            class="mt-10 border-t border-line pt-6"
            data-confirm="{{ __('admin.gallery.delete_confirm', ['name' => $item->title_fr]) }}"
            onsubmit="return confirm(this.dataset.confirm)"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                {{ __('admin.gallery.delete') }}
            </button>
        </form>
    @endif
</x-admin-layout>
