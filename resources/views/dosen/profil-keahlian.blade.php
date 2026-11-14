@extends('layouts.dosen')

@section('title', 'Profil Keahlian')
@section('page_title', 'Profil dan Bidang Keahlian')

@section('content')
<style>
    .profile-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .profile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .section-description {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        font-size: 13px;
        color: #111827;
        background: #fff;
        outline: none;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-hint {
        display: block;
        color: #9ca3af;
        font-size: 11px;
        margin-top: 6px;
    }

    .profile-photo-section {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .photo-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        color: #4f46e5;
        font-size: 28px;
        font-weight: 700;
    }

    .photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-preview img[hidden] {
        display: none;
    }

    .photo-upload {
        flex: 1;
        min-width: 220px;
    }

    .btn-delete-photo {
        width: fit-content;
        padding: 8px 13px;
        border: 1px solid #fecaca;
        border-radius: 6px;
        background: #fff;
        color: #dc2626;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: .2s ease;
        margin-top: 10px;
    }

    .btn-delete-photo:hover {
        background: #fef2f2;
        border-color: #dc2626;
    }

    .tag-input-wrapper {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
    }

    .tag-input-wrapper input {
        flex: 1;
    }

    .btn-add-tag {
        padding: 10px 16px;
        border: none;
        border-radius: 7px;
        background: #4f46e5;
        color: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-add-tag:hover {
        background: #4338ca;
    }

    .tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 12px;
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 7px 11px;
        border-radius: 20px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 600;
    }

    .tag-remove {
        border: none;
        background: transparent;
        color: #6366f1;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }

    .tag-remove:hover {
        color: #dc2626;
    }

    .tag-empty {
        color: #9ca3af;
        font-size: 12px;
    }

    .error-message {
        color: #dc2626;
        font-size: 12px;
        margin-top: 6px;
    }

    .alert-success {
        padding: 12px 16px;
        margin-bottom: 20px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        font-size: 13px;
    }

    .btn-save {
        padding: 12px 22px;
        border: none;
        border-radius: 7px;
        background: #4f46e5;
        color: white;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-save:hover {
        background: #4338ca;
    }

    @media (max-width: 600px) {
        .profile-card {
            padding: 17px;
        }

        .tag-input-wrapper {
            flex-direction: column;
        }

        .btn-add-tag {
            width: 100%;
        }
    }
</style>

<div class="profile-container">

    {{-- INFORMASI PROFIL --}}
    <form
        id="profileForm"
        action="{{ route('dosen.profil-keahlian.update') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <input
            type="hidden"
            name="hapus_foto_profil"
            id="hapus_foto_profil"
            value="0"
        >

        <div class="profile-card">
            <h3 class="section-title">Informasi Profil</h3>
            <p class="section-description">
                Kelola informasi dasar dan foto profil dosen.
            </p>

            <div class="form-group">
                <label for="name" class="form-label">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $profileName) }}"
                    required
                    maxlength="255"
                >

                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="program_studi" class="form-label">
                    Program Studi
                </label>

                <input
                    type="text"
                    id="program_studi"
                    name="program_studi"
                    class="form-control"
                    value="{{ old('program_studi', $studyProgram) }}"
                    required
                    maxlength="255"
                >

                @error('program_studi')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="foto_profil" class="form-label">
                    Foto Profil
                </label>

                <div class="profile-photo-section">
                    <div class="photo-preview">
                        @php
                            $initials = collect(explode(' ', trim($profileName)))
                                ->filter()
                                ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                                ->take(2)
                                ->implode('') ?: 'D';
                        @endphp

                        <span id="photoInitials" @if ($profilePhoto) hidden @endif>
                            {{ $initials }}
                        </span>

                        <img
                            id="photoPreview"
                            src="{{ $profilePhoto ? asset('storage/' . $profilePhoto) : '' }}"
                            alt="Foto profil dosen"
                            @if (!$profilePhoto) hidden @endif
                        >
                    </div>

                    <div class="photo-upload">
                        <input
                            type="file"
                            id="foto_profil"
                            name="foto_profil"
                            class="form-control"
                            accept=".jpg,.jpeg,.png"
                        >

                        <small class="form-hint">
                            Format JPG, JPEG, atau PNG. Maksimal 2 MB.
                            Kosongkan jika tidak ingin mengganti foto.
                        </small>

                        @error('foto_profil')
                            <div class="error-message">{{ $message }}</div>
                        @enderror

                        @if ($profilePhoto)
                            <button
                                type="button"
                                id="deletePhotoButton"
                                class="btn-delete-photo"
                            >
                                Hapus Foto
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- DESKRIPSI KEAHLIAN --}}
        <div class="profile-card">
            <h3 class="section-title">Deskripsi Keahlian</h3>
            <p class="section-description">
                Jelaskan bidang keahlian, pengalaman, atau fokus penelitian.
            </p>

            <div class="form-group">
                <label
                    for="deskripsi_keahlian"
                    class="form-label"
                >
                    Deskripsi Keahlian
                </label>

                <textarea
                    id="deskripsi_keahlian"
                    name="deskripsi_keahlian"
                    class="form-control"
                    required
                >{{ old('deskripsi_keahlian', $expertiseDescription) }}</textarea>

                @error('deskripsi_keahlian')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- BIDANG KEAHLIAN --}}
        <div class="profile-card">
            <h3 class="section-title">Bidang Keahlian</h3>
            <p class="section-description">
                Tambahkan bidang keahlian yang sesuai dengan kompetensi dosen.
                Bidang keahlian ini dapat digunakan mahasiswa untuk mencari dosen.
            </p>

            <div class="form-group">
                <label for="tagInput" class="form-label">
                    Tambah Bidang Keahlian
                </label>

                <div class="tag-input-wrapper">
                    <input
                        type="text"
                        id="tagInput"
                        class="form-control"
                        placeholder="Contoh: Data Mining"
                        maxlength="100"
                    >

                    <button
                        type="button"
                        id="addTagButton"
                        class="btn-add-tag"
                    >
                        + Tambah
                    </button>
                </div>

                <small class="form-hint">
                    Tekan tombol Tambah untuk memasukkan bidang keahlian.
                </small>

                <div id="tagList" class="tag-list"></div>

                <div id="tagInputs"></div>

                @error('tags')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                @if ($errors->has('tags.*'))
                    @foreach ($errors->get('tags.*') as $messages)
                        @foreach ($messages as $message)
                            <div class="error-message">{{ $message }}</div>
                        @endforeach
                    @endforeach
                @endif
            </div>
        </div>

        {{-- SIMPAN --}}
        <div style="display: flex; justify-content: flex-end; margin-bottom: 30px;">
            <button type="submit" class="btn-save">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('profileForm');

    if (!form) {
        return;
    }

    // ==========================================
    // PREVIEW FOTO PROFIL
    // ==========================================

    const photoInput = document.getElementById('foto_profil');
    const photoPreview = document.getElementById('photoPreview');
    const photoInitials = document.getElementById('photoInitials');

    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('Format foto harus JPG, JPEG, atau PNG.');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran foto maksimal 2 MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                photoPreview.src = event.target.result;
                photoPreview.hidden = false;

                if (photoInitials) {
                    photoInitials.hidden = true;
                }
            };

            reader.readAsDataURL(file);

            // Jika memilih foto baru, batalkan permintaan hapus foto.
            const deletePhotoInput = document.getElementById('hapus_foto_profil');

            if (deletePhotoInput) {
                deletePhotoInput.value = '0';
            }
        });
    }

    // ==========================================
    // HAPUS FOTO PROFIL
    // ==========================================

    const deletePhotoButton = document.getElementById('deletePhotoButton');
    const deletePhotoInput = document.getElementById('hapus_foto_profil');

    if (deletePhotoButton && deletePhotoInput) {
        deletePhotoButton.addEventListener('click', function () {
            const confirmed = confirm(
                'Apakah kamu yakin ingin menghapus foto profil? Foto akan diganti dengan inisial nama.'
            );

            if (!confirmed) {
                return;
            }

            deletePhotoInput.value = '1';

            // Gunakan requestSubmit agar validasi form tetap berjalan.
            form.requestSubmit();
        });
    }

    // ==========================================
    // KELOLA TAG BIDANG KEAHLIAN
    // ==========================================

    const tagInput = document.getElementById('tagInput');
    const addTagButton = document.getElementById('addTagButton');
    const tagList = document.getElementById('tagList');
    const tagInputs = document.getElementById('tagInputs');

    const initialTags = @json(old('tags', $selectedTags));

    let selectedTags = [];
    const existingTags = new Set();

    if (Array.isArray(initialTags)) {
        initialTags.forEach(function (tag) {
            if (typeof tag !== 'string') {
                return;
            }

            const normalizedTag = tag.trim().replace(/\s+/g, ' ');
            const comparisonTag = normalizedTag.toLocaleLowerCase('id-ID');

            if (normalizedTag !== '' && !existingTags.has(comparisonTag)) {
                existingTags.add(comparisonTag);
                selectedTags.push(normalizedTag);
            }
        });
    }

    function renderTags() {
        tagList.innerHTML = '';
        tagInputs.innerHTML = '';

        if (selectedTags.length === 0) {
            const emptyMessage = document.createElement('span');
            emptyMessage.className = 'tag-empty';
            emptyMessage.textContent = 'Belum ada bidang keahlian yang dipilih.';
            tagList.appendChild(emptyMessage);
        }

        selectedTags.forEach(function (tag, index) {
            const tagElement = document.createElement('span');
            tagElement.className = 'tag-item';

            const tagText = document.createElement('span');
            tagText.textContent = tag;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'tag-remove';
            removeButton.textContent = '×';
            removeButton.setAttribute('aria-label', 'Hapus ' + tag);

            removeButton.addEventListener('click', function () {
                selectedTags.splice(index, 1);
                renderTags();
            });

            tagElement.appendChild(tagText);
            tagElement.appendChild(removeButton);
            tagList.appendChild(tagElement);

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'tags[]';
            hiddenInput.value = tag;

            tagInputs.appendChild(hiddenInput);
        });
    }

    function addTag() {
        const value = tagInput.value.trim().replace(/\s+/g, ' ');

        if (value === '') {
            alert('Bidang keahlian tidak boleh kosong.');
            tagInput.focus();
            return;
        }

        if (value.length > 100) {
            alert('Nama bidang keahlian maksimal 100 karakter.');
            tagInput.focus();
            return;
        }

        const duplicate = selectedTags.some(function (tag) {
            return tag.toLocaleLowerCase('id-ID') === value.toLocaleLowerCase('id-ID');
        });

        if (duplicate) {
            alert('Bidang keahlian tersebut sudah ditambahkan.');
            tagInput.focus();
            return;
        }

        selectedTags.push(value);

        tagInput.value = '';
        tagInput.focus();

        renderTags();
    }

    addTagButton.addEventListener('click', addTag);

    tagInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addTag();
        }
    });

    renderTags();
});
</script>
@endpush
