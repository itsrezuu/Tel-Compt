@extends('layouts.dosen')
@use('App\Models\Bimbingan')
@use('Illuminate\Support\Str')

@section('title', 'Permintaan Bimbingan')
@section('page_title', 'Permintaan Bimbingan')

@push('styles')
    @include('bimbingan._styles')
@endpush

@php
    $tabs = ['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
@endphp

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div class="bm-head">
        <div>
            <h2>Permintaan bimbingan formal</h2>
            <p>Tinjau topik, anggota tim dan kebutuhan pendampingan.</p>
        </div>
    </div>

    <div class="bm-tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('dosen.bimbingan.index', ['status' => $key, 'q' => $q ?: null]) }}"
               class="bm-tab {{ $status === $key ? 'active' : '' }}">
                {{ $label }}@if ($key === 'menunggu') ({{ $counts['menunggu'] ?? 0 }})@endif
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('dosen.bimbingan.index') }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <label class="bm-search-label" for="q">Cari permintaan</label>
        <div class="bm-search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input id="q" name="q" value="{{ $q }}" placeholder="Tim, judul atau nama lomba">
        </div>
    </form>

    <div class="bm-table-wrap">
        <table class="bm-table">
            <thead>
                <tr>
                    <th>Topik / lomba</th>
                    <th>Tim / ketua</th>
                    <th>Diterima</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="{{ $pilih && $pilih->bimbingan_id === $item->bimbingan_id ? 'selected' : '' }}">
                        <td class="strong">{{ $item->kode }} · {{ Str::limit($item->topik_judul, 30) }}</td>
                        <td>{{ $item->nama_tim }} · {{ Str::before($item->mahasiswa->name, ' ') }}</td>
                        <td>{{ Bimbingan::formatWaktu($item->created_at) }}</td>
                        <td>
                            @include('bimbingan._status', ['status' => $item->status])
                            @if ($item->status === 'menunggu')
                                <span class="sub">Batas {{ Bimbingan::formatWaktu($item->batas_respons_at, true) }}</span>
                            @elseif ($item->ditolak_otomatis)
                                <span class="sub">otomatis</span>
                            @endif
                        </td>
                        <td>
                            <a class="bm-link" href="{{ route('dosen.bimbingan.index', ['status' => $status, 'q' => $q ?: null, 'pilih' => $item->bimbingan_id]) }}">Tinjau detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada pengajuan pada tab ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($pilih)
        <section class="bm-card">
            <h3>Ringkasan terpilih · {{ $pilih->kode }}</h3>
            <p style="margin:0 0 12px">@include('bimbingan._status', ['status' => $pilih->status])</p>
            <p class="bm-title">{{ $pilih->topik_judul }}</p>
            <p class="bm-text" style="margin-bottom:6px">
                @if ($pilih->lomba_judul){{ $pilih->lomba_judul }} · @endif{{ $pilih->nama_tim }} · {{ $pilih->anggota_jumlah }} anggota
            </p>
            <p class="bm-text" style="color:var(--muted)">Kebutuhan: {{ Str::limit($pilih->ringkasan_kebutuhan, 160) }}</p>
            <a href="{{ route('dosen.bimbingan.show', $pilih) }}" class="bm-btn bm-btn-primary">
                {{ $pilih->status === 'menunggu' ? 'Buka detail & putuskan' : 'Buka detail' }}
            </a>
        </section>
    @endif
</div>
@endsection
