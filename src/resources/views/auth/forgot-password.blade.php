<x-guest-layout>
    <h1 class="font-display text-heading-md">{{ __('Reset Password') }}</h1>
    <p class="mt-3 text-body-sm text-ink-soft">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">{{ __('Email Password Reset Link') }}</x-primary-button>
    </form>
</x-guest-layout>
