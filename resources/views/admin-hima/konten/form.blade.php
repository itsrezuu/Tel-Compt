@extends('layouts.admin-hima')
@section('title', $article->exists ? 'Edit Konten' : 'Tambah Konten')
@section('page_title', 'Kelola Konten')

@push('styles')
<style>
.kf-wrap{max-width:920px}
.kf-head h2{margin:0 0 6px;font-size:27px}
.kf-head p{margin:0 0 24px;color:var(--muted)}
.kf-card{padding:26px;border:1px solid var(--line);border-radius:11px;background:#fff}
.kf-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.kf-field{display:grid;gap:7px;margin-bottom:18px}
.kf-field.full{grid-column:1/-1}
.kf-field label{font-weight:600}
.kf-field input,.kf-field select,.kf-field textarea{width:100%;padding:12px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font:inherit}
.kf-field textarea{min-height:120px;resize:vertical}
.kf-field .large{min-height:240px}
.kf-error{color:#b42318;font-size:12px}
.kf-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding-top:6px}
.kf-actions a,.kf-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 18px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font:inherit;font-weight:600}
.kf-actions button{border-color:var(--primary);background:var(--primary);color:#fff}
.kf-actions button:hover{background:var(--primary-dark)}
@media(max-width:640px){.kf-grid{grid-template-columns:1fr}.kf-field.full{grid-column:auto}.kf-card{padding:18px}}
</style>
@endpush

@section('content')
<div class="kf-wrap">
    <header class="kf-head">
        <h2>{{ $article->exists ? 'Edit Basis Pengetahuan' : 'Tambah Basis Pengetahuan' }}</h2>
        <p>Tulis panduan dari pengalaman kompetisi dan pilih apakah konten langsung diterbitkan.</p>
    </header>

    <form class="kf-card" method="POST"
          action="{{ $article->exists ? route('admin.konten.update', $article) : route('admin.konten.store') }}">
        @csrf
        @if ($article->exists)
            @method('PUT')
        @endif

        <div class="kf-grid">
            <div class="kf-field full">
                <label for="judul">Judul konten</label>
                <input id="judul" name="judul" value="{{ old('judul', $article->judul) }}" maxlength="255" required>
                @error('judul') <span class="kf-error">{{ $message }}</span> @enderror
            </div>

            <div class="kf-field">
                <label for="kategori">Kategori / bidang</label>
                <input id="kategori" name="kategori" value="{{ old('kategori', $article->kategori) }}"
                       maxlength="100" placeholder="Contoh: Teknologi" required>
                @error('kategori') <span class="kf-error">{{ $message }}</span> @enderror
            </div>

            <div class="kf-field">
                <label for="lomba">Nama lomba (opsional)</label>
                <input id="lomba" name="lomba" value="{{ old('lomba', $article->lomba) }}" maxlength="255">
                @error('lomba') <span class="kf-error">{{ $message }}</span> @enderror
            </div>

            <div class="kf-field">
                <label for="kontributor">Kontributor (opsional)</label>
                <input id="kontributor" name="kontributor"
                       value="{{ old('kontributor', $article->kontributor) }}" maxlength="255">
                @error('kontributor') <span class="kf-error">{{ $message }}</span> @enderror
            </div>

            <div class="kf-field full">
                <label for="ringkasan">Ringkasan</label>
                <textarea id="ringkasan" name="ringkasan" maxlength="500" required>{{ old('ringkasan', $article->ringkasan) }}</textarea>
                @error('ringkasan') <span class="kf-error">{{ $message }}</span> @enderror
            </div>

            <div class="kf-field full">
                <label for="isi">Isi artikel</label>
                <textarea class="large" id="isi" name="isi" required>{{ old('isi', $article->isi) }}</textarea>
                <small>Baris baru akan ditampilkan sebagai paragraf.</small>
                @error('isi') <span class="kf-error">{{ $message }}</span> @enderror
            </div>

            <div class="kf-field">
                <label for="status">Status publikasi</label>
                <select id="status" name="status">
                    <option value="draf" @selected(old('status', $article->status ?: 'draf') === 'draf')>
                        Simpan sebagai draf
                    </option>
                    <option value="dipublikasikan" @selected(old('status', $article->status) === 'dipublikasikan')>
                        Publikasikan
                    </option>
                </select>
                @error('status') <span class="kf-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="kf-actions">
            <a href="{{ route('admin.konten.index') }}">Batal</a>
            <button type="submit">{{ $article->exists ? 'Simpan perubahan' : 'Simpan konten' }}</button>
        </div>
    </form>
</div>
@endsection
