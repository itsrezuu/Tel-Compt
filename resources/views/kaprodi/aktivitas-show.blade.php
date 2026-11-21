@extends('layouts.kaprodi')
@use('App\Models\Bimbingan')

@section('title', 'Detail Aktivitas')
@section('page_title', 'Detail Aktivitas')

@push('styles')
    @include('partials.dasbor-styles')
@endpush

@php
    $inisial = collect(explode(' ', $nama))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $maksMinggu = max(1, collect($mingguan)->max('jumlah'));
@endphp

@section('content')
<div class="dsh-page">
    <div class="dsh-head">
        <h2>Detail aktivitas · {{ $nama }}</h2>
        <p class="dsh-crumb" style="margin:6px 0 0">
            <a href="{{ route('kaprodi.aktivitas.index', ['periode' => $periodeKey]) }}">Aktivitas Dosen</a> / {{ $nama }} · {{ $periodeLabel }}
        </p>
    </div>

    <section class="dsh-card">
        <div class="dsh-profile">
            <div class="dsh-avatar">
                @if ($dosen->foto_profil)
                    <img src="{{ asset('storage/'.$dosen->foto_profil) }}" alt="Foto {{ $nama }}">
                @else
                    {{ $inisial }}
                @endif
            </div>
            <div>
                <h3>{{ $dosen->name }}</h3>
                <p class="meta">Dosen · {{ $dosen->program_studi ?: 'Sistem Informasi' }}</p>
                @if ($dosen->deskripsi_keahlian)
                    <p class="meta">{{ \Illuminate\Support\Str::limit($dosen->deskripsi_keahlian, 140) }}</p>
                @endif

                @if ($tags->isNotEmpty())
                    <div class="dsh-tags">
                        @foreach ($tags as $tag)
                            <span class="dsh-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <p class="meta" style="margin:0">
                        Keahlian lengkap · {{ $tags->count() }} tag
                        @if ($diperbarui) · Diperbarui {{ Bimbingan::formatWaktu($diperbarui) }} @endif
                    </p>
                @else
                    <p class="meta" style="margin-top:10px">Tag keahlian belum diisi.</p>
                @endif
            </div>
        </div>
    </section>

    <div class="dsh-stats">
        <div class="dsh-stat">
            <div class="label">Konsultasi ditanggapi</div>
            <div class="value">{{ $m['konsultasi_ditanggapi'] }}</div>
            <div class="sub">{{ $m['konsultasi_menunggu'] }} masih menunggu</div>
        </div>
        <div class="dsh-stat">
            <div class="label">Bimbingan diproses</div>
            <div class="value">{{ $m['bimbingan_diproses'] }}</div>
            <div class="sub">
                {{ $m['bimbingan_setuju'] }} setuju · {{ $m['bimbingan_tolak'] }} tolak
                @if ($m['bimbingan_otomatis'] > 0) · {{ $m['bimbingan_otomatis'] }} tidak direspons @endif
            </div>
        </div>
        <div class="dsh-stat">
            <div class="label">Waktu tanggapan</div>
            <div class="value">{{ $durasi }}</div>
            <div class="sub">Rata-rata bulan ini</div>
        </div>
        <div class="dsh-stat">
            <div class="label">Tag keahlian</div>
            <div class="value">{{ $m['tag'] > 0 ? 'Lengkap' : 'Belum diisi' }}</div>
            <div class="sub">{{ $m['tag'] }} bidang keahlian</div>
        </div>
    </div>

    <section class="dsh-card">
        <h3>Aktivitas mingguan · {{ \Illuminate\Support\Str::before($periodeLabel, ' ') }}</h3>
        <div style="margin-top:16px">
            @foreach ($mingguan as $minggu)
                <div class="dsh-week">
                    <span>{{ $minggu['label'] }} · {{ $minggu['jumlah'] }} aktivitas diproses</span>
                    <div class="bar" role="img" aria-label="{{ $minggu['label'] }}: {{ $minggu['jumlah'] }} aktivitas diproses">
                        <span style="width: {{ number_format($minggu['jumlah'] / $maksMinggu * 100, 2, '.', '') }}%"></span>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="hint">
            {{ $m['konsultasi_ditanggapi'] }} konsultasi ditanggapi + {{ $m['bimbingan_diproses'] }} bimbingan diproses
            · Data hingga {{ Bimbingan::formatWaktu($dataHingga) }}
        </p>
    </section>

    <section class="dsh-card">
        <h3>Rincian aktivitas</h3>
        <div class="dsh-table-wrap" style="margin-top:16px">
            <table class="dsh-table">
                <thead>
                    <tr>
                        <th>Permintaan</th>
                        <th>Jenis</th>
                        <th>Waktu tanggapan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rincian as $baris)
                        <tr>
                            <td class="strong">{{ $baris['judul'] }}</td>
                            <td>{{ $baris['jenis'] }}</td>
                            <td>{{ $baris['waktu'] }}</td>
                            <td><span class="dsh-badge {{ $baris['badge'] }}">{{ $baris['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada aktivitas pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div>
        <a class="dsh-btn" href="{{ route('kaprodi.aktivitas.index', ['periode' => $periodeKey]) }}">Kembali ke aktivitas dosen</a>
    </div>
</div>
@endsection
