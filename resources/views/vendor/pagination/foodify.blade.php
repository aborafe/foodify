@if ($paginator->hasPages())
    <nav class="foodify-pagination" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-summary">
            <span>Showing</span>
            <strong class="result-range">{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}</strong>
            <span>of</span>
            <strong>{{ $paginator->total() }}</strong>
            <span>results</span>
        </div>

        <div class="pagination-controls">
            @if ($paginator->onFirstPage())
                <span class="page-control is-disabled">Previous</span>
            @else
                <a class="page-control" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-dot">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-number is-active">{{ $page }}</span>
                        @else
                            <a class="page-number" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="page-control" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="page-control is-disabled">Next</span>
            @endif
        </div>
    </nav>
@endif
