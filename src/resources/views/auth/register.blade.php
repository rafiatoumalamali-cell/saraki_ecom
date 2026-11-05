<x-guest-layout>
    <h1 class="font-display text-heading-md">{{ __('ui.register') }}</h1>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" class="mt-1" type="tel" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="avatar" :value="__('Profile Photo')" />
            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                   class="field mt-1 file:me-3 file:border-0 file:bg-transparent file:text-body-sm file:font-semibold">
            <p class="mt-2 text-body-sm text-ink-soft">
                {{ __('Optional: you can add or change it later in your profile.') }}
                {{ __('JPG, PNG or WebP, up to 2 MB.') }}
            </p>
            <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">{{ __('Register') }}</x-primary-button>
    </form>

    <p class="mt-6 text-body-sm text-ink-soft">
        {{ __('Already registered?') }}
        <a class="link" href="{{ route('login') }}">{{ __('Log in') }}</a>
    </p>
</x-guest-layout>
