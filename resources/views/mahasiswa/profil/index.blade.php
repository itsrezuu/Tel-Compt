
@extends('layouts.mahasiswa')

@section('title', 'Profil Mahasiswa')
@section('page_title', 'Profil Mahasiswa')

@push('styles')
<style>
    .profile-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .profile-heading {
        margin-bottom: 22px;
    }

    .profile-heading h2 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 700;
        color: #1b2559;
    }

    .profile-heading p {
        margin: 0;
        color: #71809f;
        font-size: 13px;
    }

    .profile-card {
        background: #fff;
        border: 1px solid #dfe6f1;
        border-radius: 12px;
        padding: 26px;
        margin-bottom: 20px;
    }

    .identity {
        display: flex;
        align-items: center;
        gap: 18px;
        padding-bottom: 22px;
        border-bottom: 1px solid #edf0f5;
        margin-bottom: 22px;
    }

    .identity-avatar {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        background: #edf4ff;
        color: #2563eb;
        display: grid;
        place-items: center;
        font-size: 22px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .identity h3 {
        margin: 0 0 5px;
        font-size: 18px;
        color: #1b2559;
    }

    .identity p {
        margin: 0;
        color: #71809f;
        font-size: 13px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1b2559;
        margin: 0 0 18px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #34415e;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        border: 1px solid #dfe6f1;
        border-radius: 8px;
        padding: 11px 13px;
        font-size: 13px;
        color: #1b2559;
        background: #fff;
    }

    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    }

    textarea.form-control {
        min-height: 115px;
        resize: vertical;
    }

    .field-hint {
        margin-top: 6px;
        font-size: 11px;
        color: #71809f;
    }

    .interest-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .interest-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 1px solid #dfe6f1;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        color: #34415e;
    }

    .interest-option:has(input:checked) {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .interest-option input {
        accent-color: #2563eb;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
    }

    .btn-save {
        border: none;
        background: #2563eb;
        color: white;
        border-radius: 8px;
        padding: 11px 20px;
        font-weight: 600;
        font-size: 13px;
    }

    .btn-save:hover {
        background: #1d4ed8;
    }

    .error-message {
        color: #dc2626;
        font-size: 12px;
        margin-top: 6px;
    }

    @media (max-width: 600px) {
        .profile-card {
            padding: 18px;
        }

        .interest-grid {
            grid-template-columns: 1fr;
        }

        .identity {
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
@php
    $selectedProgramId = old('program_studi_id', $mahasiswa->program_studi_id);
    $selectedKampus = old(
        'kampus_filter',
        $mahasiswa->programStudi?->kampus ?? ''
    );
@endphp

<div class="profile-container">

    <div class="profile-heading">
        <h2>Profil Saya</h2>
        <p>Kelola informasi profil dan bidang minat kompetisi Anda.</p>
    </div>

    <div class="profile-card">

        <div class="identity">
            <div class="identity-avatar">
                {{ mb_strtoupper(mb_substr($mahasiswa->name, 0, 1)) }}
            </div>

            <div>
                <h3>{{ $mahasiswa->name }}</h3>
                <p>NIM: {{ $mahasiswa->nim_nip ?: 'Belum tersedia' }}</p>
                <p>
                    {{ $mahasiswa->programStudi
                        ? $mahasiswa->programStudi->nama_prodi . ' - ' . $mahasiswa->programStudi->kampus
                        : 'Program studi belum diisi' }}
                </p>
            </div>
        </div>

        <h3 class="section-title">Informasi Profil</h3>

        <form method="POST" action="{{ route('mahasiswa.profil.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $mahasiswa->name }}"
                    disabled
                >
            </div>

            <div class="form-group">
    <label for="kampus_filter" class="form-label">Kampus</label>

    <select id="kampus_filter" class="form-control">
        <option value="">Pilih Kampus</option>

        @foreach ($daftarKampus as $kampus)
            <option
                value="{{ $kampus }}"
                {{ $selectedKampus === $kampus ? 'selected' : '' }}
            >
                {{ $kampus }}
            </option>
        @endforeach
    </select>

    <p class="field-hint">
        Pilih kampus untuk melihat program studi yang tersedia.
    </p>
</div>

            <div class="form-group">
                <label for="program_studi_id" class="form-label">
                    Program Studi
                </label>

                <select
                    id="program_studi_id"
                    name="program_studi_id"
                    class="form-control"
                >
                    <option value="">Pilih Program Studi</option>

                    @foreach ($daftarProgramStudi as $prodi)
                        <option
                            value="{{ $prodi->id }}"
                            data-kampus="{{ $prodi->kampus }}"
                            {{ (string) $selectedProgramId === (string) $prodi->id ? 'selected' : '' }}
                        >
                            {{ $prodi->nama_prodi }} ({{ $prodi->jenjang }})
                        </option>
                    @endforeach
                </select>

                @error('program_studi_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="deskripsi_keahlian" class="form-label">
                    Deskripsi Minat dan Keahlian
                </label>

                <textarea
                    name="deskripsi_keahlian"
                    id="deskripsi_keahlian"
                    class="form-control"
                    maxlength="2000"
                    placeholder="Ceritakan minat, kemampuan, atau bidang kompetisi yang ingin Anda kembangkan..."
                >{{ old('deskripsi_keahlian', $mahasiswa->deskripsi_keahlian) }}</textarea>

                @error('deskripsi_keahlian')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Bidang Minat Kompetisi</label>

                <p class="field-hint" style="margin-bottom: 12px;">
                    Pilih satu atau beberapa bidang yang sesuai dengan minat Anda.
                </p>

                <div class="interest-grid">
                    @forelse ($bidangKeahlian as $bidang)
                        <label class="interest-option">
                            <input
                                type="checkbox"
                                name="bidang_keahlian[]"
                                value="{{ $bidang->bidang_keahlian_id }}"
                                {{ in_array($bidang->bidang_keahlian_id, old('bidang_keahlian', $selectedBidang)) ? 'checked' : '' }}
                            >
                            <span>{{ $bidang->nama_bidang }}</span>
                        </label>
                    @empty
                        <p>Belum ada pilihan bidang minat tersedia.</p>
                    @endforelse
                </div>

                @error('bidang_keahlian')
                    <div class="error-message">{{ $message }}</div>
                @enderror

                @error('bidang_keahlian.*')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kampusSelect = document.getElementById('kampus_filter');
    const prodiSelect = document.getElementById('program_studi_id');

    function filterProgramStudi() {
        const selectedKampus = kampusSelect.value;

        Array.from(prodiSelect.options).forEach(function (option) {
            if (!option.value) {
                return;
            }

            const sesuaiKampus = option.dataset.kampus === selectedKampus;

            option.hidden = !sesuaiKampus;
            option.disabled = !sesuaiKampus;
        });

        const pilihan = prodiSelect.selectedOptions[0];

        if (
            pilihan &&
            pilihan.value &&
            pilihan.dataset.kampus !== selectedKampus
        ) {
            prodiSelect.value = '';
        }
    }

    kampusSelect.addEventListener('change', function () {
        prodiSelect.value = '';
        filterProgramStudi();
    });

    filterProgramStudi();
});
</script>
@endsection
