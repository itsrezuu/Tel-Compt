@extends('layouts.mahasiswa')

@section('title', 'Riwayat Pengajuan Prestasi')
@section('page_title', 'Riwayat Pengajuan')

@section('content')
@include('mahasiswa.prestasi._styles')

<div class="prestasi-page-head">
    <h2>Arsip &amp; riwayat pengajuan</h2>
    <p>Daftar prestasi dan pengalaman yang sudah kamu kirim.</p>
</div>

<div class="prestasi-actions" style="margin-top: 20px">
    <a href="{{ route('mahasiswa.prestasi.create') }}" class="prestasi-btn primary">Kirim prestasi</a>
</div>

@forelse ($pengajuans as $pengajuan)
    @php
        $statusBadgeClass = match ($pengajuan->status) {
            'disetujui', 'selesai' => 'success',
            'ditolak' => 'rejected',
            default => 'waiting',
        };
    @endphp
    <article class="prestasi-record">
        <div class="prestasi-record-header">
            <div>
                <h3>{{ $pengajuan->nomor_pengajuan }} · {{ $pengajuan->judul_arsip }}</h3>
                <p>Dikirim {{ $pengajuan->created_at->locale('id')->translatedFormat('d M Y') }}</p>
            </div>
            <span class="prestasi-badge {{ $statusBadgeClass }}">{{ $pengajuan->status_label }}</span>
        </div>

        <dl class="prestasi-detail">
            <dt>Lomba</dt>
            <dd>{{ $pengajuan->lomba }}</dd>
            <dt>Prestasi / hasil</dt>
            <dd>{{ $pengajuan->prestasi_hasil }}</dd>
            <dt>Tanggal pencapaian</dt>
            <dd>{{ $pengajuan->tanggal_pencapaian->locale('id')->translatedFormat('d M Y') }}</dd>
            <dt>Peserta / tim</dt>
            <dd>{{ $pengajuan->peserta_tim }}</dd>
            <dt>Pengalaman &amp; proses</dt>
            <dd>{{ $pengajuan->pengalaman_proses }}</dd>
            <dt>Pembelajaran &amp; saran</dt>
            <dd>{{ $pengajuan->pembelajaran_saran }}</dd>
        </dl>

        <div class="prestasi-links" aria-label="Berkas pengajuan">
            <a class="prestasi-link" href="{{ route('mahasiswa.prestasi.berkas', ['pengajuan' => $pengajuan->getKey(), 'jenis' => 'bukti-pendukung']) }}">Unduh bukti pendukung</a>
            <a class="prestasi-link" href="{{ route('mahasiswa.prestasi.berkas', ['pengajuan' => $pengajuan->getKey(), 'jenis' => 'keluaran-lomba']) }}">Unduh keluaran lomba</a>
            @if ($pengajuan->dokumen_pengalaman_path)
                <a class="prestasi-link" href="{{ route('mahasiswa.prestasi.berkas', ['pengajuan' => $pengajuan->getKey(), 'jenis' => 'dokumen-pengalaman']) }}">Unduh dokumen pengalaman</a>
            @endif
        </div>
    </article>
@empty
    <div class="prestasi-empty">Belum ada pengajuan prestasi. Kirim pengajuan pertamamu dari halaman Prestasi &amp; Arsip.</div>
@endforelse

@if ($pengajuans->hasPages())
    <div class="table-foot">
        <div class="info">Menampilkan {{ $pengajuans->count() }} dari {{ $pengajuans->total() }} data</div>
        @include('partials.pager', ['paginator' => $pengajuans])
    </div>
@endif
@endsection