@if ($paginator->hasPages())
<nav class="pager" role="navigation" aria-label="Navigasi halaman">
    @if ($paginator->onFirstPage())
        <span class="btn btn-disabled" aria-disabled="true">Sebelumnya</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline">Sebelumnya</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pager-gap">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="btn btn-primary btn-num" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="btn btn-outline btn-num" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline">Berikutnya</a>
    @else
        <span class="btn btn-disabled" aria-disabled="true">Berikutnya</span>
    @endif
</nav>
@endif
