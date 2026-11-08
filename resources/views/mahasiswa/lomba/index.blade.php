@extends('layouts.mahasiswa')

@section('title', 'Katalog lomba')

@section('content')
<div class="katalog">
    <h1 class="page-title">Katalog lomba</h1>
    <p class="page-sub">Informasi lomba terkurasi. Temukan kategori dan pendampingan yang sesuai.</p>

    <form method="GET" action="{{ route('mahasiswa.lomba.index') }}" class="card filter-card" role="search">
        <div class="search-box">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="search" name="q" value="{{ $filter['q'] ?? '' }}" maxlength="100"
                   placeholder="Cari judul atau penyelenggara" aria-label="Cari judul atau penyelenggara">
        </div>

        <div class="filter-grid">
            <div class="field">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($daftarKategori as $k)
                        <option value="{{ $k }}" @selected(($filter['kategori'] ?? '') === $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="tingkat">Tingkat</label>
                <select id="tingkat" name="tingkat">
                    <option value="">Semua tingkat</option>
                    @foreach ($daftarTingkat as $t)
                        <option value="{{ $t }}" @selected(($filter['tingkat'] ?? '') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="pendampingan">Pendampingan</label>
                <select id="pendampingan" name="pendampingan">
                    <option value="">Semua jenis</option>
                    @foreach ($daftarPendampingan as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($filter['pendampingan'] ?? '') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (!empty($filter['urut']))
            <input type="hidden" name="urut" value="{{ $filter['urut'] }}">
        @endif

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Terapkan filter</button>
            <a href="{{ route('mahasiswa.lomba.index') }}" class="btn btn-outline">Atur ulang</a>
        </div>
    </form>

    <div class="result-bar">
        <strong>{{ $daftarLomba->total() }} lomba tersedia</strong>

        <form method="GET" action="{{ route('mahasiswa.lomba.index') }}" class="sort-form">
            @foreach (['q', 'kategori', 'tingkat', 'pendampingan'] as $k)
                @if (!empty($filter[$k]))
                    <input type="hidden" name="{{ $k }}" value="{{ $filter[$k] }}">
                @endif
            @endforeach
            <label for="urut" class="sr-only">Urutkan</label>
            <select id="urut" name="urut" onchange="this.form.submit()">
                <option value="tenggat" @selected(($filter['urut'] ?? 'tenggat') === 'tenggat')>Tenggat terdekat</option>
                <option value="terbaru" @selected(($filter['urut'] ?? '') === 'terbaru')>Terbaru ditambahkan</option>
            </select>
            <noscript><button class="btn btn-outline" type="submit">Urutkan</button></noscript>
        </form>
    </div>

    @if ($daftarLomba->isEmpty())
        <div class="card empty">
            <h2>Belum ada lomba yang cocok</h2>
            <p>Coba kata kunci lain atau longgarkan filter.</p>
            <a href="{{ route('mahasiswa.lomba.index') }}" class="btn btn-outline">Atur ulang filter</a>
        </div>
    @else
        <div class="comp-grid">
            @foreach ($daftarLomba as $lomba)
                @include('mahasiswa.lomba._card')
            @endforeach
        </div>

        <div class="pager-row">
            <p class="muted small">Menampilkan {{ $daftarLomba->count() }} dari {{ $daftarLomba->total() }} data</p>
            {{ $daftarLomba->links('mahasiswa.lomba._pagination') }}
        </div>
    @endif

    <p class="muted small note">
        Situs resmi bertanda <span aria-hidden="true">↗</span> adalah tujuan eksternal. TEL-COMPT tidak menerima pendaftaran lomba.
    </p>
</div>
@endsection
