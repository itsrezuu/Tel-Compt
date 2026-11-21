@extends('layouts.admin-hima')
@use('App\Models\Bimbingan')

@section('title', 'Dasbor')
@section('page_title', 'Ringkasan')

@push('styles')
    @include('partials.dasbor-styles')
@endpush

@section('content')
<div class="dsh-page">
    <div class="dsh-head">
        <h2>Ringkasan operasional</h2>
        <p>Prioritaskan tinjauan usulan, verifikasi prestasi dan moderasi arsip.</p>
    </div>

    <div class="dsh-stats">
        <div class="dsh-stat">
            <div class="label">Usulan menunggu</div>
            <div class="value">{{ $usulanMenunggu }}</div>
            <div class="sub">{{ $usulanBaru }} baru hari ini</div>
        </div>
        <div class="dsh-stat">
            <div class="label">Prestasi menunggu</div>
            <div class="value">{{ $prestasiMenunggu }}</div>
            <div class="sub">Perlu verifikasi bukti</div>
        </div>
        <div class="dsh-stat">
            <div class="label">Arsip menunggu</div>
            <div class="value">{{ $arsipMenunggu }}</div>
            <div class="sub">Perlu moderasi</div>
        </div>
        <div class="dsh-stat">
            <div class="label">Lomba aktif</div>
            <div class="value">{{ $lombaAktif }}</div>
            <div class="sub">Dikurasi manual</div>
        </div>
    </div>

    @if ($lombaMendekatiTenggat > 0)
        <div class="dsh-alert warn" role="status">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <div>
                <strong>{{ $lombaMendekatiTenggat }} lomba mendekati tenggat</strong>
                <p>Tenggat pendaftarannya dalam 7 hari. Periksa tanggal dan tautan penyelenggara sebelum informasi dipublikasikan.</p>
            </div>
        </div>
    @endif

    <section class="dsh-card">
        <h3>Antrean prioritas</h3>
        <p class="desc">Pengajuan yang paling lama menunggu ditampilkan paling atas.</p>
        <div class="dsh-table-wrap">
            <table class="dsh-table">
                <thead>
                    <tr>
                        <th>Pengajuan</th>
                        <th>Jenis</th>
                        <th>Diterima</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($antrean as $baris)
                        <tr>
                            <td class="strong">{{ $baris['kode'] }}</td>
                            <td>{{ $baris['jenis'] }}</td>
                            <td>{{ Bimbingan::formatWaktu($baris['diterima']) }}</td>
                            <td><span class="dsh-badge {{ $baris['badge'] }}">{{ $baris['status'] }}</span></td>
                            <td><a class="dsh-link" href="{{ $baris['url'] }}">{{ $baris['aksi'] }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Tidak ada pengajuan yang menunggu. Semua sudah ditinjau.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="dsh-grid-2" aria-label="Grafik operasional">
        <div class="dsh-card">
            <h3>Pengajuan masuk per bulan</h3>
            <p class="desc">Usulan lomba dan pengajuan prestasi &amp; arsip, enam bulan terakhir.</p>
            @include('partials.chart-columns', [
                'labels' => $grafik['tren']['labels'],
                'series' => $grafik['tren']['series'],
                'ariaLabel' => 'Grafik batang pengajuan usulan lomba dan prestasi per bulan selama enam bulan terakhir',
            ])
        </div>

        <div class="dsh-card">
            <h3>Status usulan lomba</h3>
            <p class="desc">Sebaran seluruh usulan mahasiswa menurut tahap pemrosesan.</p>
            @include('partials.chart-donut', [
                'items' => $grafik['usulan'],
                'pusat' => 'usulan',
                'ariaLabel' => 'Grafik donat jumlah usulan lomba menurut status',
            ])
        </div>

        <div class="dsh-card">
            <h3>Lomba tayang per kategori</h3>
            <p class="desc">Dipisah menurut asal: usulan mahasiswa atau dikelola langsung admin.</p>
            @include('partials.chart-stack', [
                'labels' => $grafik['kategori']['labels'],
                'series' => $grafik['kategori']['series'],
                'ariaLabel' => 'Grafik batang jumlah lomba tayang per kategori menurut asalnya',
            ])
        </div>

        <div class="dsh-card">
            <h3>Prestasi &amp; arsip menurut tahap</h3>
            <p class="desc">Posisi seluruh pengajuan prestasi dan arsip pengalaman.</p>
            @include('partials.chart-bars', [
                'labels' => $grafik['prestasi']['labels'],
                'values' => $grafik['prestasi']['values'],
                'teks' => $grafik['prestasi']['values'],
                'color' => '#2563eb',
                'ariaLabel' => 'Grafik batang jumlah pengajuan prestasi dan arsip menurut tahap verifikasi',
            ])
        </div>
    </section>

    <section class="dsh-grid-2">
        <div class="dsh-card">
            <h3>Kurasi lomba</h3>
            <p class="text">Periksa kelengkapan informasi dan sumber resmi. Tidak ada pengambilan data otomatis.</p>
            <div class="btn-row"><a class="dsh-btn primary" href="{{ route('admin.lomba.index') }}">Kelola lomba</a></div>
        </div>
        <div class="dsh-card">
            <h3>Publikasi terverifikasi</h3>
            <p class="text">Prestasi dan pengalaman hanya dipublikasikan setelah bukti dan isi selesai ditinjau.</p>
            <div class="btn-row"><a class="dsh-btn" href="{{ url('/admin-hima/konten') }}">Kelola konten</a></div>
        </div>
    </section>
</div>
@endsection
