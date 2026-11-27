@extends('layouts.admin-hima')
@section('title', 'Kelola Konten')
@section('page_title', 'Kelola Konten')

@push('styles')
<style>
.kc-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:26px}
.kc-head h2{margin:0 0 6px;font-size:27px}
.kc-head p{margin:0;color:var(--muted)}
.kc-add{display:inline-flex;align-items:center;gap:8px;padding:12px 18px;border:0;border-radius:7px;background:var(--primary);color:#fff;font-weight:600}
.kc-add:hover{background:var(--primary-dark)}
.kc-label{font-weight:600;margin:28px 0 14px}
.kc-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
.kc-tabs a{padding:11px 13px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--muted)}
.kc-tabs a.active{border-color:var(--primary);color:var(--primary);font-weight:600}
.kc-search{display:flex;gap:10px;margin-bottom:20px}
.kc-search input{flex:1;min-width:0;padding:12px;border:1px solid var(--line);border-radius:7px;font:inherit}
.kc-search button{padding:0 18px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font-weight:600}
.kc-table{overflow:auto;border:1px solid var(--line);border-radius:11px;background:#fff}
.kc-table table{width:100%;border-collapse:collapse;min-width:760px}
.kc-table th{text-align:left;padding:14px 18px;background:#fbfcfe;color:var(--muted);font-size:12px}
.kc-table td{padding:17px 18px;border-top:1px solid var(--line);vertical-align:middle}
.kc-table .title{font-weight:600}
.kc-table small{display:block;margin-top:5px;color:var(--muted)}
.kc-badge{display:inline-flex;padding:4px 9px;border-radius:6px;background:#e8f7ee;color:#14803f;font-size:12px;font-weight:600}
.kc-badge.draft{background:#edf3ff;color:#2563eb}
.kc-action{display:flex;align-items:center;gap:12px;white-space:nowrap}
.kc-action a,.kc-action button{border:0;background:none;padding:0;color:var(--primary);font:inherit;font-weight:600;cursor:pointer}
.kc-footer{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-top:18px;color:var(--muted);font-size:13px}
.kc-note{margin-top:28px;color:var(--muted);font-size:12px}
@media(max-width:640px){.kc-head h2{font-size:23px}.kc-search{flex-wrap:wrap}.kc-search button{height:44px}}
</style>
@endpush

@section('content')
<header class="kc-head">
    <div>
        <h2>Kelola Basis Pengetahuan &amp; Galeri Prestasi</h2>
        <p>Kelola artikel dan atur publikasi prestasi yang telah diverifikasi.</p>
    </div>
    <a class="kc-add" href="{{ route('admin.konten.create') }}">＋ Tambah konten</a>
</header>

<div class="kc-label">Konten terkurasi</div>

<nav class="kc-tabs" aria-label="Jenis konten">
    @foreach (['semua' => 'Semua', 'basis_pengetahuan' => 'Basis Pengetahuan', 'galeri_prestasi' => 'Galeri Prestasi', 'draf' => 'Draf'] as $key => $label)
        <a
            href="{{ route('admin.konten.index', $key === 'draf' ? ['status' => 'draf'] : ['jenis' => $key]) }}"
            class="{{ ($key === 'draf' ? $status === 'draf' : $jenis === $key && $status === 'semua') ? 'active' : '' }}"
        >
            {{ $label }} ({{ $counts[$key] }})
        </a>
    @endforeach
</nav>

<form class="kc-search" method="GET" action="{{ route('admin.konten.index') }}">
    <input type="hidden" name="jenis" value="{{ $jenis }}">
    <input type="hidden" name="status" value="{{ $status }}">
    <label for="konten-q">Cari konten</label>
    <input id="konten-q" name="q" type="search" value="{{ request('q') }}"
           placeholder="Cari judul, lomba atau nama mahasiswa">
    <button type="submit">Cari</button>
</form>

<div class="kc-table">
    <table>
        <thead>
            <tr>
                <th>Judul konten</th>
                <th>Jenis</th>
                <th>Sumber</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($content as $item)
            <tr>
                <td>
                    <span class="title">{{ $item->judul }}</span>
                    <small>{{ $item->subjudul ?: 'Tanpa nama lomba' }}</small>
                </td>
                <td>{{ $item->jenis === 'basis_pengetahuan' ? 'Basis Pengetahuan' : 'Galeri Prestasi' }}</td>
                <td>{{ $item->sumber ?: '—' }}</td>
                <td>
                    <span class="kc-badge {{ $item->status === 'draf' ? 'draft' : '' }}">
                        {{ $item->status === 'draf' ? 'Draf / disembunyikan' : 'Dipublikasikan' }}
                    </span>
                </td>
                <td>
                    <div class="kc-action">
                    @if ($item->jenis === 'basis_pengetahuan')
                        <a href="{{ route('admin.konten.edit', $item->id) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.konten.publikasi', $item->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">
                                {{ $item->status === 'dipublikasikan' ? 'Tarik publikasi' : 'Publikasikan' }}
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.konten.galeri.publikasi', $item->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit">
                                {{ $item->status === 'dipublikasikan' ? 'Tarik publikasi' : 'Publikasikan' }}
                            </button>
                        </form>
                    @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;padding:36px;color:var(--muted)">
                    Belum ada konten yang cocok dengan filter ini.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="kc-footer">
    <span>Menampilkan {{ $content->count() }} dari {{ $content->total() }} data</span>
    @include('partials.pager', ['paginator' => $content])
</div>

<p class="kc-note">
    Galeri Prestasi hanya mengambil pengajuan dengan prestasi terverifikasi dan persetujuan publikasi.
</p>
@endsection
