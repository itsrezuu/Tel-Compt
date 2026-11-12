@extends('layouts.mahasiswa')
@use('App\Models\Bimbingan')

@section('title', 'Bimbingan')
@section('page_title', 'Bimbingan')

@push('styles')
    @include('bimbingan._styles')
@endpush

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div class="bm-head">
        <div>
            <h2>Bimbingan saya</h2>
            <p>Pengajuan bimbingan formal ke dosen dan keputusannya.</p>
        </div>
        @if ($aktif)
            <span class="bm-btn bm-btn-disabled">Ajukan bimbingan</span>
        @else
            <a href="{{ route('bimbingan.create') }}" class="bm-btn bm-btn-primary">Ajukan bimbingan</a>
        @endif
    </div>

    @if (session('terkirim'))
        <div class="bm-banner ok">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
            <div>
                <strong>{{ session('terkirim.judul') }}</strong>
                <p>{{ session('terkirim.isi') }}</p>
            </div>
        </div>
    @endif

    @if ($aktif)
        <div class="bm-banner warn">
            <div>
                <p style="margin:0;color:inherit">
                    Kamu masih punya pengajuan yang aktif (menunggu atau disetujui), jadi belum bisa mengajukan ke dosen lain.
                    Kamu bisa mengajukan lagi setelah pengajuan ditolak.
                </p>
            </div>
        </div>
    @endif

    @forelse ($items as $item)
        <article class="bm-card bm-item">
            <div class="bm-item-top">
                <strong>{{ $item->kode }} · {{ $item->topik_judul }}</strong>
                @include('bimbingan._status', ['status' => $item->status])
            </div>
            @if ($item->lomba_judul)
                <p class="bm-text" style="margin:0">{{ $item->lomba_judul }}</p>
            @endif
            <p class="bm-text" style="margin:0">{{ $item->ringkasan_kebutuhan }}</p>
            <p class="bm-meta" style="margin:0">Tim {{ $item->nama_tim }} · Dosen tujuan: {{ $item->dosen->name }}</p>

            @if ($item->status === 'menunggu')
                <p class="bm-meta" style="margin:0">
                    Dikirim {{ Bimbingan::formatWaktu($item->created_at) }} · Persetujuan dosen belum diterima.
                    Batas respons: {{ Bimbingan::formatWaktu($item->batas_respons_at, true) }}.
                </p>
            @elseif ($item->catatan_dosen)
                <div class="bm-box {{ $item->status === 'ditolak' ? 'bad' : 'ok' }}">
                    <strong>
                        @if ($item->ditolak_otomatis) Ditolak otomatis
                        @elseif ($item->status === 'ditolak') Alasan penolakan
                        @else Arahan dosen @endif
                    </strong>
                    {{ $item->catatan_dosen }}
                    @if ($item->responded_at)
                        <p class="bm-meta">{{ Bimbingan::formatWaktu($item->responded_at, true) }}</p>
                    @endif
                </div>
            @endif

            <div>
                <a href="{{ route('bimbingan.show', $item) }}" class="bm-link">
                    {{ $item->status === 'disetujui' ? 'Buka detail & konsultasi →' : 'Lihat detail →' }}
                </a>
            </div>
        </article>
    @empty
        <div class="bm-empty">Belum ada pengajuan bimbingan. Klik "Ajukan bimbingan" untuk mulai.</div>
    @endforelse
</div>
@endsection
