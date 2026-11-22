@extends('layouts.mahasiswa')

@section('title', 'Riwayat Aktivitas')
@section('page_title', 'Riwayat Aktivitas')

@section('content')

<style>
    .history-page {
        max-width: 1100px;
        margin: 0 auto;
    }

    .history-heading {
        margin-bottom: 24px;
    }

    .history-heading h2 {
        margin-bottom: 8px;
        font-size: 24px;
        font-weight: 700;
    }

    .history-heading p {
        color: #64748b;
        margin: 0;
    }

    .history-summary {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .history-summary-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        flex: 1;
        min-width: 180px;
    }

    .history-summary-card span {
        display: block;
        color: #64748b;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .history-summary-card strong {
        font-size: 24px;
        color: #172b5c;
    }

    .history-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .history-item {
        display: flex;
        gap: 16px;
        padding: 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }

    .history-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eef2ff;
        color: #172b5c;
        font-size: 19px;
    }

    .history-detail {
        flex: 1;
        min-width: 0;
    }

    .history-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }

    .history-type {
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .history-date {
        color: #94a3b8;
        font-size: 12px;
    }

    .history-title {
        font-size: 16px;
        font-weight: 700;
        color: #172b5c;
        margin: 0 0 6px;
    }

    .history-description {
        color: #64748b;
        font-size: 13px;
        margin: 0 0 12px;
        overflow-wrap: anywhere;
    }

    .history-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .history-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    .history-link {
        color: #2563eb;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .history-link:hover {
        text-decoration: underline;
    }

    .history-empty {
        text-align: center;
        padding: 48px 20px;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
    }

    .history-empty h3 {
        color: #172b5c;
        margin-bottom: 8px;
    }

    .history-empty p {
        color: #64748b;
        margin: 0;
    }

    @media (max-width: 600px) {
        .history-item {
            padding: 14px;
        }

        .history-summary-card {
            min-width: 130px;
        }
    }
</style>

<div class="history-page">

    <div class="history-heading">
        <h2>Riwayat Aktivitas</h2>
        <p>
            Pantau seluruh aktivitasmu di Tel-Compt dalam satu halaman.
        </p>
    </div>

    <div class="history-summary">
        <div class="history-summary-card">
            <span>Total Aktivitas</span>
            <strong>{{ $aktivitas->count() }}</strong>
        </div>

        <div class="history-summary-card">
            <span>Aktivitas Terbaru</span>
            <strong>
                {{ $aktivitas->isNotEmpty() ? $aktivitas->first()['tanggal']->format('d/m/Y') : '-' }}
            </strong>
        </div>
    </div>

    @if ($aktivitas->isEmpty())

        <div class="history-empty">
            <h3>Belum Ada Aktivitas</h3>
            <p>
                Aktivitasmu akan muncul di sini setelah kamu menggunakan
                fitur Tel-Compt.
            </p>
        </div>

    @else

        <div class="history-list">

            @foreach ($aktivitas as $item)

                <article class="history-item">

                    <div class="history-icon">
                        @switch($item['jenis'])
                            @case('Bookmark Lomba')
                                ♡
                                @break

                            @case('Usulan Lomba')
                                ↗
                                @break

                            @case('Pengajuan Prestasi')
                                🏆
                                @break

                            @case('Bimbingan')
                                👥
                                @break

                            @case('Konsultasi')
                                💬
                                @break

                            @default
                                •
                        @endswitch
                    </div>

                    <div class="history-detail">

                        <div class="history-top">
                            <span class="history-type">
                                {{ $item['jenis'] }}
                            </span>

                            <span class="history-date">
                                {{ $item['tanggal']->format('d M Y, H:i') }}
                            </span>
                        </div>

                        <h3 class="history-title">
                            {{ $item['judul'] }}
                        </h3>

                        <p class="history-description">
                            {{ $item['deskripsi'] }}
                        </p>

                        <div class="history-bottom">
                            <span class="history-status">
                                {{ $item['status'] }}
                            </span>

                            @if (!empty($item['url']))
                                <a href="{{ $item['url'] }}" class="history-link">
                                    Lihat Detail →
                                </a>
                            @endif
                        </div>

                    </div>

                </article>

            @endforeach

        </div>

    @endif

</div>

@endsection
