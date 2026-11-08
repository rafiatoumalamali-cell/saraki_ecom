<x-admin-layout :title="$message->subject ?: __('admin.messages.no_subject')">
    <a href="{{ route('admin.messages.index') }}" class="link text-body-sm">{{ __('admin.messages.back_to_list') }}</a>

    <header class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="font-display text-heading-md">{{ $message->subject ?: __('admin.messages.no_subject') }}</h2>
            <p class="mt-1 text-body-sm text-ink-soft">
                {{ $message->name }}
                <span aria-hidden="true">·</span>
                <span class="tabular">{{ localized_date($message->created_at) }}</span>
                <span aria-hidden="true">·</span>
                <span class="tabular">{{ localized_time($message->created_at) }}</span>
            </p>
        </div>

        <x-ui.badge :tone="$message->status->badgeTone()">{{ $message->status->label() }}</x-ui.badge>
    </header>

    <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <div class="space-y-6">
            <section class="rounded-sm border border-line bg-canvas p-6">
                <div class="whitespace-pre-line text-body-md text-ink-soft">{{ $message->message }}</div>
            </section>

            <section class="rounded-sm border border-line bg-canvas p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.messages.reply') }}</h3>

                <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: __('admin.messages.no_subject'))) }}" class="mt-4 inline-flex items-center gap-2 link">
                    {{ __('admin.messages.reply_by_email', ['email' => $message->email]) }}
                </a>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-sm border border-line bg-canvas p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.messages.contact') }}</h3>

                <dl class="mt-4 space-y-3 text-body-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-ink-muted">{{ __('admin.messages.name') }}</dt>
                        <dd class="text-end text-ink">{{ $message->name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-ink-muted">{{ __('admin.messages.email') }}</dt>
                        <dd class="min-w-0 break-all text-end">
                            <a href="mailto:{{ $message->email }}" class="link">{{ $message->email }}</a>
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-ink-muted">{{ __('admin.messages.phone') }}</dt>
                        <dd class="text-end text-ink">
                            @if ($message->phone)
                                <a href="tel:{{ $message->phone }}" class="link tabular">{{ $message->phone }}</a>
                            @else
                                <span class="text-ink-muted">{{ __('admin.messages.no_phone') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-sm border border-line bg-canvas p-6">
                <h3 class="font-display text-heading-sm">{{ __('admin.messages.update_status') }}</h3>

                <form method="POST" action="{{ route('admin.messages.status', $message) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="status" :value="__('admin.messages.next_status')" />
                        <select id="status" name="status" class="field mt-1" required>
                            @foreach (\App\Enums\ContactStatus::cases() as $option)
                                <option value="{{ $option->value }}" @selected($message->status === $option)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <p class="text-body-sm text-ink-muted">{{ __('admin.messages.archived_hint') }}</p>

                    <x-ui.button type="submit" size="compact" class="w-full">{{ __('admin.messages.save_status') }}</x-ui.button>
                </form>
            </section>

            <form
                method="POST"
                action="{{ route('admin.messages.destroy', $message) }}"
                data-confirm="{{ __('admin.messages.delete_confirm', ['name' => $message->name]) }}"
                onsubmit="return confirm(this.dataset.confirm)"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="text-body-sm text-danger underline decoration-danger underline-offset-4 transition-colors duration-150 ease-brand hover:brightness-75">
                    {{ __('admin.messages.delete') }}
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>
