@extends('layouts.mahasiswa')
@use('App\Models\Bimbingan')
@use('App\Models\Konsultasi')

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
            <h2>Bimbingan {{ $item->kode }} · {{ ucfirst($item->status) }}</h2>
            <div class="bm-actions">
                @if ($item->archived_at)
                    <span class="bm-badge arsip">Arsip riwayat</span>
                @endif
                @include('bimbingan._status', ['status' => $item->status])
            </div>
        </div>
    </div>

    @include('bimbingan._banner', ['status' => $item->status, 'auto' => $item->ditolak_otomatis, 'untuk' => 'mahasiswa', 'arsip' => (bool) $item->archived_at])

    @include('bimbingan._detail')

    <section class="bm-card">
        <h3>Keputusan &amp; arahan</h3>
        @if ($item->status === Bimbingan::MENUNGGU)
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
        @endif

        @if ($item->status === Bimbingan::DITOLAK && ! $item->archived_at)
            <div class="bm-sublist">
                <h4>Ajukan ulang pengajuan yang sama</h4>
                <p class="bm-text">Pilih dosen yang akan menerima pengajuan ulang. Lomba dan isi pengajuan tetap sama, dan nomor pengajuan tidak berubah.</p>
                <form method="POST" action="{{ route('bimbingan.ajukan-ulang', $item) }}">
                    @csrf
                    @method('PATCH')
                    <div class="bm-field">
                        <label class="bm-label" for="dosen_id">Dosen tujuan *</label>
                        <select id="dosen_id" name="dosen_id" class="bm-select" required>
                            <option value="">Pilih dosen</option>
                            @foreach ($dosenList as $dosen)
                                <option value="{{ $dosen->id }}" @selected(old('dosen_id', $item->dosen_id) == $dosen->id)>{{ $dosen->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bm-btn bm-btn-primary">Ajukan ulang</button>
                </form>
            </div>
        @elseif ($item->archived_at)
            <p class="bm-meta">Pengajuan ini disimpan sebagai riwayat arsip dan tidak dapat diajukan ulang.</p>
        @endif
    </section>

    @if (in_array($item->status, [Bimbingan::DISETUJUI, Bimbingan::SELESAI], true))
        <section class="bm-card">
            <h3>Log bimbingan</h3>
            @if ($item->status === Bimbingan::DISETUJUI)
                @if ($item->archived_at)
                    <p class="bm-text">Pengajuan ini merupakan arsip; riwayat log hanya dapat dilihat.</p>
                @else
                    <p class="bm-text">
                        Kirim catatan perkembangan kepada {{ $item->dosen->name }}. Jika log dikembalikan, perbaiki isi log yang sama.
                    </p>

                    @if ($bisaKonsultasi)
                    <form method="POST" action="{{ route('konsultasi.store', $item) }}">
                        @csrf
                        <div class="bm-field">
                            <label class="bm-label" for="pertanyaan">Isi log *</label>
                            <textarea id="pertanyaan" name="pertanyaan" class="bm-textarea" maxlength="1000" required>{{ old('pertanyaan') }}</textarea>
                            <p class="bm-hint">Jelaskan kegiatan, hasil, atau hal yang perlu ditinjau dosen.</p>
                        </div>
                        <button type="submit" class="bm-btn bm-btn-primary">Kirim log bimbingan</button>
                    </form>
                    @elseif ($konsultasiPerluRevisi)
                    <div class="bm-banner warn">
                        <div><p style="margin:0;color:inherit">Perbaiki log yang ditolak di bagian riwayat di bawah. Log baru belum dapat dikirim.</p></div>
                    </div>
                    @else
                    <div class="bm-banner warn">
                        <div><p style="margin:0;color:inherit">Log sebelumnya masih menunggu keputusan dosen.</p></div>
                    </div>
                    @endif
                @endif
            @else
                <p class="bm-text">
                    {{ $item->archived_at
                        ? 'Pengajuan ini merupakan arsip; riwayat log hanya dapat dilihat.'
                        : 'Bimbingan ini selesai. Riwayat log tetap dapat dilihat dan log yang dikembalikan tetap dapat diperbaiki.' }}
                </p>
            @endif

            @if ($daftarKonsultasi->isNotEmpty())
                <div class="bm-sublist">
                    <h4>Riwayat log</h4>
                    @foreach ($daftarKonsultasi as $k)
                        <article class="bm-subitem">
                            <div class="bm-item-top">
                                <strong>{{ $k->kode }}</strong>
                                @include('bimbingan._status', ['status' => $k->status])
                            </div>
                            <p class="bm-text" style="margin:0">{{ $k->pertanyaan }}</p>
                            <p class="bm-meta" style="margin:0">Dikirim {{ Bimbingan::formatWaktu($k->created_at, true) }}</p>

                            @if ($k->status === Konsultasi::MENUNGGU)
                                <p class="bm-meta" style="margin:0">Menunggu keputusan dosen.</p>
                            @else
                                <div class="bm-box {{ $k->status === Konsultasi::DITOLAK ? 'bad' : 'ok' }}">
                                    <strong>{{ $k->status === Konsultasi::DITOLAK ? 'Catatan perbaikan' : 'Catatan dosen' }}</strong>
                                    {{ $k->jawaban_arahan }}
                                    <p class="bm-meta">{{ Bimbingan::formatWaktu($k->responded_at, true) }}</p>
                                </div>
                            @endif

                            @if ($konsultasiPerluRevisi && ! $item->archived_at && (int) $k->getKey() === (int) $konsultasiPerluRevisi->getKey())
                                <form method="POST" action="{{ route('konsultasi.revise', [$item, $k]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="bm-field">
                                        <label class="bm-label" for="revisi-{{ $k->getKey() }}">Perbaiki isi log yang sama *</label>
                                        <textarea id="revisi-{{ $k->getKey() }}" name="pertanyaan" class="bm-textarea" maxlength="1000" required>{{ old('pertanyaan', $k->pertanyaan) }}</textarea>
                                    </div>
                                    <button type="submit" class="bm-btn bm-btn-primary">Perbaiki &amp; kirim ulang log</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="bm-empty">Belum ada log bimbingan.</div>
            @endif
        </section>
    @endif
</div>
@endsection
