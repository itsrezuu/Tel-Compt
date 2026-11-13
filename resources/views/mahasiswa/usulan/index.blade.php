@extends('layouts.mahasiswa')

@section('title', 'Riwayat Usulan')
@section('page_title', 'Usulkan Lomba')

@section('content')
@include('partials.usulan-styles')

<div class="page-head">
    <h2>Riwayat usulan</h2>
    <p><a href="{{ route('mahasiswa.usulan.create') }}">Usulkan Lomba</a> / Riwayat usulan</p>
</div>

<div class="table-wrap" style="margin-top: 24px">
    <table class="data-table">
        <thead>
            <tr>
                <th scope="col">Usulan</th>
                <th scope="col">Diterima</th>
                <th scope="col">Status</th>
                <th scope="col">Catatan HIMA</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usulans as $usulan)
                <tr>
                    <td class="judul">{{ $usulan->nomor }} · {{ $usulan->judul_lomba }}</td>
                    <td>{{ $usulan->created_at->locale('id')->translatedFormat('d M Y') }}</td>
                    <td><span class="badge {{ $usulan->badge_class }}">{{ $usulan->status_label }}</span></td>
                    <td>{{ $usulan->catatan_admin ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">Belum ada usulan. Kirim usulan pertamamu lewat halaman Usulkan Lomba.</td>
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
