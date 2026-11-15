@extends('layouts.mahasiswa')

@section('title', 'Kirim Prestasi & Arsip Pengalaman')
@section('page_title', 'Prestasi & Arsip')

@section('content')
@include('mahasiswa.prestasi._styles')

<div class="prestasi-page-head">
    <h2>Kirim prestasi &amp; arsip pengalaman</h2>
    <p>Dokumentasikan hasil, bukti dan pembelajaran untuk ditinjau HIMA.</p>
</div>

<form method="POST" action="{{ route('mahasiswa.prestasi.store') }}" class="prestasi-card" enctype="multipart/form-data">
    @csrf

    <section aria-labelledby="data-prestasi-heading">
        <h3 id="data-prestasi-heading">Data prestasi</h3>

        <div class="prestasi-field @error('lomba') has-error @enderror">
            <label for="lomba">Lomba *</label>
            <input type="text" id="lomba" name="lomba" value="{{ old('lomba') }}" maxlength="255" required>
            @error('lomba') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="prestasi-grid">
            <div class="prestasi-field @error('prestasi_hasil') has-error @enderror">
                <label for="prestasi_hasil">Prestasi / hasil *</label>
                <input type="text" id="prestasi_hasil" name="prestasi_hasil" value="{{ old('prestasi_hasil') }}" maxlength="255" required>
                @error('prestasi_hasil') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="prestasi-field @error('tanggal_pencapaian') has-error @enderror">
                <label for="tanggal_pencapaian">Tanggal pencapaian *</label>
                <input type="date" id="tanggal_pencapaian" name="tanggal_pencapaian" value="{{ old('tanggal_pencapaian') }}" required>
                @error('tanggal_pencapaian') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="prestasi-field @error('peserta_tim') has-error @enderror">
            <label for="peserta_tim">Peserta / tim *</label>
            <input type="text" id="peserta_tim" name="peserta_tim" value="{{ old('peserta_tim') }}" maxlength="255" required>
            @error('peserta_tim') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="prestasi-field @error('bukti_pendukung') has-error @enderror">
            <label for="bukti_pendukung">Bukti pendukung *</label>
            <input type="file" id="bukti_pendukung" name="bukti_pendukung" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="hint">Pilih berkas PDF, JPG atau PNG. Maks. 10 MB.</div>
            @error('bukti_pendukung') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="prestasi-field @error('keluaran_lomba') has-error @enderror">
            <label for="keluaran_lomba">Keluaran lomba *</label>
            <input type="file" id="keluaran_lomba" name="keluaran_lomba" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="hint">Pilih berkas PDF, JPG atau PNG. Maks. 10 MB.</div>
            @error('keluaran_lomba') <div class="error-text">{{ $message }}</div> @enderror
        </div>
    </section>

    <section aria-labelledby="arsip-pengalaman-heading">
        <h3 id="arsip-pengalaman-heading">Arsip pengalaman</h3>

        <div class="prestasi-field @error('judul_arsip') has-error @enderror">
            <label for="judul_arsip">Judul arsip *</label>
            <input type="text" id="judul_arsip" name="judul_arsip" value="{{ old('judul_arsip') }}" maxlength="255" required>
            @error('judul_arsip') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="prestasi-field @error('pengalaman_proses') has-error @enderror">
            <label for="pengalaman_proses">Pengalaman &amp; proses *</label>
            <textarea id="pengalaman_proses" name="pengalaman_proses" required>{{ old('pengalaman_proses') }}</textarea>
            @error('pengalaman_proses') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="prestasi-field @error('pembelajaran_saran') has-error @enderror">
            <label for="pembelajaran_saran">Pembelajaran &amp; saran *</label>
            <textarea id="pembelajaran_saran" name="pembelajaran_saran" required>{{ old('pembelajaran_saran') }}</textarea>
            @error('pembelajaran_saran') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="prestasi-field @error('dokumen_pengalaman') has-error @enderror">
            <label for="dokumen_pengalaman">Dokumen pengalaman</label>
            <input type="file" id="dokumen_pengalaman" name="dokumen_pengalaman" accept=".pdf,.jpg,.jpeg,.png">
            <div class="hint">Opsional. Pilih berkas PDF, JPG atau PNG. Maks. 10 MB.</div>
            @error('dokumen_pengalaman') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <label class="prestasi-consent">
            <input type="checkbox" name="persetujuan_publikasi" value="1" @checked(old('persetujuan_publikasi')) required>
            <span>Saya menyatakan bukti dan arsip sesuai pengajuan serta mengizinkan publikasi konten yang disetujui di Galeri Prestasi dan Basis Pengetahuan.</span>
        </label>
        @error('persetujuan_publikasi') <div class="error-text">{{ $message }}</div> @enderror

        <div class="prestasi-actions">
            <button type="submit" class="prestasi-btn primary">Kirim untuk verifikasi</button>
            <a href="{{ route('mahasiswa.prestasi.index') }}" class="prestasi-btn ghost">Batal</a>
        </div>
    </section>
</form>

@if ($pengajuanTerbaru)
    <section class="prestasi-card" aria-labelledby="keadaan-pengajuan-heading">
        <h3 id="keadaan-pengajuan-heading">Keadaan setelah dikirim</h3>
        <div class="prestasi-banner" role="status">
            <div>
                <div class="title">Dokumen diterima · {{ $pengajuanTerbaru->nomor_pengajuan }}</div>
                <div class="desc">Bukti, keluaran lomba dan pengalaman tersimpan. HIMA akan memverifikasi prestasi dan memoderasi arsip sebelum publikasi.</div>
            </div>
        </div>
        <span class="prestasi-badge">{{ $pengajuanTerbaru->status_label }}</span>
        <div class="prestasi-links">
            <a href="{{ route('mahasiswa.prestasi.index') }}" class="prestasi-btn ghost">Lihat status pengajuan</a>
        </div>
    </section>
@endif
@endsection