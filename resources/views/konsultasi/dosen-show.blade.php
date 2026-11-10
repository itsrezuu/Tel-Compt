@extends('layouts.dosen')
@use('App\Models\Bimbingan')

@section('title', 'Permintaan Konsultasi')
@section('page_title', 'Permintaan Konsultasi')

@push('styles')
    @include('bimbingan._styles')
@endpush

@php
    $selesai = $item->status !== 'menunggu';
    $bimbingan = $item->bimbingan;
@endphp

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div>
        <p class="bm-crumb"><a href="{{ route('dosen.konsultasi.index') }}">Permintaan Konsultasi</a> / {{ $item->mahasiswa->name }}</p>
        <div class="bm-head">
            <h2>Konsultasi {{ $item->kode }}</h2>
            @include('bimbingan._status', ['status' => $item->status])
        </div>
    </div>

    <div class="bm-grid">
        <section class="bm-card">
            <h3>Pertanyaan mahasiswa</h3>
            <p class="bm-text">{{ $item->pertanyaan }}</p>
            <p class="bm-meta">{{ $item->mahasiswa->name }} · Dikirim {{ Bimbingan::formatWaktu($item->created_at, true) }}</p>
        </section>

        <section class="bm-card">
            <h3>Konteks bimbingan</h3>
            <p class="bm-sub" style="margin-top:0">{{ $bimbingan->kode }} · {{ $bimbingan->topik_judul }}</p>
            <p class="bm-text">Tim {{ $bimbingan->nama_tim }} · {{ $bimbingan->anggota_jumlah }} anggota</p>
            <a href="{{ route('dosen.bimbingan.show', $bimbingan) }}" class="bm-link">Lihat detail bimbingan →</a>
        </section>
    </div>

    <section class="bm-card">
        <h3>Tanggapan dosen</h3>

        @if ($selesai)
            <p class="bm-sub" style="margin-top:0">{{ $item->status === 'ditolak' ? 'Alasan penolakan' : 'Tanggapan' }}</p>
            <div class="bm-box {{ $item->status === 'ditolak' ? 'bad' : 'ok' }}">{{ $item->jawaban_arahan }}</div>
            <p class="bm-meta">{{ $item->dosen->name }} · {{ Bimbingan::formatWaktu($item->responded_at, true) }}</p>
        @else
            <form method="POST" action="{{ route('dosen.konsultasi.respond', $item) }}">
                @csrf
                @method('PATCH')
                <div class="bm-field">
                    <label class="bm-label" for="catatan">Tanggapan / alasan penolakan *</label>
                    <textarea id="catatan" name="catatan" class="bm-textarea" maxlength="2000" required>{{ old('catatan') }}</textarea>
                </div>
                <div class="bm-actions">
                    <button type="submit" name="aksi" value="tanggapi" class="bm-btn bm-btn-primary">Kirim tanggapan</button>
                    <button type="submit" name="aksi" value="tolak" class="bm-btn bm-btn-danger">Tolak konsultasi</button>
                </div>
                <p class="bm-hint">Mahasiswa tidak bisa mengirim konsultasi baru sampai pertanyaan ini ditanggapi atau ditolak.</p>
            </form>
        @endif
    </section>
</div>
@endsection
