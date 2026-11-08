@extends('layouts.mahasiswa')

@section('title', $lomba->judul)

@section('content')
<div class="katalog">
    <a href="{{ route('mahasiswa.lomba.index') }}" class="back-link">← Kembali ke katalog</a>

    <article class="card detail">
        <div class="comp-top">
            <span class="tag">{{ $lomba->kategori }} · {{ $lomba->tingkat }}</span>
            <form method="POST" action="{{ route('mahasiswa.lomba.simpan', $lomba) }}">
                @csrf
                <button type="submit" class="bookmark {{ $tersimpan ? 'on' : '' }}"
                        aria-pressed="{{ $tersimpan ? 'true' : 'false' }}"
                        aria-label="{{ $tersimpan ? 'Hapus dari Lomba Saya' : 'Simpan ke Lomba Saya' }}">
                    <svg viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4-6 4z"/>@if($tersimpan)<path d="M9 9l2 2 4-4"/>@endif</svg>
                </button>
            </form>
        </div>

        <h1 class="detail-title">{{ $lomba->judul }}</h1>
        <p class="comp-org">{{ $lomba->penyelenggara }}</p>

        <dl class="detail-meta">
            <div><dt>Batas daftar</dt><dd>{{ $lomba->batas_label }}</dd></div>
            <div><dt>Tingkat</dt><dd>{{ $lomba->tingkat }}</dd></div>
            <div><dt>Kategori</dt><dd>{{ $lomba->kategori }}</dd></div>
            <div><dt>Pendampingan</dt><dd>{{ $lomba->pendampingan_label }}</dd></div>
        </dl>

        @if ($lomba->deskripsi)
            <h2 class="detail-h2">Tentang lomba</h2>
            <p class="detail-desc">{{ $lomba->deskripsi }}</p>
        @endif

        <div class="detail-cta">
            <a href="{{ $lomba->link_pendaftaran }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
                Buka situs pendaftaran <span aria-hidden="true">&nbsp;↗</span>
            </a>
        </div>
        <p class="muted small">Pendaftaran dilakukan di situs penyelenggara ({{ $lomba->situs_host }}). TEL-COMPT tidak menerima pendaftaran lomba.</p>
    </article>
</div>
@endsection
