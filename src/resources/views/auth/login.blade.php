<x-guest-layout>
    <h1 class="font-display text-heading-md">{{ __('ui.log_in') }}</h1>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="h-5 w-5 rounded-xs border-line-strong text-gold focus:ring-gold" name="remember">
                <span class="text-body-sm text-ink-soft">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="link text-body-sm" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        <x-primary-button class="w-full">{{ __('Log in') }}</x-primary-button>
    </form>

    <p class="mt-6 text-body-sm text-ink-soft">
        {{ __('New here?') }}
        <a class="link" href="{{ route('register') }}">{{ __('Register') }}</a>
    </p>
</x-guest-layout>
