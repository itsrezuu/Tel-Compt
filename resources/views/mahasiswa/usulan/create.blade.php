@extends('layouts.mahasiswa')

@section('title', 'Usulkan Lomba')
@section('page_title', 'Usulkan Lomba')

@section('content')
@include('partials.usulan-styles')

<div class="page-head">
    <h2>Usulkan lomba</h2>
    <p>Tambahkan informasi yang belum ada. HIMA akan meninjau sebelum publikasi.</p>
</div>

<form method="POST" action="{{ route('mahasiswa.usulan.store') }}" class="card" novalidate>
    @csrf

    <h3>Informasi usulan</h3>
    <p class="req-note">* Kolom wajib diisi</p>

    <div class="field @error('judul_lomba') has-error @enderror">
        <label for="judul_lomba">Nama lomba *</label>
        <input type="text" id="judul_lomba" name="judul_lomba" value="{{ old('judul_lomba') }}" maxlength="255" required>
        @error('judul_lomba') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="field @error('link_resmi') has-error @enderror">
        <label for="link_resmi">Tautan resmi *</label>
        <input type="url" id="link_resmi" name="link_resmi" value="{{ old('link_resmi') }}" placeholder="https://" required>
        @error('link_resmi')
            <div class="error-text">{{ $message }}</div>
        @else
            <div class="hint">Alamat situs penyelenggara, bukan tautan pendaftaran internal.</div>
        @enderror
    </div>

    <div class="field @error('deskripsi') has-error @enderror">
        <label for="deskripsi">Deskripsi lomba *</label>
        <textarea id="deskripsi" name="deskripsi" maxlength="5000" required>{{ old('deskripsi') }}</textarea>
        @error('deskripsi') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary">Kirim usulan</button>
        <a href="{{ route('mahasiswa.usulan.create') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>

@if ($terakhir)
    @php
        $ditolak = $terakhir->status === 'ditolak';
        $tanggal = $terakhir->created_at->locale('id')->translatedFormat('d M Y');
        $pesan = match ($terakhir->status) {
            'dipublikasikan' => 'Usulan ini sudah dipublikasikan sebagai lomba.',
            'ditolak'        => 'Usulan ini ditolak.' . ($terakhir->catatan_admin ? ' Catatan HIMA: ' . $terakhir->catatan_admin : ''),
            default          => 'HIMA sedang meninjau kelengkapan informasi.',
        };
    @endphp

    <div class="card">
        <h3>Keadaan setelah dikirim</h3>

        <div class="banner {{ $ditolak ? 'no' : 'ok' }}" role="status">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                @if ($ditolak)
                    <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
                @else
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                @endif
            </svg>
            <div>
                <div class="title">
                    {{ session('usulan_baru') ? 'Usulan berhasil dikirim' : 'Usulan terakhir' }} · {{ $terakhir->nomor }}
                </div>
                <div class="desc">{{ $terakhir->judul_lomba }} diterima pada {{ $tanggal }}. {{ $pesan }}</div>
            </div>
        </div>

        <span class="badge {{ $terakhir->badge_class }}">{{ $terakhir->status_label }}</span>

        <p class="flow">Pengajuan → Menunggu → Diproses → Dipublikasikan / Ditolak</p>

        <a href="{{ route('mahasiswa.usulan.index') }}" class="btn btn-ghost">Lihat riwayat usulan</a>
    </div>
@endif
@endsection