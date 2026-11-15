@php
    $current = app()->getLocale();
    $onDark = ($attributes->get('tone') ?? 'dark') === 'dark';
@endphp

<div
    role="group"
    aria-label="{{ __('ui.change_language') }}"
    {{ $attributes->except('tone')->merge(['class' => 'flex items-center gap-0.5 rounded-full border p-0.5 '.($onDark ? 'border-white/15 bg-white/5' : 'border-line bg-canvas shadow-sm')]) }}
>
    @foreach (config('saraki.supported_locales') as $locale)
        <form method="POST" action="{{ route('locale.update', $locale) }}">
            @csrf
            <button
                type="submit"
                class="label-upper h-9 w-10 rounded-full transition-all duration-200 ease-brand {{ $locale === $current
                    ? 'bg-gold text-charcoal shadow-sm'
                    : ($onDark ? 'text-beige/70 hover:bg-white/10 hover:text-beige' : 'text-ink-muted hover:bg-beige-100 hover:text-ink') }}"
                @if ($locale === $current) aria-current="true" @endif
            >{{ strtoupper($locale) }}</button>
        </form>
    @endforeach
</div>
