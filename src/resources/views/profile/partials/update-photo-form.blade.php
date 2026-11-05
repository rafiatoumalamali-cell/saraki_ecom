<section>
    <header>
        <h2 class="font-display text-heading-md">{{ __('Profile Photo') }}</h2>
        <p class="mt-2 text-body-sm text-ink-soft">{{ __('Shown next to your name in the store and on your customer file.') }}</p>
    </header>

    <div class="mt-6 flex items-center gap-4">
        <x-ui.avatar :user="$user" size="lg" />

        <p class="text-body-sm text-ink-soft">
            {{ $user->avatar_path ? __('JPG, PNG or WebP, up to 2 MB.') : __('No photo yet.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data"
          class="mt-6 flex flex-wrap items-end gap-4">
        @csrf

        <div class="min-w-[220px] grow">
            <x-input-label for="avatar" :value="__('New photo')" />
            <input id="avatar" name="avatar" type="file" required
                   accept="image/jpeg,image/png,image/webp"
                   class="field mt-1 file:me-3 file:border-0 file:bg-transparent file:text-body-sm file:font-semibold">
            <p class="mt-2 text-body-sm text-ink-soft">{{ __('JPG, PNG or WebP, up to 2 MB.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <x-primary-button>{{ __('Upload photo') }}</x-primary-button>
    </form>

    @if ($user->avatar_path)
        <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-4">
            @csrf
            @method('delete')

            <div class="flex items-center gap-4">
                <x-danger-button>{{ __('Remove photo') }}</x-danger-button>

                @if (session('status') === 'photo-removed')
                    <p class="text-body-sm text-ink-soft">{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>
    @endif

    @if (session('status') === 'photo-updated')
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2000)"
            class="mt-4 text-body-sm text-ink-soft"
        >{{ __('Saved.') }}</p>
    @endif
</section>
