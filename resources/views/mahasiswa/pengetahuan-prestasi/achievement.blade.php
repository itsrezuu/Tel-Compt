@extends('layouts.mahasiswa')
@section('title', 'Prestasi · '.$pengajuan->prestasi_hasil)
@section('page_title', 'Galeri Prestasi')

@push('styles')
<style>
.pa-wrap{max-width:920px}
.pa-back{display:inline-flex;margin-bottom:20px;color:var(--primary);font-weight:600}
.pa-card{padding:28px;border:1px solid var(--line);border-radius:11px;background:#fff}
.pa-tag{display:inline-flex;padding:4px 9px;border-radius:6px;background:#effbf3;color:#14803f;font-size:12px;font-weight:600}
.pa-card h2{margin:16px 0 6px;font-size:30px}
.pa-card h3{margin:0 0 18px;font-size:20px}
.pa-meta{color:var(--muted);font-size:13px}
.pa-section{padding-top:22px;margin-top:22px;border-top:1px solid var(--line)}
.pa-section h4{margin:0 0 10px;font-size:16px}
.pa-section p{line-height:1.75;white-space:pre-line}
.pa-files{display:flex;gap:10px;flex-wrap:wrap}
.pa-files a{display:inline-flex;min-height:42px;align-items:center;padding:0 14px;border:1px solid var(--line);border-radius:7px;font-weight:600}
.pa-files a:hover{border-color:var(--primary);color:var(--primary)}
@media(max-width:640px){.pa-card{padding:20px}.pa-card h2{font-size:24px}}
</style>
@endpush

@section('content')
<div class="pa-wrap">
    <a class="pa-back" href="{{ route('mahasiswa.pengetahuan.index') }}">
        ← Kembali ke Pengetahuan &amp; Prestasi
    </a>

    <article class="pa-card">
        <span class="pa-tag">Terverifikasi HIMA</span>
        <h2>{{ $pengajuan->prestasi_hasil }}</h2>
        <h3>{{ $pengajuan->lomba }}</h3>

        <p class="pa-meta">
            {{ $pengajuan->mahasiswa?->name }} · {{ $pengajuan->peserta_tim }}<br>
            {{ $pengajuan->tanggal_pencapaian?->translatedFormat('d M Y') }}
            · Diverifikasi {{ $pengajuan->ditinjau_pada?->translatedFormat('d M Y') }}
        </p>

        <section class="pa-section">
            <h4>Pengalaman mengikuti lomba</h4>
            <p>{{ $pengajuan->pengalaman_proses }}</p>
        </section>

        <section class="pa-section">
            <h4>Pembelajaran dan saran</h4>
            <p>{{ $pengajuan->pembelajaran_saran }}</p>
        </section>

        <section class="pa-section">
            <h4>Bukti dan keluaran lomba</h4>
            <div class="pa-files">
                <a href="{{ route('mahasiswa.pengetahuan.berkas', [$pengajuan, 'bukti']) }}">
                    Unduh bukti prestasi
                </a>
                <a href="{{ route('mahasiswa.pengetahuan.berkas', [$pengajuan, 'keluaran']) }}">
                    Unduh keluaran lomba
                </a>
            </div>
        </section>
    </article>
</div>
@endsection
