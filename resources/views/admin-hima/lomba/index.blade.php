@extends('layouts.admin-hima')

@section('title', 'Kelola Lomba')
@section('page_title', 'Kelola Lomba')

@push('styles')
<style>
    .page-head h2 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 6px;
        color: var(--ink);
        letter-spacing: -.2px;
    }
    .page-head p { margin: 0; color: var(--muted); font-size: 14px; }

    .toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 28px 0 24px;
        flex-wrap: wrap;
    }
    .toolbar .count { font-weight: 600; font-size: 14px; }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        padding: 12px 18px;
        border-radius: 8px;
        border: 0;
        transition: background .15s ease;
    }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-primary svg { width: 16px; height: 16px; stroke: currentColor; stroke-width: 2.4; fill: none; stroke-linecap: round; }

    .search-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; }
    .search-box {
        position: relative;
        margin-bottom: 24px;
    }
    .search-box svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        stroke: var(--muted);
        stroke-width: 2;
        fill: none;
        stroke-linecap: round;
        pointer-events: none;
    }
    .search-box input {
        width: 100%;
        padding: 13px 16px 13px 40px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--surface);
        font: inherit;
        color: var(--ink);
    }
    .search-box input::placeholder { color: #7d88ab; }
    .search-box input:focus { outline: 2px solid var(--primary); outline-offset: 0; border-color: var(--primary); }

    .table-wrap {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 10px;
        overflow-x: auto;
    }
    table.lomba-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .lomba-table thead th {
        text-align: left;
        font-size: 13px;
        font-weight: 500;
        color: var(--muted);
        padding: 16px 20px;
        background: #fbfcfe;
    }
    .lomba-table tbody td {
        padding: 20px;
        border-top: 1px solid var(--line);
        font-size: 14px;
        vertical-align: middle;
    }
    .lomba-table tbody tr:first-child td { border-top: 0; }
    .lomba-table .judul { font-weight: 600; }
    .lomba-table .penyelenggara { display: block; font-size: 12px; color: var(--muted); font-weight: 400; margin-top: 2px; }

    .badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge.published { background: #e8f7ee; color: #14803f; }
    .badge.process   { background: #e6f0ff; color: #1d4ed8; }
    .badge.draft     { background: #eef0f6; color: #5b6488; }
    .badge.closed    { background: #fdecec; color: #b42318; }

    .aksi { display: flex; align-items: center; gap: 6px; color: var(--muted); }
    .aksi a, .aksi button.link {
        color: var(--primary);
        font-weight: 500;
        background: none;
        border: 0;
        padding: 0;
        font-size: 14px;
    }
    .aksi a:hover, .aksi button.link:hover { text-decoration: underline; }
    .aksi form { display: inline; margin: 0; }

    .empty { text-align: center; color: var(--muted); padding: 40px 20px !important; }

    .table-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 24px;
        flex-wrap: wrap;
    }
    .table-foot .info { font-size: 13px; color: var(--muted); }

    .pager { display: flex; align-items: center; gap: 12px; }
    .pager a, .pager span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 44px;
        padding: 0 16px;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: var(--surface);
        font-weight: 600;
        font-size: 14px;
        color: var(--ink);
    }
    .pager a:hover { border-color: var(--primary); }
    .pager .current { background: var(--primary); border-color: var(--primary); color: #fff; }
    .pager .disabled { background: #e6eaf3; border-color: #e6eaf3; color: var(--muted); }
    .pager .gap { border: 0; background: none; min-width: auto; padding: 0 2px; }

    .note { font-size: 13px; color: var(--muted); margin: 28px 0 0; }

    @media (max-width: 600px) {
        .page-head h2 { font-size: 24px; }
        .pager { flex-wrap: wrap; }
    }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Carbon;

    // Data yang diharapkan dari controller: $lombas (LengthAwarePaginator)
    $total   = $lombas->total();
    $shown   = $lombas->count();
    $current = $lombas->currentPage();
    $last    = $lombas->lastPage();

    $statusClass = [
        'dipublikasikan' => 'published',
        'diproses'       => 'process',
        'draf'           => 'draft',
        'ditutup'        => 'closed',
    ];
    $statusLabel = [
        'dipublikasikan' => 'Dipublikasikan',
        'diproses'       => 'Diproses',
        'draf'           => 'Draf',
        'ditutup'        => 'Ditutup',
    ];
@endphp

<div class="page-head">
    <h2>Kelola lomba</h2>
    <p>Tambah, perbarui atau hapus informasi lomba terkurasi.</p>
</div>

<div class="toolbar">
    <div class="count">{{ $total }} lomba · {{ $shown }} ditampilkan</div>
    <a href="{{ url('/admin-hima/lomba/create') }}" class="btn-primary">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        Tambah lomba
    </a>
</div>

<form method="GET" action="{{ url('/admin-hima/lomba') }}" role="search">
    <label for="q" class="search-label">Cari lomba</label>
    <div class="search-box">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Judul atau penyelenggara" autocomplete="off">
    </div>
</form>

<div class="table-wrap">
    <table class="lomba-table">
        <thead>
            <tr>
                <th scope="col">Lomba</th>
                <th scope="col">Kategori / tingkat</th>
                <th scope="col">Tenggat</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lombas as $lomba)
                @php $status = strtolower($lomba->status ?? 'draf'); @endphp
                <tr>
                    <td class="judul">
                        {{ $lomba->judul }}
                        @if (!empty($lomba->penyelenggara))
                            <span class="penyelenggara">{{ $lomba->penyelenggara }}</span>
                        @endif
                    </td>
                    <td>{{ $lomba->kategori }} · {{ $lomba->tingkat }}</td>
                    <td>
                        @if ($lomba->tenggat)
                            {{ Carbon::parse($lomba->tenggat)->locale('id')->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $statusClass[$status] ?? 'draft' }}">
                            {{ $statusLabel[$status] ?? ucfirst($status) }}
                        </span>
                    </td>
                    <td>
                        <div class="aksi">
                            <a href="{{ url('/admin-hima/lomba/'.$lomba->id.'/edit') }}">Edit</a>
                            <span aria-hidden="true">·</span>
                            <form method="POST"
                                  action="{{ url('/admin-hima/lomba/'.$lomba->id) }}"
                                  onsubmit="return confirm('Hapus lomba &quot;{{ addslashes($lomba->judul) }}&quot;? Tindakan ini tidak bisa dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">
                        @if (request()->filled('q'))
                            Tidak ada lomba yang cocok dengan "{{ request('q') }}". Coba kata kunci lain.
                        @else
                            Belum ada lomba. Klik "Tambah lomba" untuk menambahkan yang pertama.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-foot">
    <div class="info">Menampilkan {{ $shown }} dari {{ $total }} data</div>

    @if ($last > 1)
        <nav class="pager" aria-label="Halaman">
            {{-- Sebelumnya --}}
            @if ($lombas->onFirstPage())
                <span class="disabled" aria-disabled="true">Sebelumnya</span>
            @else
                <a href="{{ $lombas->appends(request()->query())->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif

            {{-- Nomor halaman (jendela 2 halaman di kiri/kanan halaman aktif) --}}
            @php
                $start = max(1, $current - 2);
                $end   = min($last, $current + 2);
            @endphp

            @if ($start > 1)
                <a href="{{ $lombas->appends(request()->query())->url(1) }}">1</a>
                @if ($start > 2)<span class="gap">…</span>@endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="current" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $lombas->appends(request()->query())->url($page) }}">{{ $page }}</a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)<span class="gap">…</span>@endif
                <a href="{{ $lombas->appends(request()->query())->url($last) }}">{{ $last }}</a>
            @endif

            {{-- Berikutnya --}}
            @if ($lombas->hasMorePages())
                <a href="{{ $lombas->appends(request()->query())->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else
                <span class="disabled" aria-disabled="true">Berikutnya</span>
            @endif
        </nav>
    @endif
</div>

<p class="note">Informasi ditambahkan dan diperbarui secara manual oleh Admin HIMA.</p>
@endsection