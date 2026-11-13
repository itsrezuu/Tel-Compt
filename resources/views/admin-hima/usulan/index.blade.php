@extends('layouts.admin-hima')

@section('title', 'Tinjau Usulan')
@section('page_title', 'Tinjau Usulan')

@section('content')
@include('partials.usulan-styles')

@php
    $tabs = [
        'menunggu'       => 'Menunggu',
        'diproses'       => 'Diproses',
        'dipublikasikan' => 'Dipublikasikan',
        'ditolak'        => 'Ditolak',
    ];
@endphp

<div class="page-head">
    <h2>Antrean usulan lomba</h2>
    <p>Tinjau sumber dan kelengkapan informasi sebelum publikasi.</p>
</div>

<div class="tabs">
    @foreach ($tabs as $key => $label)
        <a href="{{ route('admin.usulan.index', ['status' => $key]) }}"
           class="tab {{ $status === $key ? 'active' : '' }}"
           @if ($status === $key) aria-current="page" @endif>
            {{ $label }}@if (in_array($key, ['menunggu', 'diproses'], true)) ({{ $counts[$key] ?? 0 }})@endif
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.usulan.index') }}" role="search">
    <input type="hidden" name="status" value="{{ $status }}">
    <label for="q" class="search-label">Cari usulan</label>
    <div class="search-box">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Judul, nomor usulan atau pengaju" autocomplete="off">
    </div>
</form>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th scope="col">Usulan</th>
                <th scope="col">Pengaju</th>
                <th scope="col">Diterima</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usulans as $usulan)
                <tr>
                    <td class="judul">{{ $usulan->nomor }} · {{ $usulan->judul_lomba }}</td>
                    <td>{{ $usulan->pengaju?->name ?? '-' }}</td>
                    <td>{{ $usulan->created_at->locale('id')->translatedFormat('d M Y') }}</td>
                    <td><span class="badge {{ $usulan->badge_class }}">{{ $usulan->status_label }}</span></td>
                    <td><a class="link" href="{{ route('admin.usulan.show', $usulan) }}">Buka tinjauan</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">
                        @if (request()->filled('q'))
                            Tidak ada usulan yang cocok dengan "{{ request('q') }}".
                        @else
                            Belum ada usulan dengan status {{ strtolower($tabs[$status]) }}.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-foot">
    <div class="info">Menampilkan {{ $usulans->count() }} dari {{ $usulans->total() }} data</div>
    @include('partials.pager', ['paginator' => $usulans])
</div>
@endsection
