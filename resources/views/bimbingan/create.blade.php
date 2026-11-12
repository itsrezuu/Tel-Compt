@extends('layouts.mahasiswa')

@section('title', 'Ajukan Bimbingan')
@section('page_title', 'Bimbingan')

@push('styles')
    @include('bimbingan._styles')
@endpush

@section('content')
<div class="bm-page">
    @include('bimbingan._errors')

    <div class="bm-head">
        <div>
            <h2>Ajukan bimbingan formal</h2>
            <p>Sampaikan topik dan informasi tim untuk mendapatkan keputusan dosen.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('bimbingan.store') }}" class="bm-page" style="max-width:none">
        @csrf

        <section class="bm-card">
            <h3>Topik &amp; dosen pendamping</h3>

            @if (! empty($lombaOptions))
                <div class="bm-field">
                    <label class="bm-label" for="lomba_id">Lomba terkait *</label>
                    <select id="lomba_id" name="lomba_id" class="bm-select" required>
                        <option value="">Pilih lomba</option>
                        @foreach ($lombaOptions as $id => $judul)
                            <option value="{{ $id }}" @selected(old('lomba_id') == $id)>{{ $judul }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="bm-field">
                <label class="bm-label" for="dosen_id">Dosen tujuan *</label>
                <select id="dosen_id" name="dosen_id" class="bm-select" required>
                    <option value="">Pilih dosen</option>
                    @foreach ($dosenList as $dosen)
                        <option value="{{ $dosen->id }}" @selected(old('dosen_id') == $dosen->id)>{{ $dosen->name }}</option>
                    @endforeach
                </select>
                <p class="bm-hint">Pilih berdasarkan bidang keahlian, bukan rekomendasi otomatis.</p>
            </div>

            <div class="bm-field">
                <label class="bm-label" for="topik_judul">Judul / topik *</label>
                <input id="topik_judul" name="topik_judul" class="bm-input" value="{{ old('topik_judul') }}" maxlength="150" required>
            </div>

            <div class="bm-field" style="margin-bottom:0">
                <label class="bm-label" for="ringkasan_kebutuhan">Ringkasan kebutuhan bimbingan *</label>
                <textarea id="ringkasan_kebutuhan" name="ringkasan_kebutuhan" class="bm-textarea" maxlength="1500" required>{{ old('ringkasan_kebutuhan') }}</textarea>
            </div>
        </section>

        <section class="bm-card">
            <h3>Informasi tim</h3>

            <div class="bm-row bm-field">
                <div>
                    <label class="bm-label" for="nama_tim">Nama tim *</label>
                    <input id="nama_tim" name="nama_tim" class="bm-input" value="{{ old('nama_tim') }}" maxlength="100" required>
                </div>
                <div>
                    <label class="bm-label" for="ketua_tim">Ketua tim *</label>
                    <input id="ketua_tim" name="ketua_tim" class="bm-input" value="{{ old('ketua_tim', $ketuaDefault) }}" maxlength="100" required>
                </div>
            </div>

            <div class="bm-field">
                <label class="bm-label" for="anggota_tim">Anggota tim *</label>
                <textarea id="anggota_tim" name="anggota_tim" class="bm-textarea" maxlength="1000" required
                          placeholder="Satu anggota per baris, contoh: Raka Bimantara · 1301260002">{{ old('anggota_tim') }}</textarea>
            </div>

            <div class="bm-field">
                <label class="bm-label" for="rencana_kegiatan">Rencana kegiatan *</label>
                <textarea id="rencana_kegiatan" name="rencana_kegiatan" class="bm-textarea" maxlength="1500" required>{{ old('rencana_kegiatan') }}</textarea>
            </div>

            <div class="bm-actions">
                <button type="submit" class="bm-btn bm-btn-primary">Kirim permintaan bimbingan</button>
                <a href="{{ route('bimbingan.index') }}" class="bm-btn bm-btn-ghost">Batal</a>
            </div>
            <p class="bm-hint">Dosen punya waktu 1 hari untuk memutuskan. Jika tidak direspons, pengajuan ditolak otomatis dan kamu bisa mengajukan lagi.</p>
        </section>
    </form>
</div>
@endsection
