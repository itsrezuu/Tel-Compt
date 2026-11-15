@extends('layouts.dosen')
@use('App\Models\Bimbingan')
@use('Illuminate\Support\Carbon')
@use('Illuminate\Support\Str')

@section('title', 'Riwayat konsultasi & bimbingan')
@section('page_title', 'Riwayat')

@push('styles')
    @include('bimbingan._styles')
    <style>
        .history-filter-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
        .history-period { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .history-period label { color: var(--muted); font-size: 13px; }
        .history-period .bm-input { width: 170px; padding: 10px 12px; }
        .history-period .bm-btn { padding: 10px 14px; }
        .history-pagination { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .history-pagination p { margin: 0; color: var(--muted); font-size: 12px; }
        .history-note { white-space: pre-line; }
        @media (max-width: 620px) {
            .history-period { width: 100%; }
            .history-period .bm-input { flex: 1; min-width: 140px; }
        }
    </style>
@endpush

@php
    $jenisFilter = [
        'semua' => 'Semua',
        'konsultasi' => 'Konsultasi',
        'bimbingan' => 'Bimbingan',
    ];
@endphp

@section('content')
<div class="bm-page">
    <div class="bm-head">
        <div>
            <h2>Riwayat konsultasi &amp; bimbingan</h2>
            <p>Permintaan yang telah Anda tanggapi dan putuskan.</p>
        </div>
    </div>

    <div class="history-filter-row">
        <nav class="bm-tabs" aria-label="Filter jenis permintaan">
            @foreach ($jenisFilter as $key => $label)
                <a
                    href="{{ route('dosen.riwayat', ['jenis' => $key, 'periode' => $periode ?: null]) }}"
                    class="bm-tab {{ $jenis === $key ? 'active' : '' }}"
                    @if ($jenis === $key) aria-current="page" @endif
                >{{ $label }}</a>
            @endforeach
        </nav>

        <form class="history-period" method="GET" action="{{ route('dosen.riwayat') }}">
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <label for="periode">Periode bulan</label>
            <input class="bm-input" type="month" id="periode" name="periode" value="{{ $periode }}">
            <button class="bm-btn bm-btn-ghost" type="submit">Terapkan</button>
            @if ($periode !== '')
                <a class="bm-link" href="{{ route('dosen.riwayat', ['jenis' => $jenis]) }}">Hapus</a>
            @endif
        </form>
    </div>

    <div class="bm-table-wrap">
        <table class="bm-table">
            <thead>
                <tr>
                    <th>Permintaan</th>
                    <th>Mahasiswa / tim</th>
                    <th>Selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="{{ $pilih && $pilih->kode === $item->kode ? 'selected' : '' }}">
                        <td>
                            <span class="strong">{{ $item->kode }} · {{ Str::limit($item->permintaan, 46) }}</span>
                            <span class="sub">{{ $item->jenis === 'konsultasi' ? 'Konsultasi' : 'Bimbingan' }}</span>
                        </td>
                        <td>{{ $item->jenis === 'bimbingan' ? $item->nama_tim : $item->mahasiswa }}</td>
                        <td>{{ Bimbingan::formatWaktu(Carbon::parse($item->selesai)) }}</td>
                        <td>@include('bimbingan._status', ['status' => $item->status])</td>
                        <td>
                            <a
                                class="bm-link"
                                href="{{ route('dosen.riwayat', [
                                    'jenis' => $jenis,
                                    'periode' => $periode ?: null,
                                    'pilih' => $item->jenis.':'.$item->id,
                                    'page' => $items->currentPage(),
                                ]) }}"
                            >
                                @if ($item->status === 'ditolak')
                                    Lihat alasan
                                @elseif ($item->jenis === 'konsultasi')
                                    Lihat tanggapan
                                @else
                                    Lihat keputusan
                                @endif
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">Belum ada permintaan yang telah ditanggapi atau diputuskan pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="history-pagination">
        <p>
            Menampilkan {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}
            dari {{ $items->total() }} data
        </p>
        @include('partials.pager', ['paginator' => $items])
    </div>

    @if ($pilih)
        <section class="bm-card" aria-labelledby="history-selection-title">
            <h3 id="history-selection-title">
                {{ $pilih->jenis === 'bimbingan' ? 'Keputusan terpilih' : 'Tanggapan terpilih' }}
                · {{ $pilih->kode }}
            </h3>
            <p class="bm-text">
                <strong>{{ Str::limit($pilih->permintaan, 100) }}</strong>
                · @include('bimbingan._status', ['status' => $pilih->status])
                {{ Bimbingan::formatWaktu(Carbon::parse($pilih->selesai)) }}
            </p>
            <p class="bm-text history-note">{{ $pilih->catatan }}</p>
            <p class="bm-meta">Tanggapan dan keputusan tercatat pada riwayat mahasiswa.</p>
        </section>
    @endif
</div>
@endsection