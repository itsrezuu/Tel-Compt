
@extends('layouts.dosen')

@section('title', 'Dasbor Dosen')
@section('page_title', 'Ringkasan')

@push('styles')
<style>
    .dashboard {
        max-width: 1400px;
        margin: auto;
    }

    .welcome {
        margin-bottom: 24px;
    }

    .welcome h2 {
        margin: 0 0 7px;
        font-size: 25px;
        font-weight: 700;
        color: #1b2559;
    }

    .welcome p {
        margin: 0;
        color: #71809f;
        font-size: 14px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .stat-card, .panel, .competition-card {
        background: #fff;
        border: 1px solid #dfe6f1;
        border-radius: 10px;
    }

    .stat-card {
        padding: 17px 18px;
    }

    .stat-label {
        color: #71809f;
        font-size: 12px;
        margin-bottom: 9px;
    }

    .stat-value {
        font-size: 27px;
        line-height: 1.2;
        font-weight: 700;
        color: #1b2559;
    }

    .stat-description {
        margin-top: 10px;
        color: #71809f;
        font-size: 11px;
    }

    .panel {
        padding: 21px;
        margin-bottom: 22px;
    }

    .panel-title {
        margin: 0 0 17px;
        color: #263248;
        font-size: 17px;
        font-weight: 650;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #dfe6f1;
        border-radius: 9px;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 680px;
        text-align: left;
    }

    .dashboard-table th {
        background: #f7f9fc;
        color: #71809f;
        font-size: 11px;
        font-weight: 600;
        padding: 14px 17px;
    }

    .dashboard-table td {
        padding: 17px;
        border-top: 1px solid #dfe6f1;
        color: #334155;
        font-size: 12px;
    }

    .dashboard-table td:first-child {
        font-weight: 600;
    }

    .status {
        display: inline-block;
        border-radius: 6px;
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-menunggu {
        background: #fff8e7;
        color: #b66a00;
    }

    .status-ditanggapi, .status-disetujui {
        background: #e8f7ee;
        color: #168044;
    }

    .status-ditolak {
        background: #fdecec;
        color: #bd3030;
    }

    .action-link {
        color: #2563eb;
        font-weight: 600;
    }

    .action-link:hover {
        text-decoration: underline;
    }

    .bottom-grid {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 15px;
    }

    .competition-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .competition-card {
        padding: 16px;
        min-width: 0;
    }

    .competition-category {
        display: inline-block;
        padding: 5px 9px;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 600;
    }

    .competition-card h4 {
        font-size: 15px;
        margin: 13px 0 8px;
        line-height: 1.5;
        color: #263248;
    }

    .competition-card p {
        font-size: 11px;
        color: #71809f;
        margin: 5px 0;
    }

    .saved-count {
        margin-top: 12px;
        padding-top: 11px;
        border-top: 1px solid #dfe6f1;
        font-size: 11px;
        color: #2563eb;
        font-weight: 600;
    }

    .activity-number {
        font-size: 26px;
        font-weight: 700;
        color: #1b2559;
        margin: 12px 0 16px;
    }

    .activity-description {
        color: #71809f;
        font-size: 12px;
    }

    .activity-summary {
        margin-top: 20px;
        font-size: 12px;
        color: #71809f;
    }

    .empty-state {
        padding: 28px 15px;
        text-align: center;
        color: #71809f;
        font-size: 12px;
    }

    .dashboard-footer {
        margin-top: 35px;
        color: #71809f;
        font-size: 10px;
    }

    @media(max-width: 1100px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width: 700px) {
        .bottom-grid {
            grid-template-columns: 1fr;
        }

        .competition-grid {
            grid-template-columns: 1fr;
        }

        .welcome h2 {
            font-size: 21px;
        }

        .panel {
            padding: 15px;
        }
    }

    @media(max-width: 400px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="dashboard">

    {{-- Sambutan --}}
    <section class="welcome">
        <h2>Selamat datang, {{ $user->name }}</h2>

        <p>
            Ringkasan permintaan dan aktivitas pendampingan Anda
            · {{ now()->locale('id')->translatedFormat('F Y') }}
        </p>
    </section>

    {{-- Statistik --}}
    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-label">Konsultasi menunggu</div>
            <div class="stat-value">{{ $konsultasiMenunggu }}</div>
            <div class="stat-description">Perlu tanggapan</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Bimbingan menunggu</div>
            <div class="stat-value">{{ $bimbinganMenunggu }}</div>
            <div class="stat-description">Perlu keputusan</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Konsultasi ditanggapi</div>
            <div class="stat-value">{{ $konsultasiDitanggapi }}</div>
            <div class="stat-description">Bulan ini</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Bimbingan diproses</div>
            <div class="stat-value">{{ $bimbinganDiproses }}</div>
            <div class="stat-description">
                {{ $bimbinganDisetujui }} setuju ·
                {{ $bimbinganDitolak }} tolak
            </div>
        </div>

    </section>

    {{-- Permintaan terbaru --}}
    <section class="panel">

        <h3 class="panel-title">Permintaan terbaru</h3>

        <div class="table-wrapper">

            <table class="dashboard-table">

                <thead>
                    <tr>
                        <th>Permintaan</th>
                        <th>Mahasiswa / Tim</th>
                        <th>Diterima</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($permintaanTerbaru as $item)

                        <tr>
                            <td>{{ $item['kode'] }} · {{ $item['judul'] }}</td>

                            <td>{{ $item['nama'] }}</td>

                            <td>
                                {{ $item['tanggal']->locale('id')->translatedFormat('d M Y') }}
                            </td>

                            <td>
                                <span class="status status-{{ $item['status'] }}">
                                    {{ ucfirst($item['status']) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ $item['url'] }}" class="action-link">
                                    {{ $item['status'] === 'menunggu' ? ($item['jenis'] === 'Konsultasi' ? 'Tanggapi' : 'Tinjau') : 'Lihat' }}
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="empty-state">
                                Belum ada permintaan masuk.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    {{-- Bagian bawah --}}
    <section class="bottom-grid">

        {{-- Lomba populer --}}
        <div class="panel">

            <h3 class="panel-title">Lomba yang banyak disimpan</h3>

            <p class="activity-description">
                Berdasarkan jumlah penyimpanan mahasiswa;
                bukan rekomendasi otomatis.
            </p>

            @if($lombaPopuler->isNotEmpty())

                <div class="competition-grid">

                    @foreach($lombaPopuler as $lomba)

                        <article class="competition-card">

                            <span class="competition-category">
                                {{ $lomba->kategori }} · {{ $lomba->tingkat }}
                            </span>

                            <h4>{{ $lomba->judul_lomba }}</h4>

                            <p>{{ $lomba->penyelenggara }}</p>

                            <div class="saved-count">
                                {{ $lomba->jumlah_simpan }} mahasiswa menyimpan
                            </div>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="empty-state">
                    Data lomba populer belum tersedia.
                </div>

            @endif

        </div>

        {{-- Aktivitas pribadi --}}
        <div class="panel">

            <h3 class="panel-title">Aktivitas pribadi</h3>

            <div class="activity-number">
                {{ $rataRataHari !== null ? number_format($rataRataHari, 1, ',', '.') . ' hari' : '-' }}
            </div>

            <p class="activity-description">
                Rata-rata waktu tanggapan bulan ini
            </p>

            <div style="margin-top:20px;font-weight:600;font-size:12px;color:#263248">
                Ringkasan aktivitas bulan ini
            </div>

            <p class="activity-description" style="margin-top:10px">
                {{ $konsultasiDitanggapi }} konsultasi ditanggapi
                ·
                {{ $bimbinganDiproses }} bimbingan diproses
            </p>

            <div class="activity-summary">
                Total permintaan menunggu:
                {{ $konsultasiMenunggu + $bimbinganMenunggu }}
            </div>

        </div>

    </section>

    <footer class="dashboard-footer">
        TEL-COMPT · Sistem Informasi Telkom University.
    </footer>

</div>

@endsection
