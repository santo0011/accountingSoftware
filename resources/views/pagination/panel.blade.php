{{-- Admin / portal pagination: first · prev · numbered pages · next · last --}}
@if ($paginator->hasPages())
    <nav class="pg" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pg-btn disabled" aria-hidden="true"><i class="bi bi-chevron-double-left"></i></span>
            <span class="pg-btn disabled" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
        @else
            <a class="pg-btn" href="{{ $paginator->url(1) }}" aria-label="First page"><i class="bi bi-chevron-double-left"></i></a>
            <a class="pg-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg-btn pg-gap" aria-hidden="true">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pg-btn" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pg-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
            <a class="pg-btn" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Last page"><i class="bi bi-chevron-double-right"></i></a>
        @else
            <span class="pg-btn disabled" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
            <span class="pg-btn disabled" aria-hidden="true"><i class="bi bi-chevron-double-right"></i></span>
        @endif
    </nav>
@endif
