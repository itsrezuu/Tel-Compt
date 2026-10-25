@extends('layouts.dosen')

@section('title', 'Profil & Keahlian')
@section('page_title', 'Profil & Keahlian')

@php
    // Data contoh mengikuti mockup. Data ini belum tersimpan ke database.
    $profileName = 'Dimas Arunika, M.Kom.';
    $studyProgram = 'Sistem Informasi';
    $expertiseDescription = 'Fokus pada perancangan antarmuka inklusif, evaluasi prototipe dan pengujian perangkat lunak.';
    $selectedTags = [
        'Interaksi manusia-komputer',
        'Rekayasa perangkat lunak',
        'Pengujian perangkat lunak',
    ];
    $availableTags = [
        'Interaksi manusia-komputer',
        'Rekayasa perangkat lunak',
        'Pengujian perangkat lunak',
        'Kecerdasan buatan',
        'Basis data',
        'Data mining',
        'Sistem informasi',
        'Pemrograman web',
        'UI/UX',
        'Jaringan komputer',
        'Keamanan siber',
        'Cloud computing',
        'Analisis data',
    ];
@endphp

@push('styles')
<style>
    .expertise-page { max-width: 1200px; margin: 0 auto; }
    .page-heading { margin: 0 0 22px; }
    .page-heading h2 { margin: 0; color: #1b2a59; font-size: 25px; line-height: 1.3; font-weight: 700; letter-spacing: -.35px; }
    .page-heading p { margin: 8px 0 0; color: #71809f; font-size: 13px; }

    .profile-summary {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #fff;
        border: 1px solid #dfe6f1;
        border-radius: 10px;
        padding: 22px;
        margin-bottom: 23px;
        min-height: 116px;
    }
    .profile-summary-avatar {
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        color: #2563eb;
        background: #edf4ff;
        font-size: 16px;
        font-weight: 600;
    }
    .profile-summary h3 { margin: 0; color: #202b43; font-size: 17px; line-height: 1.4; font-weight: 700; }
    .profile-summary .role { color: #71809f; margin: 4px 0 0; font-size: 12px; }
    .profile-summary .sample-note { color: #71809f; margin: 3px 0 0; font-size: 12px; }

    .profile-card {
        background: #fff;
        border: 1px solid #dfe6f1;
        border-radius: 10px;
        padding: 22px;
    }
    .profile-card h3 { margin: 0 0 17px; color: #202b43; font-size: 16px; font-weight: 700; }
    .profile-form { display: flex; flex-direction: column; gap: 16px; }
    .form-field { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
    .form-field label, .field-label { color: #202b43; font-size: 13px; font-weight: 600; }
    .form-control {
        display: block;
        width: 100%;
        min-height: 44px;
        border: 1px solid #dce4f1;
        border-radius: 6px;
        background: #fff;
        color: #27344e;
        padding: 10px 11px;
        font-size: 13px;
        line-height: 1.45;
        box-shadow: 0 1px 1px rgba(20, 35, 70, .02);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .form-control:focus { border-color: #8bb4ff; box-shadow: 0 0 0 3px rgba(37, 99, 235, .09); outline: 0; }
    textarea.form-control { min-height: 92px; resize: vertical; }

    .tag-picker-wrap { position: relative; }
    .tag-picker {
        display: flex;
        width: 100%;
        min-height: 44px;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 11px;
        border: 1px solid #dce4f1;
        border-radius: 6px;
        background: #fff;
        color: #3c4962;
        text-align: left;
        font-size: 13px;
    }
    .tag-picker:hover, .tag-picker[aria-expanded="true"] { border-color: #94b5f7; }
    .tag-picker svg { width: 15px; height: 15px; flex: 0 0 15px; stroke: currentColor; stroke-width: 1.8; fill: none; }
    .tag-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 20;
        padding: 10px;
        background: #fff;
        border: 1px solid #dce4f1;
        border-radius: 8px;
        box-shadow: 0 12px 28px rgba(22, 35, 68, .13);
    }
    .tag-dropdown[hidden] { display: none; }
    .tag-search-row { display: flex; gap: 8px; margin-bottom: 9px; }
    .tag-search-row .form-control { min-height: 38px; padding: 8px 10px; }
    .tag-add-custom {
        min-width: 66px;
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
        border-radius: 6px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .tag-add-custom:hover { background: #1d4ed8; }
    .tag-suggestions { display: flex; flex-wrap: wrap; gap: 7px; max-height: 150px; overflow-y: auto; }
    .tag-suggestion {
        border: 1px solid #dce7fb;
        color: #2563eb;
        background: #f7faff;
        border-radius: 6px;
        padding: 6px 8px;
        font-size: 12px;
        text-align: left;
    }
    .tag-suggestion:hover { background: #eaf2ff; }
    .tag-suggestion:disabled { opacity: .45; cursor: not-allowed; }
    .selected-tags { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 14px; min-height: 24px; }
    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        background: #edf4ff;
        color: #2563eb;
        border-radius: 6px;
        padding: 5px 9px;
        font-size: 11px;
        line-height: 1.35;
        font-weight: 600;
        overflow-wrap: anywhere;
    }
    .tag-chip button { padding: 0; border: 0; background: transparent; color: #2563eb; font-size: 13px; line-height: 1; font-weight: 600; }
    .tag-chip button:hover { color: #1e40af; }
    .tag-help { margin: 13px 0 0; color: #71809f; font-size: 11px; line-height: 1.5; }
    .form-actions { display: flex; align-items: center; gap: 12px; margin-top: 0; }
    .btn-primary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        border: 1px solid #2563eb;
        border-radius: 6px;
        background: #2563eb;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        transition: background .15s ease, border-color .15s ease;
    }
    .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
    .btn-primary svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .profile-status {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-top: 23px;
        padding: 15px 16px;
        border-radius: 7px;
        background: #effbf3;
        color: #15803d;
    }
    .profile-status > svg { width: 17px; height: 17px; flex: 0 0 17px; margin-top: 1px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .profile-status h4 { margin: 0; font-size: 13px; font-weight: 700; }
    .profile-status p { margin: 4px 0 0; color: #71809f; font-size: 12px; line-height: 1.6; }
    .profile-status.preview { background: #fff8e8; color: #9a6700; }

    .page-footer { color: #71809f; font-size: 11px; margin: 34px 0 0; }
    @media (max-width: 600px) {
        .page-heading h2 { font-size: 22px; }
        .profile-summary { padding: 17px; gap: 12px; }
        .profile-summary-avatar { width: 48px; height: 48px; flex-basis: 48px; }
        .profile-card { padding: 17px; }
        .profile-summary h3 { font-size: 15px; }
        .tag-dropdown { max-height: 70vh; overflow-y: auto; }
    }
</style>
@endpush

@section('content')
<div class="expertise-page">
    <header class="page-heading">
        <h2>Profil &amp; keahlian</h2>
        <p>Bantu mahasiswa memilih dosen sesuai bidang keahlian.</p>
    </header>

    <section class="profile-summary" aria-label="Ringkasan profil dosen">
        <div class="profile-summary-avatar" aria-hidden="true">DA</div>
        <div>
            <h3>{{ $profileName }}</h3>
            <p class="role">Dosen · {{ $studyProgram }}</p>
            <p class="sample-note">Data contoh fiktif</p>
        </div>
    </section>

    <section class="profile-card" aria-labelledby="profileFormTitle">
        <h3 id="profileFormTitle">Informasi profil</h3>

        <form class="profile-form" id="expertiseProfileForm" method="POST" action="{{ url('/dosen/profil-keahlian') }}">
            @csrf

            <div class="form-field">
                <label for="lecturerName">Nama</label>
                <input class="form-control" id="lecturerName" name="name" type="text" value="{{ $profileName }}" autocomplete="name" required>
            </div>

            <div class="form-field">
                <label for="studyProgram">Program studi</label>
                <input class="form-control" id="studyProgram" name="study_program" type="text" value="{{ $studyProgram }}" required>
            </div>

            <div class="form-field">
                <label for="expertiseDescription">Deskripsi bidang keahlian</label>
                <textarea class="form-control" id="expertiseDescription" name="expertise_description" rows="3" required>{{ $expertiseDescription }}</textarea>
            </div>

            <div class="form-field">
                <span class="field-label" id="tagsLabel">Tambah tag keahlian</span>
                <div class="tag-picker-wrap">
                    <button class="tag-picker" type="button" id="tagPickerButton" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="tagsLabel">
                        <span id="tagPickerText">Pilih atau tuliskan bidang keahlian</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="tag-dropdown" id="tagDropdown" hidden>
                        <div class="tag-search-row">
                            <input class="form-control" type="text" id="customTagInput" placeholder="Cari atau tuliskan tag..." aria-label="Cari atau tuliskan tag keahlian" autocomplete="off">
                            <button class="tag-add-custom" type="button" id="addCustomTagButton">Tambah</button>
                        </div>
                        <div class="tag-suggestions" id="tagSuggestions" role="listbox" aria-label="Saran tag keahlian"></div>
                    </div>
                </div>

                <div class="selected-tags" id="selectedTags" aria-label="Tag keahlian terpilih">
                    @foreach ($selectedTags as $tag)
                        <span class="tag-chip" data-tag-chip="{{ $tag }}">
                            <span>{{ $tag }}</span>
                            <button type="button" class="remove-tag" aria-label="Hapus tag {{ $tag }}" title="Hapus tag">×</button>
                            <input type="hidden" name="tags[]" value="{{ $tag }}">
                        </span>
                    @endforeach
                </div>
                <p class="tag-help">Gunakan tag yang benar-benar mewakili bidang keahlian. Tag ditampilkan pada pilihan dosen.</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                    Simpan profil
                </button>
            </div>
        </form>
    </section>

    {{-- Status hijau ini mengikuti mockup; datanya masih contoh dan belum tersimpan ke database. --}}
    <section class="profile-status" id="profileStatus" role="status" aria-live="polite">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
        <div>
            <h4 id="profileStatusTitle">Profil tersimpan</h4>
            <p id="profileStatusMessage">Deskripsi dan 3 tag keahlian berhasil diperbarui. Kelengkapan tag: lengkap.</p>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('expertiseProfileForm');
        const pickerButton = document.getElementById('tagPickerButton');
        const pickerText = document.getElementById('tagPickerText');
        const dropdown = document.getElementById('tagDropdown');
        const searchInput = document.getElementById('customTagInput');
        const addCustomButton = document.getElementById('addCustomTagButton');
        const suggestionsContainer = document.getElementById('tagSuggestions');
        const selectedTagsContainer = document.getElementById('selectedTags');
        const statusBox = document.getElementById('profileStatus');
        const statusTitle = document.getElementById('profileStatusTitle');
        const statusMessage = document.getElementById('profileStatusMessage');

        if (!form || !pickerButton || !dropdown || !selectedTagsContainer) return;

        const suggestedTags = @json($availableTags);

        function getSelectedTags() {
            return Array.from(selectedTagsContainer.querySelectorAll('input[name="tags[]"]'))
                .map((input) => input.value.trim())
                .filter(Boolean);
        }

        function renderChips() {
            selectedTagsContainer.querySelectorAll('.tag-chip').forEach((chip) => {
                const removeButton = chip.querySelector('.remove-tag');
                if (removeButton) {
                    removeButton.onclick = function () {
                        chip.remove();
                        renderSuggestions();
                        updatePickerText();
                    };
                }
            });
        }

        function renderSuggestions() {
            const selected = getSelectedTags().map((tag) => tag.toLocaleLowerCase('id-ID'));
            const query = (searchInput.value || '').trim().toLocaleLowerCase('id-ID');
            suggestionsContainer.replaceChildren();

            suggestedTags
                .filter((tag) => !selected.includes(tag.toLocaleLowerCase('id-ID')))
                .filter((tag) => !query || tag.toLocaleLowerCase('id-ID').includes(query))
                .forEach((tag) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'tag-suggestion';
                    button.setAttribute('role', 'option');
                    button.textContent = tag;
                    button.addEventListener('click', function () { addTag(tag); });
                    suggestionsContainer.appendChild(button);
                });

            if (suggestionsContainer.childElementCount === 0) {
                const emptyState = document.createElement('span');
                emptyState.style.color = '#71809f';
                emptyState.style.fontSize = '12px';
                emptyState.textContent = 'Tidak ada saran tag yang cocok. Kamu bisa menambahkan tag sendiri.';
                suggestionsContainer.appendChild(emptyState);
            }
        }

        function updatePickerText() {
            const count = getSelectedTags().length;
            pickerText.textContent = count > 0
                ? 'Tambah bidang keahlian lain'
                : 'Pilih atau tuliskan bidang keahlian';
        }

        function addTag(rawTag) {
            const tag = String(rawTag || '').trim().replace(/\s+/g, ' ');
            if (!tag) return;

            const existing = getSelectedTags().some((selected) => selected.toLocaleLowerCase('id-ID') === tag.toLocaleLowerCase('id-ID'));
            if (existing) {
                searchInput.value = '';
                renderSuggestions();
                return;
            }

            const chip = document.createElement('span');
            chip.className = 'tag-chip';
            chip.dataset.tagChip = tag;

            const label = document.createElement('span');
            label.textContent = tag;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'remove-tag';
            removeButton.setAttribute('aria-label', 'Hapus tag ' + tag);
            removeButton.title = 'Hapus tag';
            removeButton.textContent = '×';

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'tags[]';
            hiddenInput.value = tag;

            chip.append(label, removeButton, hiddenInput);
            selectedTagsContainer.appendChild(chip);
            searchInput.value = '';
            renderChips();
            renderSuggestions();
            updatePickerText();
        }

        function openDropdown(open) {
            dropdown.hidden = !open;
            pickerButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                renderSuggestions();
                searchInput.focus();
            }
        }

        pickerButton.addEventListener('click', function () {
            openDropdown(dropdown.hidden);
        });
        searchInput.addEventListener('input', renderSuggestions);
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addTag(searchInput.value);
            }
            if (event.key === 'Escape') openDropdown(false);
        });
        addCustomButton.addEventListener('click', function () { addTag(searchInput.value); });

        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target) && !pickerButton.contains(event.target)) openDropdown(false);
        });

        form.addEventListener('submit', function (event) {
            // UI mockup: tidak mengirim data ke server sampai controller dan penyimpanan dibuat.
            event.preventDefault();
            statusBox.classList.add('preview');
            statusTitle.textContent = 'Mode pratinjau';
            statusMessage.textContent = 'Perubahan ditampilkan di halaman ini saja dan belum disimpan ke database.';
            statusBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        renderChips();
        renderSuggestions();
        updatePickerText();
    })();
</script>
@endpush
