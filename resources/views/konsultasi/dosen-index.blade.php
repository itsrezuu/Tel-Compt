@extends('layouts.dosen')
@use('App\Models\Bimbingan')
@use('Illuminate\Support\Str')

@section('title', 'Permintaan Konsultasi')
@section('page_title', 'Permintaan Konsultasi')

@push('styles')
    @include('bimbingan._styles')
@endpush

@php
    $tabs = ['menunggu' => 'Menunggu', 'ditanggapi' => 'Ditanggapi', 'ditolak' => 'Ditolak'];
@endphp

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div class="bm-head">
        <div>
            <h2>Permintaan konsultasi</h2>
            <p>Pertanyaan dari mahasiswa bimbingan Anda. Mahasiswa baru bisa bertanya lagi setelah pertanyaan sebelumnya dijawab.</p>
        </div>
    </div>

    <div class="bm-tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dosen.konsultasi.index', ['status' => $key, 'q' => $q ?: null]) }}"
               class="bm-tab {{ $status === $key ? 'active' : '' }}">
                {{ $label }}@if ($key === 'menunggu') ({{ $counts['menunggu'] ?? 0 }})@endif
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('dosen.konsultasi.index') }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <label class="bm-search-label" for="q">Cari konsultasi</label>
        <div class="bm-search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input id="q" name="q" value="{{ $q }}" placeholder="Mahasiswa, tim, topik atau isi pertanyaan">
        </div>
    </form>

    <div class="bm-table-wrap">
        <table class="bm-table">
            <thead>
                <tr>
                    <th>Konsultasi</th>
                    <th>Mahasiswa / tim</th>
                    <th>Diterima</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <span class="strong">{{ $item->kode }} · {{ Str::limit($item->bimbingan->topik_judul, 30) }}</span>
                            <span class="sub">{{ Str::limit($item->pertanyaan, 70) }}</span>
                        </td>
                        <td>{{ $item->mahasiswa->name }} · {{ $item->bimbingan->nama_tim }}</td>
                        <td>{{ Bimbingan::formatWaktu($item->created_at) }}</td>
                        <td>@include('bimbingan._status', ['status' => $item->status])</td>
                        <td>
                            <a class="bm-link" href="{{ route('dosen.konsultasi.show', $item) }}">
                                {{ $item->status === 'menunggu' ? 'Buka & tanggapi' : 'Lihat tanggapan' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada konsultasi pada tab ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
