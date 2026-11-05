@php
    $current = app()->getLocale();
    $onDark = ($attributes->get('tone') ?? 'dark') === 'dark';
@endphp

<div role="group" aria-label="{{ __('ui.change_language') }}" {{ $attributes->except('tone')->merge(['class' => 'flex items-center gap-1']) }}>
    @foreach (config('saraki.supported_locales') as $locale)
        <form method="POST" action="{{ route('locale.update', $locale) }}">
            @csrf
            <button
                type="submit"
                class="label-upper h-10 w-11 rounded-xs transition-colors duration-150 ease-brand {{ $locale === $current
                    ? 'bg-gold text-charcoal'
                    : ($onDark ? 'text-beige hover:bg-charcoal-700' : 'text-charcoal hover:bg-beige') }}"
                @if ($locale === $current) aria-current="true" @endif
            >{{ strtoupper($locale) }}</button>
        </form>
    @endforeach
</div>
