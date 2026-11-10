{{--
    Partial form lomba, dipakai bersama oleh create & edit.
    Variabel: $lomba (null saat create), $action, $method (POST|PUT), $submitLabel
--}}
@push('styles')
<style>
    .page-head h2 { font-size: 28px; font-weight: 700; margin: 0 0 6px; color: var(--ink); letter-spacing: -.2px; }
    .page-head p { margin: 0; color: var(--muted); font-size: 14px; }
    .page-head p a:hover { text-decoration: underline; }

    .info-banner { display: flex; gap: 12px; align-items: flex-start; background: #eff6ff; border-radius: 8px; padding: 16px 20px; margin: 24px 0; }
    .info-banner svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; stroke: var(--primary); stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
    .info-banner .title { color: var(--primary); font-weight: 600; font-size: 14px; }
    .info-banner .desc { color: var(--muted); font-size: 13px; margin-top: 2px; }

    .form-card { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; padding: 24px; margin-top: 24px; }
    .form-card h3 { margin: 0 0 20px; font-size: 18px; font-weight: 600; }

    .error-summary { background: #fdecec; border: 1px solid #f5c2c2; color: #a62121; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 14px; }

    .field { margin-bottom: 20px; }
    .field label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; }

    .field input[type="text"], .field input[type="url"], .field input[type="date"], .field select, .field textarea {
        width: 100%; padding: 13px 14px; border: 1px solid var(--line); border-radius: 8px;
        background: var(--surface); font: inherit; color: var(--ink);
    }
    .field textarea { min-height: 96px; resize: vertical; line-height: 1.5; }
    .field input::placeholder, .field textarea::placeholder { color: #8a94b3; }
    .field input:focus, .field select:focus, .field textarea:focus { outline: 2px solid var(--primary); outline-offset: 0; border-color: var(--primary); }

    .field select {
        appearance: none; -webkit-appearance: none; padding-right: 36px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%2364709a' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 14px center;
    }

    .field.has-error input, .field.has-error select, .field.has-error textarea { border-color: #dc2626; }
    .field .error-text { color: #b42318; font-size: 12px; margin-top: 6px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }

    .form-actions { display: flex; align-items: center; gap: 10px; margin-top: 4px; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; border: 1px solid transparent; transition: background .15s ease, border-color .15s ease; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-ghost { background: var(--surface); border-color: var(--line); color: var(--ink); }
    .btn-ghost:hover { border-color: var(--primary); }

    @media (max-width: 700px) {
        .page-head h2 { font-size: 24px; }
        .grid-2 { grid-template-columns: 1fr; }
        .form-card { padding: 18px; }
    }
</style>
@endpush

@php
    $lomba = $lomba ?? null;

    // Nilai field: old() lebih dulu, lalu data lomba (saat edit), lalu default.
    $val = fn ($f, $default = null) => old($f, $lomba?->{$f} ?? $default);
    $date = fn ($f) => old($f, $lomba?->{$f}?->format('Y-m-d'));

    $kategoriOptions = ['Teknologi', 'Desain', 'Bisnis', 'Karya tulis'];
    $tingkatOptions  = ['Nasional', 'Regional', 'Internasional', 'Internal kampus'];
    // Jika data lama berisi nilai di luar daftar, tetap tampil di dropdown
    if ($lomba?->kategori) { $kategoriOptions = array_values(array_unique(array_merge($kategoriOptions, [$lomba->kategori]))); }
    if ($lomba?->tingkat)  { $tingkatOptions  = array_values(array_unique(array_merge($tingkatOptions,  [$lomba->tingkat]))); }

    // value (disimpan ke DB) => label (tampil)
    $pendampinganOptions = [
        'tanpa pendampingan'     => 'Tanpa pendampingan',
        'konsultasi ringan'      => 'Konsultasi ringan',
        'pembimbing resmi wajib' => 'Pembimbing resmi wajib',
    ];
    $statusOptions = [
        'dipublikasikan' => 'Dipublikasikan',
        'diproses'       => 'Diproses',
        'draf'           => 'Draf',
        'pending'        => 'Menunggu',
    ];
@endphp

<form method="POST" action="{{ $action }}" class="form-card" novalidate>
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <h3>Informasi lomba</h3>

    @if ($errors->any())
        <div class="error-summary" role="alert">
            Ada {{ $errors->count() }} isian yang perlu diperbaiki. Periksa kolom yang ditandai merah.
        </div>
    @endif

    <div class="field @error('judul_lomba') has-error @enderror">
        <label for="judul_lomba">Nama lomba *</label>
        <input type="text" id="judul_lomba" name="judul_lomba" value="{{ $val('judul_lomba') }}" required maxlength="255">
        @error('judul_lomba') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="field @error('penyelenggara') has-error @enderror">
        <label for="penyelenggara">Penyelenggara *</label>
        <input type="text" id="penyelenggara" name="penyelenggara" value="{{ $val('penyelenggara') }}" required maxlength="255">
        @error('penyelenggara') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="grid-2">
        <div class="field @error('kategori') has-error @enderror">
            <label for="kategori">Kategori *</label>
            <select id="kategori" name="kategori" required>
                <option value="" disabled {{ $val('kategori') ? '' : 'selected' }}>Pilih kategori</option>
                @foreach ($kategoriOptions as $opt)
                    <option value="{{ $opt }}" @selected($val('kategori') === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
            @error('kategori') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field @error('tingkat') has-error @enderror">
            <label for="tingkat">Tingkat *</label>
            <select id="tingkat" name="tingkat" required>
                <option value="" disabled {{ $val('tingkat') ? '' : 'selected' }}>Pilih tingkat</option>
                @foreach ($tingkatOptions as $opt)
                    <option value="{{ $opt }}" @selected($val('tingkat') === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
            @error('tingkat') <div class="error-text">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="grid-2">
        <div class="field @error('pendaftaran_dibuka') has-error @enderror">
            <label for="pendaftaran_dibuka">Pendaftaran dibuka *</label>
            <input type="date" id="pendaftaran_dibuka" name="pendaftaran_dibuka" value="{{ $date('pendaftaran_dibuka') }}" required>
            @error('pendaftaran_dibuka') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field @error('tenggat_pendaftaran') has-error @enderror">
            <label for="tenggat_pendaftaran">Batas pendaftaran *</label>
            <input type="date" id="tenggat_pendaftaran" name="tenggat_pendaftaran" value="{{ $date('tenggat_pendaftaran') }}" required>
            @error('tenggat_pendaftaran') <div class="error-text">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="grid-2">
        <div class="field @error('batas_pengumpulan') has-error @enderror">
            <label for="batas_pengumpulan">Batas pengumpulan</label>
            <input type="date" id="batas_pengumpulan" name="batas_pengumpulan" value="{{ $date('batas_pengumpulan') }}">
            @error('batas_pengumpulan') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field @error('tanggal_final') has-error @enderror">
            <label for="tanggal_final">Tanggal final</label>
            <input type="date" id="tanggal_final" name="tanggal_final" value="{{ $date('tanggal_final') }}">
            @error('tanggal_final') <div class="error-text">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="field @error('jenis_pendampingan') has-error @enderror">
        <label for="jenis_pendampingan">Jenis pendampingan *</label>
        <select id="jenis_pendampingan" name="jenis_pendampingan" required>
            @foreach ($pendampinganOptions as $value => $label)
                <option value="{{ $value }}" @selected($val('jenis_pendampingan', 'konsultasi ringan') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('jenis_pendampingan') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="field @error('link_pendaftaran') has-error @enderror">
        <label for="link_pendaftaran">Tautan resmi / pendaftaran *</label>
        <input type="url" id="link_pendaftaran" name="link_pendaftaran" value="{{ $val('link_pendaftaran') }}" placeholder="https://" required>
        @error('link_pendaftaran') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="field @error('deskripsi') has-error @enderror">
        <label for="deskripsi">Deskripsi &amp; ketentuan peserta *</label>
        <textarea id="deskripsi" name="deskripsi" required>{{ $val('deskripsi') }}</textarea>
        @error('deskripsi') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="field @error('status_publikasi') has-error @enderror">
        <label for="status_publikasi">Status publikasi</label>
        <select id="status_publikasi" name="status_publikasi">
            @foreach ($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected($val('status_publikasi', 'dipublikasikan') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status_publikasi') <div class="error-text">{{ $message }}</div> @enderror
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="submitBtn">{{ $submitLabel }}</button>
        <a href="{{ route('admin.lomba.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>

@if ($method === 'POST')
    @push('scripts')
    <script>
        // Label tombol menyesuaikan status yang dipilih (khusus halaman tambah).
        (function () {
            var status = document.getElementById('status_publikasi');
            var btn = document.getElementById('submitBtn');
            var labels = {
                dipublikasikan: 'Publikasikan lomba',
                diproses: 'Simpan lomba',
                draf: 'Simpan sebagai draf',
                pending: 'Simpan lomba'
            };
            function sync() { btn.textContent = labels[status.value] || 'Simpan lomba'; }
            status.addEventListener('change', sync);
            sync();
        })();
    </script>
    @endpush
@endif