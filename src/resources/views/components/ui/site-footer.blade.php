@php
    $locale = app()->getLocale();
    $socials = $settings->socialLinks();

    $columns = [
        'shop' => [
            ['label' => __('ui.catalogue'), 'href' => route('shop.index', $locale)],
            ['label' => __('ui.gallery'), 'href' => route('gallery.index', $locale)],
            ['label' => __('ui.journal'), 'href' => route('journal.index', $locale)],
            ['label' => __('ui.appointments'), 'href' => route('appointments.index', $locale)],
            ['label' => __('ui.orders'), 'href' => auth()->check() ? route('dashboard') : null],
        ],
        'help' => [
            ['label' => __('ui.shipping'), 'href' => route('pages.show', ['locale' => $locale, 'page' => 'shipping'])],
            ['label' => __('ui.returns'), 'href' => route('pages.show', ['locale' => $locale, 'page' => 'returns'])],
            ['label' => __('ui.faq'), 'href' => route('faq.index', $locale)],
            ['label' => __('ui.privacy'), 'href' => route('pages.show', ['locale' => $locale, 'page' => 'privacy'])],
            ['label' => __('ui.terms'), 'href' => route('pages.show', ['locale' => $locale, 'page' => 'terms'])],
        ],
        'contact' => [
            ['label' => __('ui.contact'), 'href' => route('contact.show', $locale)],
            ['label' => __('ui.about'), 'href' => route('pages.show', ['locale' => $locale, 'page' => 'about'])],
            ['label' => __('ui.whatsapp'), 'href' => $settings->whatsappLink()],
        ],
    ];
@endphp

<footer class="relative bg-charcoal-800 text-beige">
    <span aria-hidden="true" class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/50 to-transparent"></span>

    <div class="container py-16 lg:py-20">
        <div class="grid gap-12 lg:grid-cols-4 lg:gap-10">
            <div>
                <a href="{{ route('home', $locale) }}" class="group inline-flex items-center gap-2">
                    <x-ui.logo tone="dark" />
                    <span class="h-1.5 w-1.5 rounded-full bg-gold" aria-hidden="true"></span>
                </a>
                <p class="mt-5 max-w-prose text-body-sm leading-relaxed text-beige/60">{{ __('ui.footer.tagline') }}</p>

                @if ($socials !== [])
                    <ul class="mt-7 flex flex-wrap gap-2">
                        @foreach ($socials as $network => $url)
                            <li>
                                <a
                                    href="{{ $url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="label-upper inline-flex items-center rounded-full border border-white/15 px-3.5 py-2 text-beige/70 transition-all duration-200 ease-brand hover:-translate-y-0.5 hover:border-gold/60 hover:text-gold"
                                >{{ __('ui.footer.social.'.$network) }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @foreach ($columns as $key => $items)
                <details class="border-b border-white/10 lg:border-0" open>
                    <summary class="label-upper cursor-default list-none pb-4 text-gold lg:pb-5">{{ __('ui.footer.'.$key) }}</summary>
                    <ul class="space-y-3.5 pb-6 lg:pb-0">
                        @foreach ($items as $item)
                            <li>
                                @if ($item['href'])
                                    <a href="{{ $item['href'] }}" class="inline-block text-body-sm text-beige/60 transition-all duration-200 ease-brand hover:translate-x-0.5 hover:text-beige">{{ $item['label'] }}</a>
                                @else
                                    <span class="text-body-sm text-beige/35" aria-disabled="true" title="{{ __('ui.soon') }}">{{ $item['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endforeach
        </div>

        <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 text-body-sm text-beige/50 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->format('Y') }} {{ config('saraki.store_name') }}. {{ __('ui.footer.rights') }}</p>
            <p class="label-upper inline-flex items-center gap-2 text-gold-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                {{ __('ui.footer.payments') }}
            </p>
        </div>
    </div>
</footer>
