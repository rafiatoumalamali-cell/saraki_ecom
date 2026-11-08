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
            ['label' => __('ui.faq'), 'href' => null],
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

<footer class="bg-charcoal text-beige">
    <div class="container py-16">
        <div class="grid gap-10 lg:grid-cols-4">
            <div>
                <a href="{{ route('home', $locale) }}" class="font-display text-heading-md tracking-[0.3em] text-beige">
                    {{ config('saraki.store_name') }}
                </a>
                <p class="mt-4 max-w-prose text-body-sm text-beige/70">{{ __('ui.footer.tagline') }}</p>

                @if ($socials !== [])
                    <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-3">
                        @foreach ($socials as $network => $url)
                            <li>
                                <a
                                    href="{{ $url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="label-upper text-beige/70 transition-colors duration-150 ease-brand hover:text-gold"
                                >{{ __('ui.footer.social.'.$network) }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @foreach ($columns as $key => $items)
                <details class="border-b border-charcoal-700 lg:border-0" open>
                    <summary class="label-upper cursor-default list-none py-4 text-gold lg:py-0">{{ __('ui.footer.'.$key) }}</summary>
                    <ul class="space-y-3 pb-6 lg:pb-0">
                        @foreach ($items as $item)
                            <li>
                                @if ($item['href'])
                                    <a href="{{ $item['href'] }}" class="text-body-sm text-beige/70 transition-colors duration-150 ease-brand hover:text-beige">{{ $item['label'] }}</a>
                                @else
                                    <span class="text-body-sm text-beige/70" aria-disabled="true" title="{{ __('ui.soon') }}">{{ $item['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endforeach
        </div>

        <div class="mt-12 flex flex-col gap-4 border-t border-charcoal-700 pt-8 text-body-sm text-beige/60 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->format('Y') }} {{ config('saraki.store_name') }}. {{ __('ui.footer.rights') }}</p>
            <p class="label-upper text-gold-600">{{ __('ui.footer.payments') }}</p>
        </div>
    </div>
</footer>
