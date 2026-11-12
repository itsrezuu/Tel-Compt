@extends('layouts.mahasiswa')
@use('App\Models\Bimbingan')

@section('title', 'Detail Bimbingan')
@section('page_title', 'Bimbingan')

@push('styles')
    @include('bimbingan._styles')
@endpush

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div>
        <p class="bm-crumb"><a href="{{ route('bimbingan.index') }}">Bimbingan</a> / Informasi topik dan tim</p>
        <div class="bm-head">
            <h2>Bimbingan {{ $item->kode }}@if ($item->status !== 'menunggu') · {{ ucfirst($item->status) }}@endif</h2>
            @include('bimbingan._status', ['status' => $item->status])
        </div>
    </div>

    @include('bimbingan._banner', ['status' => $item->status, 'auto' => $item->ditolak_otomatis, 'untuk' => 'mahasiswa'])

    @include('bimbingan._detail')

    <section class="bm-card">
        <h3>Keputusan &amp; arahan</h3>
        @if ($item->status === 'menunggu')
            <p class="bm-text" style="margin:0">
                Menunggu keputusan {{ $item->dosen->name }}. Batas respons: {{ Bimbingan::formatWaktu($item->batas_respons_at, true) }}
                (lewat dari itu, pengajuan ditolak otomatis).
            </p>
        @else
            <div class="bm-box">{{ $item->catatan_dosen ?: '-' }}</div>
            <p class="bm-meta">
                {{ $item->ditolak_otomatis ? 'Ditolak otomatis oleh sistem' : $item->dosen->name }}
                · {{ Bimbingan::formatWaktu($item->responded_at, true) }}
            </p>
            @if ($item->status === 'ditolak')
                <p style="margin:14px 0 0"><a href="{{ route('bimbingan.create') }}" class="bm-link">Ajukan bimbingan baru →</a></p>
            @endif
        @endif
    </section>

    @if ($item->status === 'disetujui')
        <section class="bm-card">
            <h3>Konsultasi dengan pembimbing</h3>
            <p class="bm-text">
                Tanyakan hal singkat kepada {{ $item->dosen->name }}. Konsultasi baru bisa dikirim setelah konsultasi sebelumnya dijawab.
            </p>

            @if ($bisaKonsultasi)
                <form method="POST" action="{{ route('konsultasi.store', $item) }}">
                    @csrf
                    <div class="bm-field">
                        <label class="bm-label" for="pertanyaan">Pertanyaan *</label>
                        <textarea id="pertanyaan" name="pertanyaan" class="bm-textarea" maxlength="1000" required>{{ old('pertanyaan') }}</textarea>
                        <p class="bm-hint">Jelaskan konteks dan pertanyaan utama.</p>
                    </div>
                    <button type="submit" class="bm-btn bm-btn-primary">Kirim konsultasi</button>
                </form>
            @else
                <div class="bm-banner warn">
                    <div>
                        <p style="margin:0;color:inherit">
                            Konsultasimu sebelumnya belum dijawab pembimbing. Kamu bisa mengirim konsultasi baru setelah ditanggapi atau ditolak.
                        </p>
                    </div>
                </div>
            @endif

            @if ($daftarKonsultasi->isNotEmpty())
                <div class="bm-sublist">
                    <h4>Riwayat konsultasi</h4>
                    @foreach ($daftarKonsultasi as $k)
                        <article class="bm-subitem">
                            <div class="bm-item-top">
                                <strong>{{ $k->kode }}</strong>
                                @include('bimbingan._status', ['status' => $k->status])
                            </div>
                            <p class="bm-text" style="margin:0">{{ $k->pertanyaan }}</p>
                            <p class="bm-meta" style="margin:0">Dikirim {{ Bimbingan::formatWaktu($k->created_at, true) }}</p>

                            @if ($k->status === 'menunggu')
                                <p class="bm-meta" style="margin:0">Menunggu tanggapan pembimbing.</p>
                            @else
                                <div class="bm-box {{ $k->status === 'ditolak' ? 'bad' : 'ok' }}">
                                    <strong>{{ $k->status === 'ditolak' ? 'Alasan penolakan' : 'Tanggapan dosen' }}</strong>
                                    {{ $k->jawaban_arahan }}
                                    <p class="bm-meta">{{ Bimbingan::formatWaktu($k->responded_at, true) }}</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
