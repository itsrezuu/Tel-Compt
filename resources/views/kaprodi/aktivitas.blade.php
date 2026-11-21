@extends('layouts.kaprodi')

@section('title', 'Aktivitas Dosen')
@section('page_title', 'Aktivitas Dosen')

@push('styles')
    @include('partials.dasbor-styles')
@endpush

@section('content')
<div class="dsh-page">
    <div class="dsh-head">
        <h2>Aktivitas dosen</h2>
        <p>Pantau aktivitas pendampingan program studi Sistem Informasi.</p>
    </div>

    @include('kaprodi._filter', ['rute' => 'kaprodi.aktivitas.index'])

    @include('kaprodi._stats')

    <section class="dsh-card">
        <h3>Ringkasan per dosen</h3>
        <div class="dsh-table-wrap" style="margin-top:16px">
            <table class="dsh-table">
                <thead>
                    <tr>
                        <th>Dosen</th>
                        <th>Konsultasi / bimbingan</th>
                        <th>Waktu / keahlian</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ringkasan as $baris)
                        <tr>
                            <td class="strong">{{ $baris['nama'] }}</td>
                            <td>
                                {{ $baris['m']['konsultasi_ditanggapi'] }} ditanggapi / {{ $baris['m']['bimbingan_diproses'] }} diproses
                                @if ($baris['m']['bimbingan_otomatis'] > 0)
                                    <span style="display:block;font-size:12px;color:var(--muted)">{{ $baris['m']['bimbingan_otomatis'] }} bimbingan tidak direspons</span>
                                @endif
                            </td>
                            <td>{{ $baris['durasi'] }} / {{ $baris['keahlian'] }}</td>
                            <td>
                                <a class="dsh-link" href="{{ route('kaprodi.aktivitas.show', ['dosen' => $baris['dosen'], 'periode' => $periodeKey]) }}">Lihat aktivitas</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada dosen yang dapat ditampilkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if (count($stat['belum_lengkap']) > 0)
        <div class="dsh-alert warn" role="status">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <div>
                <strong>{{ count($stat['belum_lengkap']) }} profil keahlian belum lengkap</strong>
                <p>{{ implode(', ', $stat['belum_lengkap']) }} belum mengisi tag keahlian. Kelengkapan membantu mahasiswa memilih dosen sesuai bidang.</p>
            </div>
        </div>
    @endif

    <section class="dsh-card">
        <h3>Cara membaca aktivitas</h3>
        <p class="text">
            Konsultasi: jumlah permintaan yang mendapat tanggapan. Bimbingan: jumlah keputusan disetujui atau ditolak.
            Waktu tanggapan: selisih waktu pengajuan hingga tanggapan atau keputusan pertama.
        </p>
        <p class="hint">Angka menunjukkan aktivitas pada periode terpilih, bukan penilaian atau peringkat dosen.</p>
    </section>

    <div class="dsh-alert info">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
        <div>
            <strong>Akses fungsi dosen tersedia</strong>
            <p>Kaprodi juga dapat mengakses permintaan konsultasi, bimbingan dan profil keahlian melalui menu di samping. Layar yang sama digunakan tanpa duplikasi.</p>
        </div>
    </div>
</div>
@endsection
