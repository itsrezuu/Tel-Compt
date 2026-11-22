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
            <p>Kamu dapat mengajukan beberapa lomba sekaligus, satu pengajuan untuk setiap lomba.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('bimbingan.store') }}" class="bm-page bm-form">
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
            @else
                <div class="bm-banner warn" role="status">
                    <div>
                        <strong>Tidak ada lomba baru yang tersedia</strong>
                        <p>Setiap lomba yang sudah memiliki pengajuan hanya dapat diajukan ulang dari halaman detailnya jika ditolak.</p>
                    </div>
                </div>
            @endif

            @php
                $selectedDosen = collect($dosenList)->first(
                    fn ($dosen) => (string) $dosen->getKey() === (string) old('dosen_id')
                );
            @endphp
            <div class="bm-field">
                <label class="bm-label" for="dosen_id_display">Dosen tujuan *</label>
                <div class="bm-autocomplete" data-lecturer-picker>
                    <input id="dosen_id_display" type="text" class="bm-input" value="{{ $selectedDosen?->name }}"
                           placeholder="Pilih dosen" autocomplete="off" readonly role="combobox"
                           aria-haspopup="listbox" aria-expanded="false" aria-controls="dosen_options"
                           aria-required="true" aria-describedby="dosen_help" data-lecturer-trigger>
                    <div id="dosen_dropdown" class="bm-lecturer-dropdown" data-lecturer-dropdown hidden>
                        <label class="bm-label" for="dosen_search">Cari dosen</label>
                        <input id="dosen_search" type="search" class="bm-input bm-lecturer-search" autocomplete="off"
                               placeholder="Ketik minimal 3 karakter untuk memfilter daftar."
                               aria-controls="dosen_options" aria-describedby="dosen_search_hint"
                               data-lecturer-search>
                        <div id="dosen_options" class="bm-lecturer-options" role="listbox" aria-label="Daftar dosen">
                            @foreach ($dosenList as $dosen)
                                <button type="button" class="bm-lecturer-option" role="option"
                                        aria-selected="{{ (string) old('dosen_id') === (string) $dosen->getKey() ? 'true' : 'false' }}"
                                        data-lecturer-option data-value="{{ $dosen->getKey() }}">{{ $dosen->name }}</button>
                            @endforeach
                        </div>
                        <p id="dosen_search_hint" class="bm-hint" role="status" aria-live="polite">Ketik minimal 3 karakter untuk memfilter daftar dosen.</p>
                    </div>
                    <select id="dosen_id" name="dosen_id" class="bm-visually-hidden" tabindex="-1" aria-hidden="true">
                        <option value="">Pilih dosen</option>
                        @foreach ($dosenList as $dosen)
                            <option value="{{ $dosen->getKey() }}" @selected(old('dosen_id') == $dosen->getKey())>{{ $dosen->name }}</option>
                        @endforeach
                    </select>
                </div>
                <p id="dosen_help" class="bm-hint">Pilih berdasarkan bidang keahlian, bukan rekomendasi otomatis.</p>
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
                <div class="bm-autocomplete">
                    <label class="bm-label" for="ketua_tim">Ketua tim *</label>
                    <input id="ketua_tim" name="ketua_tim" class="bm-input bm-mahasiswa-search"
                           value="{{ old('ketua_tim', $ketuaDefault) }}" maxlength="100" autocomplete="off" required
                           data-mahasiswa-autocomplete data-search-url="{{ route('bimbingan.mahasiswa-search') }}">
                    <div class="bm-suggestions" role="listbox" hidden></div>
                </div>
                <div>
                    <label class="bm-label" for="nama_tim">Nama tim *</label>
                    <input id="nama_tim" name="nama_tim" class="bm-input" value="{{ old('nama_tim') }}" maxlength="100" required>
                </div>
            </div>

            <div class="bm-field">
                <label class="bm-label" for="anggota_tim_trigger">Anggota tim *</label>
                <div class="bm-member-picker" data-member-picker>
                    <button id="anggota_tim_trigger" type="button" class="bm-input bm-member-trigger"
                            aria-expanded="false" aria-controls="anggota_tim_panel" aria-describedby="anggota_tim_help"
                            aria-haspopup="listbox" data-member-trigger>
                        <span class="bm-member-trigger-label" data-member-trigger-label>Pilih anggota tim</span>
                        <span class="bm-member-trigger-count" data-member-trigger-count>{{ $anggotaTerpilih->count() }} dipilih</span>
                        <span class="bm-member-trigger-icon" aria-hidden="true">⌄</span>
                    </button>
                    <div id="anggota_tim_panel" class="bm-member-panel" data-member-panel hidden>
                        <label class="bm-label" for="anggota_tim_search">Cari mahasiswa</label>
                        <input id="anggota_tim_search" type="search" class="bm-input bm-member-search"
                               autocomplete="off" placeholder="Ketik minimal 3 karakter untuk mencari mahasiswa."
                               aria-controls="anggota_tim_options" aria-describedby="anggota_tim_status"
                               data-search-url="{{ route('bimbingan.mahasiswa-search') }}" data-member-search>
                        <div id="anggota_tim_options" class="bm-member-options" role="listbox"
                             aria-label="Hasil mahasiswa" data-member-options hidden></div>
                        <p id="anggota_tim_status" class="bm-hint bm-member-status" role="status" aria-live="polite" data-member-status>
                            Ketik minimal 3 karakter untuk mencari mahasiswa.
                        </p>
                    </div>
                    <div class="bm-member-selected" data-member-selected @if ($anggotaTerpilih->isEmpty()) hidden @endif>
                        <div class="bm-member-selected-head">
                            <strong>Anggota terpilih</strong>
                            <span class="bm-member-selected-count" data-member-selected-count>{{ $anggotaTerpilih->count() }} anggota dipilih</span>
                        </div>
                        <div class="bm-member-list" data-member-list aria-label="Anggota tim terpilih">
                            @foreach ($anggotaTerpilih as $anggota)
                                <div class="bm-member-chip" data-member-chip>
                                    <input type="hidden" name="anggota_tim_ids[]" value="{{ $anggota->getKey() }}"
                                           data-member-id="{{ $anggota->getKey() }}"
                                           data-member-label="{{ $anggota->name.($anggota->nim_nip ? ' · '.$anggota->nim_nip : '') }}">
                                    <span class="bm-member-chip-main">
                                        <span class="bm-member-chip-label">{{ $anggota->name }}</span>
                                        @if ($anggota->nim_nip)
                                            <span class="bm-member-chip-nim">NIM {{ $anggota->nim_nip }}</span>
                                        @endif
                                    </span>
                                    <button type="button" class="bm-member-chip-remove" data-member-remove
                                            aria-label="Hapus {{ $anggota->name }} dari anggota tim" title="Hapus anggota">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <p class="bm-hint" id="anggota_tim_help">Pilih satu nama per anggota. Anggota yang sama tidak dapat dipilih dua kali. Batas informasi anggota tim adalah 1000 karakter.</p>
                    <p class="bm-hint bm-member-error" data-member-error role="alert" hidden></p>
                </div>
            </div>

            <div class="bm-field">
                <label class="bm-label" for="rencana_kegiatan">Rencana kegiatan *</label>
                <textarea id="rencana_kegiatan" name="rencana_kegiatan" class="bm-textarea" maxlength="1500" required>{{ old('rencana_kegiatan') }}</textarea>
            </div>

            <div class="bm-actions">
                <button type="submit" class="bm-btn bm-btn-primary" @disabled(empty($lombaOptions))>Kirim permintaan bimbingan</button>
                <a href="{{ route('bimbingan.index') }}" class="bm-btn bm-btn-ghost">Batal</a>
            </div>
            <p class="bm-hint">Dosen punya waktu 1 hari untuk memutuskan. Jika tidak direspons, pengajuan ditolak otomatis dan kamu dapat mengajukan ulang pengajuan yang sama.</p>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-mahasiswa-autocomplete]').forEach((field) => {
        const suggestions = field.parentElement.querySelector('.bm-suggestions');
        let timer;
        let requestNumber = 0;

        function hideSuggestions() {
            suggestions.replaceChildren();
            suggestions.hidden = true;
        }

        field.addEventListener('input', () => {
            const term = field.value.trim();
            const currentRequest = ++requestNumber;

            window.clearTimeout(timer);

            if (term.length < 3) {
                hideSuggestions();
                return;
            }

            timer = window.setTimeout(async () => {
                try {
                    const url = field.dataset.searchUrl + '?q=' + encodeURIComponent(term);
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });

                    if (!response.ok || currentRequest !== requestNumber) {
                        return;
                    }

                    const matches = await response.json();
                    suggestions.replaceChildren();

                    matches.forEach((student) => {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.className = 'bm-suggestion';
                        option.setAttribute('role', 'option');
                        option.textContent = student.nim_nip
                            ? student.name + ' · ' + student.nim_nip
                            : student.name;
                        option.addEventListener('click', () => {
                            field.value = option.textContent.trim();
                            field.dispatchEvent(new Event('change', { bubbles: true }));
                            hideSuggestions();
                        });
                        suggestions.appendChild(option);
                    });

                    suggestions.hidden = matches.length === 0;
                } catch {
                    hideSuggestions();
                }
            }, 200);
        });

        field.addEventListener('blur', () => {
            window.setTimeout(hideSuggestions, 150);
        });
    });

    const memberPicker = document.querySelector('[data-member-picker]');

    if (memberPicker) {
        const trigger = memberPicker.querySelector('[data-member-trigger]');
        const triggerLabel = memberPicker.querySelector('[data-member-trigger-label]');
        const triggerCount = memberPicker.querySelector('[data-member-trigger-count]');
        const panel = memberPicker.querySelector('[data-member-panel]');
        const search = memberPicker.querySelector('[data-member-search]');
        const options = memberPicker.querySelector('[data-member-options]');
        const status = memberPicker.querySelector('[data-member-status]');
        const list = memberPicker.querySelector('[data-member-list]');
        const selectedCount = memberPicker.querySelector('[data-member-selected-count]');
        const selected = memberPicker.querySelector('[data-member-selected]');
        const error = memberPicker.querySelector('[data-member-error]');
        let timer;
        let requestNumber = 0;
        let suppressFocusOpen = false;

        function memberInputs() {
            return Array.from(memberPicker.querySelectorAll('input[type="hidden"][name="anggota_tim_ids[]"][data-member-id]'));
        }

        function updateSelection() {
            const inputs = memberInputs();
            const count = inputs.length;
            const message = count === 1 ? '1 anggota dipilih' : count + ' anggota dipilih';
            triggerCount.textContent = count + ' dipilih';
            triggerLabel.textContent = count > 0 ? 'Tambah anggota tim' : 'Pilih anggota tim';
            selectedCount.textContent = message;
            selected.hidden = count === 0;

            if (count > 0) {
                error.hidden = true;
                trigger.removeAttribute('aria-invalid');
            }
        }

        function setOpen(open, focusSearch = false) {
            panel.hidden = !open;
            trigger.setAttribute('aria-expanded', String(open));

            if (open && focusSearch) {
                search.focus();
            }
        }

        function closePicker(returnFocus = false) {
            setOpen(false);

            if (returnFocus) {
                suppressFocusOpen = true;
                trigger.focus();
                if (suppressFocusOpen) {
                    suppressFocusOpen = false;
                }
                setOpen(false);
            }
        }

        function setStatus(message) {
            status.textContent = message;
        }

        function clearOptions() {
            options.replaceChildren();
            options.hidden = true;
            options.setAttribute('aria-busy', 'false');
        }

        function renderOptions(students) {
            options.replaceChildren();

            students.forEach((student) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'bm-member-option';
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.dataset.memberOption = '';
                option.dataset.studentId = String(student.id);

                const name = document.createElement('span');
                name.className = 'bm-member-option-name';
                name.textContent = student.name;
                option.appendChild(name);

                if (student.nim_nip) {
                    const nim = document.createElement('span');
                    nim.className = 'bm-member-option-nim';
                    nim.textContent = 'NIM ' + student.nim_nip;
                    option.appendChild(nim);
                }

                options.appendChild(option);
            });

            options.hidden = students.length === 0;
        }

        function memberLabel(student) {
            return student.nim_nip
                ? student.name + ' · ' + student.nim_nip
                : student.name;
        }

        function selectedInformationLength(labels) {
            return Array.from(labels.join('\n')).length;
        }

        function selectStudent(student) {
            const id = String(student.id);
            const inputs = memberInputs();

            if (inputs.some((input) => input.dataset.memberId === id)) {
                return;
            }

            const labels = inputs.map((input) => input.dataset.memberLabel);
            const label = memberLabel(student);

            if (selectedInformationLength([...labels, label]) > 1000) {
                error.textContent = 'Informasi anggota tim tidak boleh lebih dari 1000 karakter.';
                error.hidden = false;
                return;
            }

            const chip = document.createElement('div');
            chip.className = 'bm-member-chip';
            chip.dataset.memberChip = '';

            const selected = document.createElement('input');
            selected.type = 'hidden';
            selected.name = 'anggota_tim_ids[]';
            selected.value = id;
            selected.dataset.memberId = id;
            selected.dataset.memberLabel = label;

            const main = document.createElement('span');
            main.className = 'bm-member-chip-main';

            const name = document.createElement('span');
            name.className = 'bm-member-chip-label';
            name.textContent = student.name;
            main.appendChild(name);

            if (student.nim_nip) {
                const nim = document.createElement('span');
                nim.className = 'bm-member-chip-nim';
                nim.textContent = 'NIM ' + student.nim_nip;
                main.appendChild(nim);
            }

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'bm-member-chip-remove';
            remove.dataset.memberRemove = '';
            remove.setAttribute('aria-label', 'Hapus ' + student.name + ' dari anggota tim');
            remove.title = 'Hapus anggota';

            const removeIcon = document.createElement('span');
            removeIcon.setAttribute('aria-hidden', 'true');
            removeIcon.textContent = '×';
            remove.appendChild(removeIcon);

            chip.append(selected, main, remove);
            list.appendChild(chip);
            updateSelection();
            search.value = '';
            clearOptions();
            setStatus('Mahasiswa ditambahkan. Kamu dapat mencari anggota berikutnya.');
            search.focus();
        }

        function searchStudents() {
            const term = search.value.trim();
            const currentRequest = ++requestNumber;

            window.clearTimeout(timer);
            clearOptions();

            if (Array.from(term).length < 3) {
                setStatus('Ketik minimal 3 karakter untuk mencari mahasiswa.');
                return;
            }

            setStatus('Mencari mahasiswa...');
            options.setAttribute('aria-busy', 'true');

            timer = window.setTimeout(async () => {
                try {
                    const response = await fetch(search.dataset.searchUrl + '?q=' + encodeURIComponent(term), {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('Pencarian gagal.');
                    }

                    const students = await response.json();

                    if (currentRequest !== requestNumber) {
                        return;
                    }

                    const selectedIds = new Set(memberInputs().map((input) => input.dataset.memberId));
                    const availableStudents = students.filter((student) => !selectedIds.has(String(student.id)));
                    renderOptions(availableStudents);
                    options.setAttribute('aria-busy', 'false');

                    if (availableStudents.length > 0) {
                        setStatus(availableStudents.length + ' mahasiswa ditemukan. Pilih nama untuk menambahkannya.');
                    } else if (students.length > 0) {
                        setStatus('Semua mahasiswa yang cocok sudah dipilih.');
                    } else {
                        setStatus('Mahasiswa tidak ditemukan. Coba kata kunci atau NIM lain.');
                    }
                } catch {
                    if (currentRequest === requestNumber) {
                        clearOptions();
                        setStatus('Pencarian gagal. Coba lagi sebentar.');
                    }
                }
            }, 200);
        }

        trigger.addEventListener('focus', () => {
            if (suppressFocusOpen) {
                suppressFocusOpen = false;
                return;
            }

            setOpen(true);
        });

        trigger.addEventListener('click', () => setOpen(true, true));
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) {
                event.preventDefault();
                closePicker();
            } else if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                setOpen(true, true);
            }
        });

        search.addEventListener('input', searchStudents);
        search.addEventListener('keydown', (event) => {
            const availableOptions = Array.from(options.querySelectorAll('[data-member-option]'));

            if (event.key === 'Escape') {
                event.preventDefault();
                closePicker(true);
            } else if (event.key === 'ArrowDown' && availableOptions.length > 0) {
                event.preventDefault();
                availableOptions[0].focus();
            } else if (event.key === 'Enter' && availableOptions.length > 0) {
                event.preventDefault();
                if (availableOptions.length === 1) {
                    availableOptions[0].click();
                } else {
                    availableOptions[0].focus();
                }
            }
        });

        options.addEventListener('click', (event) => {
            const option = event.target.closest('[data-member-option]');

            if (!option) {
                return;
            }

            const student = {
                id: option.dataset.studentId,
                name: option.querySelector('.bm-member-option-name').textContent,
                nim_nip: option.querySelector('.bm-member-option-nim')?.textContent.replace(/^NIM\s+/, '') || null,
            };

            selectStudent(student);
        });

        options.addEventListener('keydown', (event) => {
            const option = event.target.closest('[data-member-option]');

            if (!option) {
                return;
            }

            const availableOptions = Array.from(options.querySelectorAll('[data-member-option]'));
            const currentIndex = availableOptions.indexOf(option);

            if (event.key === 'Escape') {
                event.preventDefault();
                closePicker(true);
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                const nextIndex = currentIndex + direction;

                if (nextIndex >= 0 && nextIndex < availableOptions.length) {
                    availableOptions[nextIndex].focus();
                } else if (event.key === 'ArrowUp') {
                    search.focus();
                } else {
                    availableOptions[0]?.focus();
                }
            }
        });

        list.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-member-remove]');

            if (!remove) {
                return;
            }

            const chip = remove.closest('[data-member-chip]');
            const nextChip = chip.nextElementSibling?.querySelector('[data-member-remove]')
                || chip.previousElementSibling?.querySelector('[data-member-remove]');
            chip.remove();
            updateSelection();

            if (search.value.trim().length >= 3 && !panel.hidden) {
                searchStudents();
                search.focus();
            } else if (nextChip) {
                nextChip.focus();
            } else {
                trigger.focus();
            }
        });

        memberPicker.addEventListener('focusout', () => {
            window.setTimeout(() => {
                if (!memberPicker.contains(document.activeElement)) {
                    closePicker();
                }
            }, 0);
        });

        document.addEventListener('click', (event) => {
            if (!memberPicker.contains(event.target)) {
                closePicker();
            }
        });

        trigger.form?.addEventListener('submit', (event) => {
            const labels = memberInputs().map((input) => input.dataset.memberLabel);

            if (labels.length === 0) {
                event.preventDefault();
                error.textContent = 'Pilih minimal satu anggota tim sebelum mengirim pengajuan.';
                error.hidden = false;
                trigger.setAttribute('aria-invalid', 'true');
                setOpen(true, true);
            } else if (selectedInformationLength(labels) > 1000) {
                event.preventDefault();
                error.textContent = 'Informasi anggota tim tidak boleh lebih dari 1000 karakter.';
                error.hidden = false;
                trigger.setAttribute('aria-invalid', 'true');
                setOpen(true, true);
            }
        });

        updateSelection();
    }
</script>
<script>
    const lecturerPicker = document.querySelector('[data-lecturer-picker]');

    if (lecturerPicker) {
        const trigger = lecturerPicker.querySelector('[data-lecturer-trigger]');
        const dropdown = lecturerPicker.querySelector('[data-lecturer-dropdown]');
        const search = lecturerPicker.querySelector('[data-lecturer-search]');
        const lecturerSelect = lecturerPicker.querySelector('#dosen_id');
        const optionsList = lecturerPicker.querySelector('#dosen_options');
        const status = lecturerPicker.querySelector('#dosen_search_hint');
        const lecturerOptions = Array.from(optionsList.querySelectorAll('[data-lecturer-option]'));
        let suppressFocusOpen = false;

        function setOpen(open, focusSearch = false) {
            dropdown.hidden = !open;
            trigger.setAttribute('aria-expanded', String(open));

            if (open) {
                filterOptions();
                if (focusSearch) search.focus();
            }
        }

        function filterOptions() {
            const term = search.value.trim();
            const shouldFilter = term.length >= 3;
            const normalizedTerm = term.toLocaleLowerCase();
            let visibleCount = 0;

            lecturerOptions.forEach((option) => {
                const matches = !shouldFilter || option.textContent.trim().toLocaleLowerCase().includes(normalizedTerm);
                option.hidden = !matches;
                visibleCount += matches ? 1 : 0;
            });

            status.textContent = !shouldFilter
                ? 'Ketik minimal 3 karakter untuk memfilter daftar dosen.'
                : visibleCount === 0
                    ? 'Dosen tidak ditemukan.'
                    : `${visibleCount} dosen ditemukan. Pilih nama dari daftar.`;
        }

        function closePicker(returnFocus = false) {
            setOpen(false);

            if (returnFocus) {
                suppressFocusOpen = true;
                trigger.focus();
                if (suppressFocusOpen) suppressFocusOpen = false;
                setOpen(false);
            }
        }

        function chooseLecturer(option) {
            lecturerSelect.value = option.dataset.value;
            trigger.value = option.textContent.trim();
            trigger.removeAttribute('aria-invalid');
            lecturerOptions.forEach((item) => item.setAttribute('aria-selected', String(item === option)));
            search.value = '';
            filterOptions();
            closePicker(true);
        }

        trigger.addEventListener('focus', () => {
            if (suppressFocusOpen) {
                suppressFocusOpen = false;
                return;
            }
            setOpen(true);
        });

        trigger.addEventListener('click', () => {
            setOpen(true, true);
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !dropdown.hidden) {
                event.preventDefault();
                closePicker();
            } else if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
                event.preventDefault();
                setOpen(true, true);
            }
        });

        search.addEventListener('input', filterOptions);
        search.addEventListener('keydown', (event) => {
            const visibleOptions = lecturerOptions.filter((option) => !option.hidden);

            if (event.key === 'Escape') {
                event.preventDefault();
                closePicker(true);
            } else if (event.key === 'ArrowDown' && visibleOptions.length > 0) {
                event.preventDefault();
                visibleOptions[0].focus();
            }
        });

        lecturerOptions.forEach((option) => {
            option.addEventListener('click', () => chooseLecturer(option));
            option.addEventListener('keydown', (event) => {
                const visibleOptions = lecturerOptions.filter((item) => !item.hidden);
                const currentIndex = visibleOptions.indexOf(option);

                if (event.key === 'Escape') {
                    event.preventDefault();
                    closePicker(true);
                } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    const direction = event.key === 'ArrowDown' ? 1 : -1;
                    const nextIndex = (currentIndex + direction + visibleOptions.length) % visibleOptions.length;
                    visibleOptions[nextIndex]?.focus();
                }
            });
        });

        lecturerPicker.addEventListener('focusout', () => {
            window.setTimeout(() => {
                if (!lecturerPicker.contains(document.activeElement)) closePicker();
            }, 0);
        });

        document.addEventListener('click', (event) => {
            if (!lecturerPicker.contains(event.target)) closePicker();
        });

        trigger.form?.addEventListener('submit', (event) => {
            if (!lecturerSelect.value) {
                event.preventDefault();
                trigger.setAttribute('aria-invalid', 'true');
                status.textContent = 'Pilih dosen tujuan sebelum mengirim pengajuan.';
                setOpen(true, true);
            }
        });
    }
</script>
@endpush
