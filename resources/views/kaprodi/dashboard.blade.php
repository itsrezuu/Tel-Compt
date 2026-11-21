@extends('layouts.kaprodi')

@section('title', 'Dasbor')
@section('page_title', 'Dasbor')

@push('styles')
    @include('partials.dasbor-styles')
@endpush

@section('content')
<div class="dsh-page">
    <div class="dsh-head">
        <h2>Dasbor keaktifan dosen</h2>
        <p>Ringkasan aktivitas konsultasi dan bimbingan dosen program studi Sistem Informasi.</p>
    </div>

    @include('kaprodi._filter', ['rute' => 'kaprodi.dashboard'])

    @include('kaprodi._stats')

    <section class="dsh-grid-2" aria-label="Grafik keaktifan dosen">
        <div class="dsh-card">
            <h3>Aktivitas per dosen</h3>
            <p class="desc">Konsultasi yang ditanggapi dan bimbingan yang diputuskan, {{ $periodeLabel }}.</p>
            @include('partials.chart-columns', [
                'labels' => $grafik['perDosen']['labels'],
                'series' => $grafik['perDosen']['series'],
                'ariaLabel' => 'Grafik batang jumlah konsultasi ditanggapi dan bimbingan diproses per dosen',
            ])
        </div>

        <div class="dsh-card">
            <h3>Rata-rata waktu tanggapan</h3>
            <p class="desc">Selisih waktu pengajuan hingga tanggapan atau keputusan pertama.</p>
            @include('partials.chart-bars', [
                'labels' => $grafik['waktu']['labels'],
                'values' => $grafik['waktu']['values'],
                'teks' => $grafik['waktu']['teks'],
                'color' => '#4c40b3',
                'ariaLabel' => 'Grafik batang rata-rata waktu tanggapan per dosen',
            ])
        </div>

        <div class="dsh-card">
            <h3>Keputusan bimbingan per dosen</h3>
            <p class="desc">Termasuk pengajuan yang ditolak otomatis karena tidak direspons dalam 1 hari.</p>
            @include('partials.chart-stack', [
                'labels' => $grafik['keputusan']['labels'],
                'series' => $grafik['keputusan']['series'],
                'ariaLabel' => 'Grafik batang bertumpuk keputusan bimbingan per dosen',
            ])
        </div>

        <div class="dsh-card">
            <h3>Aktivitas mingguan</h3>
            <p class="desc">Jumlah konsultasi dan bimbingan yang diproses tiap minggu pada {{ $periodeLabel }}.</p>
            @include('partials.chart-columns', [
                'labels' => $grafik['mingguan']['labels'],
                'series' => $grafik['mingguan']['series'],
                'ariaLabel' => 'Grafik batang aktivitas dosen yang diproses per minggu',
            ])
        </div>
    </section>

    <section class="dsh-card">
        <h3>Cara membaca grafik</h3>
        <p class="text">
            Konsultasi: jumlah permintaan yang mendapat tanggapan. Bimbingan: jumlah keputusan disetujui atau ditolak dosen.
            Bimbingan yang ditolak otomatis karena tidak direspons tidak dihitung sebagai keputusan dosen dan ditampilkan terpisah.
        </p>
        <p class="hint">Angka menunjukkan aktivitas pada periode terpilih, bukan penilaian atau peringkat dosen.</p>
        <div class="btn-row"><a class="dsh-btn primary" href="{{ route('kaprodi.aktivitas.index', ['periode' => $periodeKey]) }}">Lihat aktivitas per dosen</a></div>
    </section>
</div>
@endsection
