@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.navigation') }}" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="label-upper inline-flex h-11 cursor-not-allowed items-center gap-2 rounded-sm border border-line bg-canvas px-4 text-ink-muted/50">
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="label-upper inline-flex h-11 items-center gap-2 rounded-sm border border-line bg-canvas px-4 text-ink shadow-sm transition-all duration-200 ease-brand hover:-translate-y-0.5 hover:border-line-strong hover:shadow-md">
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="label-upper inline-flex h-11 items-center gap-2 rounded-sm border border-line bg-canvas px-4 text-ink shadow-sm transition-all duration-200 ease-brand hover:-translate-y-0.5 hover:border-line-strong hover:shadow-md">
                {!! __('pagination.next') !!}
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @else
            <span class="label-upper inline-flex h-11 cursor-not-allowed items-center gap-2 rounded-sm border border-line bg-canvas px-4 text-ink-muted/50">
                {!! __('pagination.next') !!}
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </span>
        @endif
    </nav>
@endif