@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.navigation') }}" class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
        <p class="hidden text-body-sm text-ink-muted sm:block">
            {!! __('pagination.showing') !!}
            @if ($paginator->firstItem())
                <span class="tabular font-semibold text-ink">{{ $paginator->firstItem() }}</span>
                {!! __('pagination.to') !!}
                <span class="tabular font-semibold text-ink">{{ $paginator->lastItem() }}</span>
            @else
                <span class="tabular font-semibold text-ink">{{ $paginator->count() }}</span>
            @endif
            {!! __('pagination.of') !!}
            <span class="tabular font-semibold text-ink">{{ $paginator->total() }}</span>
            {!! __('pagination.results') !!}
        </p>

        <div class="inline-flex flex-wrap items-center justify-center gap-1 rounded-full border border-line bg-canvas p-1 shadow-sm">
            @if ($paginator->onFirstPage())
                <span role="link" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-full text-ink-muted/40" aria-hidden="true">
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex h-9 w-9 items-center justify-center rounded-full text-ink-soft transition-colors duration-200 ease-brand hover:bg-beige-100 hover:text-ink" aria-label="{{ __('pagination.previous') }}">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="flex h-9 w-9 cursor-default items-center justify-center text-body-sm text-ink-muted/60">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="tabular flex h-9 min-w-9 cursor-default items-center justify-center rounded-full bg-charcoal px-3 text-body-sm font-bold text-beige shadow-sm">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $url }}" class="tabular flex h-9 min-w-9 items-center justify-center rounded-full px-3 text-body-sm font-semibold text-ink-soft transition-colors duration-200 ease-brand hover:bg-beige-100 hover:text-ink" aria-label="{{ __('pagination.go_to_page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex h-9 w-9 items-center justify-center rounded-full text-ink-soft transition-colors duration-200 ease-brand hover:bg-beige-100 hover:text-ink" aria-label="{{ __('pagination.next') }}">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @else
                <span role="link" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-full text-ink-muted/40" aria-hidden="true">
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </span>
                </span>
            @endif
        </div>
    </nav>
@endif