<x-guest-layout>
    <h1 class="font-display text-heading-md">{{ __('ui.account_title') }}</h1>
    <p class="mt-3 text-body-sm text-ink-soft">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') === 'verification-link-sent')
        <p class="mt-4 rounded-sm bg-tint-success p-4 text-body-sm text-success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </p>
    @endif

    <div class="mt-6 flex flex-wrap items-center gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="link">{{ __('ui.log_out') }}</x-ui.button>
        </form>
    </div>
</x-guest-layout>
