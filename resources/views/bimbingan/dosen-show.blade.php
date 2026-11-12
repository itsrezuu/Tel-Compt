@extends('layouts.dosen')
@use('App\Models\Bimbingan')

@section('title', 'Permintaan Bimbingan')
@section('page_title', 'Permintaan Bimbingan')

@push('styles')
    @include('bimbingan._styles')
@endpush

@php
    $selesai = $item->status !== 'menunggu';
@endphp

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div>
        <p class="bm-crumb"><a href="{{ route('dosen.bimbingan.index') }}">Permintaan Bimbingan</a> / Informasi topik dan tim</p>
        <div class="bm-head">
            <h2>
                @if ($selesai) Bimbingan {{ $item->kode }} · {{ ucfirst($item->status) }}
                @else Detail bimbingan {{ $item->kode }} @endif
            </h2>
            @include('bimbingan._status', ['status' => $item->status])
        </div>
    </div>

    @include('bimbingan._banner', ['status' => $item->status, 'auto' => $item->ditolak_otomatis, 'untuk' => 'dosen'])

    @include('bimbingan._detail')

    <section class="bm-card">
        <h3>Keputusan &amp; arahan</h3>

        @if ($selesai)
            <p class="bm-sub" style="margin-top:0">{{ $item->status === 'ditolak' ? 'Alasan penolakan' : 'Catatan keputusan' }}</p>
            <div class="bm-box">{{ $item->catatan_dosen ?: '-' }}</div>
            <p class="bm-meta">
                {{ $item->ditolak_otomatis ? 'Ditolak otomatis oleh sistem' : $item->dosen->name }}
                · {{ Bimbingan::formatWaktu($item->responded_at, true) }}
            </p>
        @else
            <form method="POST" action="{{ route('dosen.bimbingan.respond', $item) }}">
                @csrf
                @method('PATCH')
                <div class="bm-field">
                    <label class="bm-label" for="catatan">Catatan keputusan / alasan penolakan *</label>
                    <textarea id="catatan" name="catatan" class="bm-textarea" maxlength="2000" required>{{ old('catatan') }}</textarea>
                </div>
                <div class="bm-actions">
                    <button type="submit" name="aksi" value="setuju" class="bm-btn bm-btn-primary">Setujui bimbingan</button>
                    <button type="submit" name="aksi" value="tolak" class="bm-btn bm-btn-danger">Tolak bimbingan</button>
                </div>
                <p class="bm-hint">
                    Batas respons: {{ Bimbingan::formatWaktu($item->batas_respons_at, true) }}. Jika tidak direspons sampai batas itu,
                    pengajuan ditolak otomatis dan mahasiswa bisa mengajukan lagi.
                </p>
            </form>
        @endif
    </section>
</div>
@endsection
