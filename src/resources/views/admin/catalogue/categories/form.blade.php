@php
    $editing = $category->exists;
@endphp

<x-admin-layout :title="$editing ? __('admin.categories.edit') : __('admin.categories.create')">
    @include('admin.catalogue.nav')

    <a href="{{ route('admin.catalogue.categories.index') }}" class="link text-body-sm">{{ __('admin.common.back') }}</a>

    <form
        method="POST"
        action="{{ $editing ? route('admin.catalogue.categories.update', $category) : route('admin.catalogue.categories.store') }}"
        class="mt-6 max-w-3xl"
    >
        @csrf
        @if ($editing)
            @method('PATCH')
        @endif

        <div class="rounded-sm border border-line bg-canvas p-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="name_fr" :value="__('admin.categories.name_fr')" />
                    <x-text-input id="name_fr" name="name_fr" type="text" class="mt-1" :value="old('name_fr', $category->name_fr)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name_fr')" />
                </div>

                <div>
                    <x-input-label for="name_en" :value="__('admin.categories.name_en')" />
                    <x-text-input id="name_en" name="name_en" type="text" class="mt-1" :value="old('name_en', $category->name_en)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name_en')" />
                </div>

                <div>
                    <x-input-label for="slug" :value="__('admin.categories.slug')" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-1" :value="old('slug', $category->slug)" />
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.categories.slug_hint') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                </div>

                <div>
                    <x-input-label for="parent_id" :value="__('admin.categories.parent')" />
                    <select id="parent_id" name="parent_id" class="field mt-1">
                        <option value="">{{ __('admin.categories.no_parent') }}</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>
                                {{ $parent->name_fr }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
                </div>

                <div>
                    <x-input-label for="sort_order" :value="__('admin.categories.sort_order')" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="100000" class="mt-1" :value="old('sort_order', $category->sort_order)" />
                    <p class="mt-1 text-body-sm text-ink-muted">{{ __('admin.categories.sort_order_hint') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                </div>

                <div class="sm:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <label for="is_active" class="flex items-center gap-3">
                        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded-xs border-line text-charcoal shadow-sm focus:ring-gold" @checked(old('is_active', $category->is_active))>
                        <span class="text-body-md text-ink">{{ __('admin.categories.active') }}</span>
                    </label>
                    <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <x-primary-button>{{ __('admin.common.save') }}</x-primary-button>
            <a href="{{ route('admin.catalogue.categories.index') }}" class="link text-body-sm">{{ __('admin.common.cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
