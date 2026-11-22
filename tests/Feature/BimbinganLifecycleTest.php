<?php

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\Lomba;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function bimbinganLifecycleUser(string $role, ?string $name = null): User
{
    $user = User::factory()->create();

    $user->forceFill([
        'role' => $role,
        'name' => $name ?? $user->name,
    ])->save();

    return $user->refresh();
}

function bimbinganLifecycleLomba(string $title): Lomba
{
    return Lomba::create([
        'judul_lomba' => $title,
        'penyelenggara' => 'Penyelenggara Demo',
        'kategori' => 'Teknologi',
        'tingkat' => 'Nasional',
        'pendaftaran_dibuka' => now()->subDay()->toDateString(),
        'tenggat_pendaftaran' => now()->addMonth()->toDateString(),
        'link_pendaftaran' => 'https://example.test/daftar',
        'jenis_pendampingan' => 'pembimbing resmi wajib',
        'status_publikasi' => 'dipublikasikan',
    ]);
}

function bimbinganLifecycleCreate(
    User $student,
    User $lecturer,
    string $status,
    ?int $lombaId = null,
): Bimbingan {
    return Bimbingan::create([
        'mahasiswa_id' => $student->getKey(),
        'dosen_id' => $lecturer->getKey(),
        'lomba_id' => $lombaId,
        'topik_judul' => 'Topik bimbingan contoh',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan untuk menyusun proposal.',
        'nama_tim' => 'Tim Contoh',
        'ketua_tim' => $student->name,
        'anggota_tim' => 'Anggota 1',
        'rencana_kegiatan' => 'Menyusun proposal dan jadwal latihan.',
        'status' => $status,
        'catatan_dosen' => $status === Bimbingan::DITOLAK ? 'Perbaiki informasi pengajuan.' : null,
        'batas_respons_at' => now()->addDay(),
        'responded_at' => $status === Bimbingan::MENUNGGU ? null : now(),
    ]);
}

function bimbinganLifecyclePayload(User $student, User $lecturer, int $lombaId, ?array $anggotaIds = null): array
{
    return [
        'lomba_id' => $lombaId,
        'dosen_id' => $lecturer->getKey(),
        'topik_judul' => 'Topik baru',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan untuk rencana baru.',
        'nama_tim' => 'Tim Contoh Berikutnya',
        'ketua_tim' => 'Mahasiswa Contoh',
        'anggota_tim_ids' => $anggotaIds ?? [$student->getKey()],
        'rencana_kegiatan' => 'Menyusun rencana kegiatan baru.',
    ];
}

it('allows a student to apply to different competitions with the same lecturer and blocks a duplicate pair', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $firstCompetition = bimbinganLifecycleLomba('Kompetisi A');
    $secondCompetition = bimbinganLifecycleLomba('Kompetisi B');

    $this->actingAs($student)
        ->post(route('bimbingan.store'), bimbinganLifecyclePayload($student, $lecturer, (int) $firstCompetition->getKey()))
        ->assertRedirect(route('bimbingan.index'));

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload($student, $lecturer, (int) $secondCompetition->getKey()))
        ->assertRedirect(route('bimbingan.index'));

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload($student, $lecturer, (int) $firstCompetition->getKey()))
        ->assertSessionHasErrors([
            'lomba_id' => 'Kamu sudah memiliki pengajuan untuk lomba ini. Pengajuan yang ditolak dapat diajukan ulang dari halaman detailnya.',
        ]);

    $this->assertDatabaseCount('bimbingan', 2);
    expect(Bimbingan::query()->where('mahasiswa_id', $student->getKey())->where('dosen_id', $lecturer->getKey())->count())->toBe(2);
});

it('resubmits the same rejected application to another lecturer', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $oldLecturer = bimbinganLifecycleUser('dosen');
    $newLecturer = bimbinganLifecycleUser('kaprodi');
    $competition = bimbinganLifecycleLomba('Kompetisi Ulang');
    $application = bimbinganLifecycleCreate($student, $oldLecturer, Bimbingan::DITOLAK, (int) $competition->getKey());

    $this->actingAs($student)
        ->get(route('bimbingan.show', $application))
        ->assertOk()
        ->assertSee('Ajukan ulang pengajuan yang sama');

    $this->patch(route('bimbingan.ajukan-ulang', $application), [
        'dosen_id' => $newLecturer->getKey(),
    ])->assertRedirect(route('bimbingan.show', $application))
        ->assertSessionHas('success', 'Pengajuan yang sama berhasil diajukan ulang kepada dosen pilihanmu.');

    $updated = $application->fresh();

    expect((int) $updated->getKey())->toBe((int) $application->getKey());
    expect((int) $updated->dosen_id)->toBe((int) $newLecturer->getKey());
    expect($updated->status)->toBe(Bimbingan::MENUNGGU);
    expect($updated->catatan_dosen)->toBeNull();
    expect($updated->responded_at)->toBeNull();
    expect($updated->batas_respons_at->isFuture())->toBeTrue();
    $this->assertDatabaseCount('bimbingan', 1);
    expect($newLecturer->notifications()->firstOrFail()->data['title'])->toBe('Permintaan bimbingan diajukan ulang');
});

it('blocks a second request for a completed competition but accepts another competition', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $completedCompetition = bimbinganLifecycleLomba('Kompetisi Selesai');
    $newCompetition = bimbinganLifecycleLomba('Kompetisi Baru');
    bimbinganLifecycleCreate($student, $lecturer, Bimbingan::SELESAI, (int) $completedCompetition->getKey());

    $this->actingAs($student)
        ->post(route('bimbingan.store'), bimbinganLifecyclePayload($student, $lecturer, (int) $completedCompetition->getKey()))
        ->assertSessionHasErrors('lomba_id');

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload($student, $lecturer, (int) $newCompetition->getKey()))
        ->assertRedirect(route('bimbingan.index'));

    $this->assertDatabaseCount('bimbingan', 2);
    expect(Bimbingan::query()->where('mahasiswa_id', $student->getKey())->where('lomba_id', $newCompetition->getKey())->value('status'))
        ->toBe(Bimbingan::MENUNGGU);
});

it('retains archived applications and allows one current application for the same competition', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi Arsip');
    $archived = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::SELESAI, (int) $competition->getKey());

    DB::table('bimbingan')
        ->where('bimbingan_id', $archived->getKey())
        ->update(['archived_at' => now()]);

    $current = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::DISETUJUI, (int) $competition->getKey());

    $this->actingAs($student)
        ->get(route('bimbingan.index'))
        ->assertOk()
        ->assertSee('Arsip riwayat')
        ->assertSee($archived->kode)
        ->assertSee($current->kode);

    $this->get(route('bimbingan.show', $archived))
        ->assertOk()
        ->assertSee('disimpan sebagai riwayat arsip')
        ->assertDontSee('Ajukan ulang pengajuan yang sama');

    $this->assertDatabaseCount('bimbingan', 2);
    expect($archived->fresh()->archived_at)->not->toBeNull();
});

it('archives duplicate completed records during migration and preserves their consultation history', function (): void {
    $migration = require database_path('migrations/2026_11_20_050341_add_archive_support_to_bimbingan_table.php');
    $migration->down();

    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi Riwayat Lama');
    $firstCompleted = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::SELESAI, (int) $competition->getKey());
    $secondCompleted = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::SELESAI, (int) $competition->getKey());
    $current = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::DISETUJUI, (int) $competition->getKey());
    $log = Konsultasi::create([
        'bimbingan_id' => $firstCompleted->getKey(),
        'mahasiswa_id' => $student->getKey(),
        'dosen_id' => $lecturer->getKey(),
        'pertanyaan' => 'Log dari pengajuan yang selesai.',
        'status' => Konsultasi::DITANGGAPI,
        'jawaban_arahan' => 'Catatan historis dosen.',
        'responded_at' => now(),
    ]);

    $migration->up();

    expect($firstCompleted->fresh()->archived_at)->not->toBeNull();
    expect($secondCompleted->fresh()->archived_at)->not->toBeNull();
    expect($current->fresh()->archived_at)->toBeNull();
    expect($log->fresh()->bimbingan_id)->toBe($firstCompleted->getKey());
    $this->assertDatabaseCount('bimbingan', 3);
    $this->assertDatabaseCount('konsultasi', 1);

    $this->expectException(QueryException::class);
    bimbinganLifecycleCreate($student, $lecturer, Bimbingan::MENUNGGU, (int) $competition->getKey());
});

it('returns student suggestions by name and NIM only after three characters', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa', 'Bagas Pramesti');
    $student->forceFill(['nim_nip' => '1301260001'])->save();
    bimbinganLifecycleUser('mahasiswa', 'Naufal Hidayat');
    bimbinganLifecycleUser('dosen', 'Bagas Lecturer');

    $expectedSuggestion = [
        'id' => $student->getKey(),
        'name' => 'Bagas Pramesti',
        'nim_nip' => '1301260001',
    ];

    $this->actingAs($student)
        ->getJson(route('bimbingan.mahasiswa-search', ['q' => 'Ba']))
        ->assertOk()
        ->assertExactJson([]);

    $this->getJson(route('bimbingan.mahasiswa-search', ['q' => 'bag']))
        ->assertOk()
        ->assertExactJson([$expectedSuggestion]);

    $this->getJson(route('bimbingan.mahasiswa-search', ['q' => '126']))
        ->assertOk()
        ->assertExactJson([$expectedSuggestion]);

    foreach (['dosen', 'kaprodi', 'admin'] as $role) {
        $this->actingAs(bimbinganLifecycleUser($role))
            ->getJson(route('bimbingan.mahasiswa-search', ['q' => 'bag']))
            ->assertForbidden();
    }
});

it('renders the member picker and restores selected students on the create form', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $member = bimbinganLifecycleUser('mahasiswa', 'Anggota Terpilih');

    $this->actingAs($student)
        ->withSession(['_old_input' => ['anggota_tim_ids' => [$member->getKey()]]])
        ->get(route('bimbingan.create'))
        ->assertOk()
        ->assertSee('id="anggota_tim_trigger" type="button" class="bm-input bm-member-trigger"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('id="anggota_tim_panel" class="bm-member-panel" data-member-panel hidden', false)
        ->assertSee('placeholder="Ketik minimal 3 karakter untuk mencari mahasiswa."', false)
        ->assertSee('role="listbox"', false)
        ->assertSee('data-member-remove', false)
        ->assertSee('class="bm-member-chip-label"', false)
        ->assertDontSee('<textarea id="anggota_tim"', false)
        ->assertSee('data-member-search', false)
        ->assertSee('data-member-status', false)
        ->assertSee('selected.hidden = count === 0;', false)
        ->assertSee('data-search-url="'.route('bimbingan.mahasiswa-search').'"', false)
        ->assertSee('Ketik minimal 3 karakter untuk mencari mahasiswa.')
        ->assertSee('name="anggota_tim_ids[]"', false)
        ->assertSee('value="'.$member->getKey().'"', false)
        ->assertSee('Anggota Terpilih');
});

it('renders the lecturer picker closed initially and restores the selected lecturer ID', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $firstLecturer = bimbinganLifecycleUser('dosen', 'Agus Santoso, M.Kom.');
    $selectedLecturer = bimbinganLifecycleUser('kaprodi', 'Dimas Arunika, M.Kom.');

    $this->actingAs($student)
        ->get(route('bimbingan.create'))
        ->assertOk()
        ->assertSee('id="dosen_id_display"', false)
        ->assertSee('placeholder="Pilih dosen"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('id="dosen_dropdown" class="bm-lecturer-dropdown" data-lecturer-dropdown hidden', false)
        ->assertSee('name="dosen_id"', false)
        ->assertSee('Ketik minimal 3 karakter untuk memfilter daftar dosen.')
        ->assertSee('Agus Santoso, M.Kom.')
        ->assertSee("trigger.addEventListener('focus'", false)
        ->assertSee('term.length >= 3', false)
        ->assertSee('chooseLecturer(option)', false);

    $this->withSession(['_old_input' => ['dosen_id' => $selectedLecturer->getKey()]])
        ->get(route('bimbingan.create'))
        ->assertOk()
        ->assertSee('value="Dimas Arunika, M.Kom."', false)
        ->assertSee('value="'.$selectedLecturer->getKey().'" selected', false)
        ->assertSee('aria-selected="true"', false)
        ->assertSee('Dimas Arunika, M.Kom.');
});

it('persists the ID of a member returned by search', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa', 'Ketua Mahasiswa');
    $member = bimbinganLifecycleUser('mahasiswa', 'Ilham Nugraha');
    $member->forceFill(['nim_nip' => '1301260010'])->save();
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi Pilih Anggota');

    $searchResponse = $this->actingAs($student)
        ->getJson(route('bimbingan.mahasiswa-search', ['q' => '1301260010']))
        ->assertOk()
        ->assertExactJson([[
            'id' => $member->getKey(),
            'name' => 'Ilham Nugraha',
            'nim_nip' => '1301260010',
        ]]);

    $selectedMemberId = $searchResponse->json()[0]['id'];

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload(
        $student,
        $lecturer,
        (int) $competition->getKey(),
        [$selectedMemberId],
    ))->assertRedirect(route('bimbingan.index'));

    $application = Bimbingan::query()->firstOrFail();

    $this->assertDatabaseHas('bimbingan_anggota_tim', [
        'bimbingan_id' => $application->getKey(),
        'user_id' => $selectedMemberId,
    ]);
    expect($application->anggota_tim)->toBe('Ilham Nugraha · 1301260010');
});

it('persists selected student IDs and keeps their display labels', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa', 'Ketua Mahasiswa');
    $memberOne = bimbinganLifecycleUser('mahasiswa', 'Anggota Pertama');
    $memberTwo = bimbinganLifecycleUser('mahasiswa', 'Anggota Kedua');
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi Anggota');

    $this->actingAs($student)
        ->post(route('bimbingan.store'), bimbinganLifecyclePayload(
            $student,
            $lecturer,
            (int) $competition->getKey(),
            [$memberOne->getKey(), $memberTwo->getKey()],
        ))
        ->assertRedirect(route('bimbingan.index'));

    $application = Bimbingan::query()->firstOrFail();

    $this->assertDatabaseHas('bimbingan_anggota_tim', [
        'bimbingan_id' => $application->getKey(),
        'user_id' => $memberOne->getKey(),
    ]);
    $this->assertDatabaseHas('bimbingan_anggota_tim', [
        'bimbingan_id' => $application->getKey(),
        'user_id' => $memberTwo->getKey(),
    ]);
    $this->assertDatabaseCount('bimbingan_anggota_tim', 2);
    expect($application->anggota_tim)->toBe("Anggota Pertama\nAnggota Kedua");
});

it('rejects duplicate member IDs without creating an application', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $member = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi Duplikat Anggota');

    $this->actingAs($student)
        ->post(route('bimbingan.store'), bimbinganLifecyclePayload(
            $student,
            $lecturer,
            (int) $competition->getKey(),
            [$member->getKey(), $member->getKey()],
        ))
        ->assertSessionHasErrors('anggota_tim_ids.1');

    $this->assertDatabaseCount('bimbingan', 0);
    $this->assertDatabaseCount('bimbingan_anggota_tim', 0);
});

it('rejects missing and non-student member IDs without creating an application', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi ID Tidak Valid');

    $this->actingAs($student)
        ->post(route('bimbingan.store'), bimbinganLifecyclePayload(
            $student,
            $lecturer,
            (int) $competition->getKey(),
            [99999999],
        ))
        ->assertSessionHasErrors('anggota_tim_ids.0');

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload(
        $student,
        $lecturer,
        (int) $competition->getKey(),
        [$lecturer->getKey()],
    ))
        ->assertSessionHasErrors('anggota_tim_ids.0');

    $this->assertDatabaseCount('bimbingan', 0);
    $this->assertDatabaseCount('bimbingan_anggota_tim', 0);
});

it('keeps the existing 1000 character team member limit', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $competition = bimbinganLifecycleLomba('Kompetisi Batas Anggota');
    $members = collect(range(1, 4))
        ->map(fn (int $index): User => bimbinganLifecycleUser('mahasiswa', str_repeat((string) $index, 250)))
        ->map(fn (User $member): int => (int) $member->getKey())
        ->all();

    $this->actingAs($student)
        ->post(route('bimbingan.store'), bimbinganLifecyclePayload(
            $student,
            $lecturer,
            (int) $competition->getKey(),
            $members,
        ))
        ->assertSessionHasErrors('anggota_tim_ids');

    $this->assertDatabaseCount('bimbingan', 0);
    $this->assertDatabaseCount('bimbingan_anggota_tim', 0);
});

it('lets a student revise a rejected log on the same record', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $application = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::DISETUJUI);
    $log = Konsultasi::create([
        'bimbingan_id' => $application->getKey(),
        'mahasiswa_id' => $student->getKey(),
        'dosen_id' => $lecturer->getKey(),
        'pertanyaan' => 'Catatan awal.',
        'status' => Konsultasi::MENUNGGU,
    ]);

    $this->actingAs($lecturer)
        ->patch(route('dosen.konsultasi.respond', $log), [
            'aksi' => 'tolak',
            'catatan' => 'Tambahkan hasil latihan.',
        ])
        ->assertRedirect(route('dosen.konsultasi.show', $log));

    expect($log->fresh()->status)->toBe(Konsultasi::DITOLAK);

    $this->actingAs($student)
        ->get(route('bimbingan.show', $application))
        ->assertOk()
        ->assertSee('Perbaiki isi log yang sama');

    $this->patch(route('konsultasi.revise', [$application, $log]), [
        'pertanyaan' => 'Catatan diperbaiki dengan hasil latihan.',
    ])->assertRedirect(route('bimbingan.show', $application))
        ->assertSessionHas('success', 'Log yang sama berhasil diperbaiki dan dikirim ulang.');

    $updatedLog = $log->fresh();

    expect((int) $updatedLog->getKey())->toBe((int) $log->getKey());
    expect($updatedLog->pertanyaan)->toBe('Catatan diperbaiki dengan hasil latihan.');
    expect($updatedLog->status)->toBe(Konsultasi::MENUNGGU);
    expect($updatedLog->jawaban_arahan)->toBeNull();
    expect($updatedLog->responded_at)->toBeNull();
    expect($application->fresh()->status)->toBe(Bimbingan::DISETUJUI);
    $this->assertDatabaseCount('konsultasi', 1);
    expect($lecturer->notifications()->latest()->firstOrFail()->data['title'])->toBe('Log bimbingan diperbaiki');
});

it('lets only the assigned lecturer edit a log without changing its status', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $otherLecturer = bimbinganLifecycleUser('dosen');
    $application = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::DISETUJUI);
    $log = Konsultasi::create([
        'bimbingan_id' => $application->getKey(),
        'mahasiswa_id' => $student->getKey(),
        'dosen_id' => $lecturer->getKey(),
        'pertanyaan' => 'Isi log sebelum perubahan.',
        'status' => Konsultasi::MENUNGGU,
    ]);

    $this->actingAs($otherLecturer)
        ->patch(route('dosen.konsultasi.update', $log), ['pertanyaan' => 'Perubahan tanpa izin.'])
        ->assertForbidden();

    $this->actingAs($student)
        ->patch(route('dosen.konsultasi.update', $log), ['pertanyaan' => 'Perubahan oleh mahasiswa.'])
        ->assertForbidden();

    $this->actingAs($lecturer)
        ->get(route('dosen.konsultasi.show', $log))
        ->assertOk()
        ->assertSee('Simpan perubahan isi log');

    $this->patch(route('dosen.konsultasi.update', $log), [
        'pertanyaan' => 'Isi log yang diedit dosen.',
    ])->assertRedirect(route('dosen.konsultasi.show', $log))
        ->assertSessionHas('success', 'Isi log bimbingan berhasil diperbarui.');

    expect($log->fresh()->pertanyaan)->toBe('Isi log yang diedit dosen.');
    expect($log->fresh()->status)->toBe(Konsultasi::MENUNGGU);
    expect($log->fresh()->responded_at)->toBeNull();

    $this->patch(route('dosen.konsultasi.respond', $log), [
        'aksi' => 'tanggapi',
        'catatan' => 'Log sesuai.',
    ])->assertRedirect(route('dosen.konsultasi.show', $log));

    $this->patch(route('dosen.konsultasi.update', $log), [
        'pertanyaan' => 'Perubahan setelah keputusan.',
    ])->assertRedirect(route('dosen.konsultasi.show', $log))
        ->assertSessionHas('error', 'Log hanya dapat diubah selama menunggu keputusan dosen.');

    expect($log->fresh()->pertanyaan)->toBe('Isi log yang diedit dosen.');

    $this->get(route('dosen.konsultasi.show', $log))
        ->assertOk()
        ->assertDontSee('Simpan perubahan isi log');
});
