@extends('layouts.admin-hima')

@section('title', 'Tinjau ' . $usulan->nomor)
@section('page_title', 'Tinjau Usulan')

@section('content')
@include('partials.usulan-styles')

@php
    $aktif = in_array($usulan->status, ['menunggu', 'diproses'], true);

    // Data awal form publikasi diambil dari usulan mahasiswa
    $draftLomba = new \App\Models\Lomba([
        'judul_lomba'      => $usulan->judul_lomba,
        'link_pendaftaran' => $usulan->link_resmi,
        'deskripsi'        => $usulan->deskripsi,
    ]);
@endphp

<div class="page-head">
    <h2>Tinjau usulan</h2>
    <p><a href="{{ route('admin.usulan.index') }}">Tinjau Usulan</a> / {{ $usulan->nomor }}</p>
</div>

<div class="card">
    <h3>Detail usulan</h3>
    <dl class="detail">
        <dt>Nomor usulan</dt>
        <dd>{{ $usulan->nomor }}</dd>

        <dt>Pengaju</dt>
        <dd>{{ $usulan->pengaju?->name ?? '-' }}</dd>

        <dt>Diterima</dt>
        <dd>{{ $usulan->created_at->locale('id')->translatedFormat('d M Y') }}</dd>

        <dt>Status</dt>
        <dd><span class="badge {{ $usulan->badge_class }}">{{ $usulan->status_label }}</span></dd>

        <dt>Nama lomba</dt>
        <dd>{{ $usulan->judul_lomba }}</dd>

        <dt>Tautan resmi</dt>
        <dd><a href="{{ $usulan->link_resmi }}" target="_blank" rel="noopener noreferrer">{{ $usulan->link_resmi }}</a></dd>

        <dt>Deskripsi</dt>
        <dd>{!! nl2br(e($usulan->deskripsi)) !!}</dd>

        @if ($usulan->catatan_admin)
            <dt>Catatan HIMA</dt>
            <dd>{{ $usulan->catatan_admin }}</dd>
        @endif

        @if ($usulan->status === 'dipublikasikan' && $usulan->lomba)
            <dt>Lomba</dt>
            <dd><a href="{{ route('admin.lomba.edit', $usulan->lomba) }}">Lihat di Kelola Lomba</a></dd>
        @endif
    </dl>
</div>

@if ($usulan->status === 'menunggu')
    <div class="card">
        <h3>Mulai peninjauan</h3>
        <p class="hint-text">Tandai usulan ini sedang diproses agar pengaju tahu HIMA sudah membukanya.</p>
        <form method="POST" action="{{ route('admin.usulan.proses', $usulan) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-primary">Proses usulan</button>
        </form>
    </div>
@endif

@if ($aktif)
    {{-- Form publikasi: lengkapi data lomba, lalu simpan ke tabel lomba --}}
    @include('admin-hima.lomba._form', [
        'lomba'       => $draftLomba,
        'action'      => route('admin.usulan.publikasikan', $usulan),
        'method'      => 'POST',
        'submitLabel' => 'Publikasikan lomba',
        'cancelUrl'   => route('admin.usulan.index'),
        'hideStatus'  => true,
    ])

    <form method="POST" action="{{ route('admin.usulan.tolak', $usulan) }}" class="card" novalidate
          onsubmit="return confirm('Tolak usulan ini?');">
        @csrf
        @method('PATCH')
        <h3>Tolak usulan</h3>

        <div class="field @if ($errors->tolak->has('catatan_admin')) has-error @endif">
            <label for="catatan_admin">Alasan penolakan *</label>
            <textarea id="catatan_admin" name="catatan_admin" maxlength="1000" required>{{ old('catatan_admin') }}</textarea>
            @if ($errors->tolak->has('catatan_admin'))
                <div class="error-text">{{ $errors->tolak->first('catatan_admin') }}</div>
            @else
                <div class="hint">Alasan ini akan terlihat oleh pengaju.</div>
            @endif
        </div>

        <button type="submit" class="btn btn-danger">Tolak usulan</button>
    </form>
@endif
@endsection
