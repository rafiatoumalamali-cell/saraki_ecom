{{-- Floating click-to-chat. Rendered only when the atelier has published a number. --}}
@php
    $whatsapp = $settings->whatsappLink();
@endphp

@if ($whatsapp !== null)
    <a
        href="{{ $whatsapp }}"
        target="_blank"
        rel="noopener noreferrer"
        class="group fixed bottom-6 end-6 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-charcoal text-gold shadow-lg ring-1 ring-gold-600/50 transition-all duration-300 ease-brand hover:-translate-y-1 hover:bg-charcoal-800 hover:text-gold-600 hover:shadow-lift active:translate-y-0 max-sm:bottom-5 max-sm:end-4"
        aria-label="{{ __('ui.whatsapp_cta') }}"
        title="{{ __('ui.whatsapp_cta') }}"
    >
        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12.04 2C6.6 2 2.2 6.4 2.2 11.84c0 1.74.46 3.44 1.32 4.94L2 22l5.36-1.4a9.84 9.84 0 0 0 4.68 1.2h.01c5.43 0 9.84-4.4 9.84-9.84S17.48 2 12.04 2Zm0 17.94h-.01a8.2 8.2 0 0 1-4.17-1.14l-.3-.18-3.1.81.83-3.02-.2-.31a8.13 8.13 0 0 1-1.25-4.36c0-4.5 3.67-8.17 8.2-8.17 2.19 0 4.24.85 5.79 2.4a8.13 8.13 0 0 1 2.4 5.79c0 4.52-3.68 8.18-8.19 8.18Zm4.5-6.12c-.25-.13-1.46-.72-1.68-.8-.23-.09-.39-.13-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.43.12-.14.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.13-.55-1.33-.75-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.42.06-.64.31-.22.25-.84.82-.84 2 0 1.19.86 2.33.98 2.49.12.17 1.69 2.58 4.1 3.62.57.25 1.02.4 1.37.51.58.18 1.1.16 1.52.1.46-.07 1.46-.6 1.66-1.17.21-.58.21-1.07.15-1.17-.06-.11-.22-.17-.47-.3Z" />
        </svg>
    </a>
@endif
