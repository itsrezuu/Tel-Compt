{{-- Pagination bergaya TEL-COMPT. Pakai: @include('partials.pager', ['paginator' => $usulans]) --}}
@push('styles')
<style>
    .pager { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .pager a, .pager span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 44px; padding: 0 16px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); font-weight: 600; font-size: 14px; color: var(--ink); }
    .pager a:hover { border-color: var(--primary); }
    .pager .current { background: var(--primary); border-color: var(--primary); color: #fff; }
    .pager .disabled { background: #e6eaf3; border-color: #e6eaf3; color: var(--muted); }
    .pager .gap { border: 0; background: none; min-width: auto; padding: 0 2px; }
</style>
@endpush

@php
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();
    $start   = max(1, $current - 2);
    $end     = min($last, $current + 2);
@endphp

@if ($last > 1)
    <nav class="pager" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="disabled" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}">1</a>
            @if ($start > 2)<span class="gap">…</span>@endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page === $current)
                <span class="current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $last)
            @if ($end < $last - 1)<span class="gap">…</span>@endif
            <a href="{{ $paginator->url($last) }}">{{ $last }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="disabled" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif