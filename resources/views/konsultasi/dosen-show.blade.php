@extends('layouts.dosen')
@use('App\Models\Bimbingan')
@use('App\Models\Konsultasi')

@section('title', 'Log Bimbingan')
@section('page_title', 'Log Bimbingan')

@push('styles')
    @include('bimbingan._styles')
@endpush

@php
    $selesai = $item->status !== Konsultasi::MENUNGGU;
    $bimbingan = $item->bimbingan;
@endphp

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div>
        <p class="bm-crumb"><a href="{{ route('dosen.konsultasi.index') }}">Log bimbingan</a> / {{ $item->mahasiswa->name }}</p>
        <div class="bm-head">
            <h2>Log {{ $item->kode }}</h2>
            @include('bimbingan._status', ['status' => $item->status])
        </div>
    </div>

    <div class="bm-grid">
        <section class="bm-card">
            <h3>Isi log mahasiswa</h3>
            @if ($bimbingan->archived_at)
                <p class="bm-text">{{ $item->pertanyaan }}</p>
                <p class="bm-meta">Log berasal dari pengajuan arsip dan hanya dapat dilihat.</p>
            @elseif ($item->status === Konsultasi::MENUNGGU)
                <form method="POST" action="{{ route('dosen.konsultasi.update', $item) }}">
                    @csrf
                    @method('PATCH')
                    <div class="bm-field">
                        <label class="bm-label" for="pertanyaan">Isi log *</label>
                        <textarea id="pertanyaan" name="pertanyaan" class="bm-textarea" maxlength="1000" required>{{ old('pertanyaan', $item->pertanyaan) }}</textarea>
                    </div>
                    <button type="submit" class="bm-btn bm-btn-ghost">Simpan perubahan isi log</button>
                </form>
            @else
                <p class="bm-text">Isi log dikunci setelah keputusan dosen.</p>
            @endif
            <p class="bm-meta">{{ $item->mahasiswa->name }} · Dikirim {{ Bimbingan::formatWaktu($item->created_at, true) }}</p>
        </section>

        <section class="bm-card">
            <h3>Konteks bimbingan</h3>
            <p class="bm-sub" style="margin-top:0">{{ $bimbingan->kode }} · {{ $bimbingan->topik_judul }}</p>
            @if ($bimbingan->lomba_judul)
                <p class="bm-text">{{ $bimbingan->lomba_judul }}</p>
            @endif
            <p class="bm-text">Tim {{ $bimbingan->nama_tim }} · {{ $bimbingan->anggota_jumlah }} anggota</p>
            <a href="{{ route('dosen.bimbingan.show', $bimbingan) }}" class="bm-link">Lihat detail bimbingan →</a>
        </section>
    </div>

    <section class="bm-card">
        <h3>Keputusan dosen</h3>

        @if ($selesai)
            <p class="bm-sub" style="margin-top:0">{{ $item->status === Konsultasi::DITOLAK ? 'Catatan perbaikan' : 'Catatan dosen' }}</p>
            <div class="bm-box {{ $item->status === Konsultasi::DITOLAK ? 'bad' : 'ok' }}">{{ $item->jawaban_arahan }}</div>
            <p class="bm-meta">{{ $item->dosen->name }} · {{ Bimbingan::formatWaktu($item->responded_at, true) }}</p>
            @if (! $bimbingan->archived_at && $item->status === Konsultasi::DITOLAK)
                <p class="bm-hint">Mahasiswa dapat memperbaiki isi log yang sama dan mengirimkannya kembali.</p>
            @endif
        @elseif ($bimbingan->archived_at)
            <p class="bm-text">Pengajuan ini diarsipkan dan log hanya dapat dilihat.</p>
        @else
            <form method="POST" action="{{ route('dosen.konsultasi.respond', $item) }}">
                @csrf
                @method('PATCH')
                <div class="bm-field">
                    <label class="bm-label" for="catatan">Catatan keputusan *</label>
                    <textarea id="catatan" name="catatan" class="bm-textarea" maxlength="2000" required>{{ old('catatan') }}</textarea>
                </div>
                <div class="bm-actions">
                    <button type="submit" name="aksi" value="tanggapi" class="bm-btn bm-btn-primary">Setujui log</button>
                    <button type="submit" name="aksi" value="tolak" class="bm-btn bm-btn-danger">Tolak &amp; minta perbaikan</button>
                </div>
                <p class="bm-hint">Dosen dapat mengedit isi log langsung sebelum memberi keputusan.</p>
            </form>
        @endif
    </section>
</div>
@endsection