@php
    $locale = app()->getLocale();

    $columns = [
        'shop' => [__('ui.catalogue'), __('ui.appointments'), __('ui.orders')],
        'help' => [__('ui.shipping'), __('ui.returns'), __('ui.faq')],
        'contact' => [__('ui.contact'), __('ui.about'), __('ui.whatsapp')],
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
            </div>

            @foreach ($columns as $key => $items)
                <details class="border-b border-charcoal-700 lg:border-0" open>
                    <summary class="label-upper cursor-default list-none py-4 text-gold lg:py-0">{{ __('ui.footer.'.$key) }}</summary>
                    <ul class="space-y-3 pb-6 lg:pb-0">
                        @foreach ($items as $item)
                            <li>
                                <span class="text-body-sm text-beige/70" aria-disabled="true" title="{{ __('ui.soon') }}">{{ $item }}</span>
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
