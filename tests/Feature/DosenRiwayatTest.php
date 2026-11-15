<?php

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dosenRiwayatTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role])->save();

    return $user->refresh();
}

function dosenRiwayatTestBimbingan(
    User $mahasiswa,
    User $dosen,
    string $judul,
    string $tim,
    string $status,
    ?Carbon $respondedAt,
    ?string $catatan,
): Bimbingan {
    return Bimbingan::create([
        'mahasiswa_id' => $mahasiswa->id,
        'dosen_id' => $dosen->id,
        'lomba_id' => null,
        'topik_judul' => $judul,
        'ringkasan_kebutuhan' => 'Ringkasan kebutuhan.',
        'nama_tim' => $tim,
        'ketua_tim' => $mahasiswa->name,
        'anggota_tim' => 'Anggota 1',
        'rencana_kegiatan' => 'Rencana kegiatan.',
        'status' => $status,
        'catatan_dosen' => $catatan,
        'batas_respons_at' => ($respondedAt ?? now())->copy()->addDay(),
        'responded_at' => $respondedAt,
    ]);
}

test('lecturer history shows only completed requests assigned to the signed in lecturer and filters by type and month', function (): void {
    $this->travelTo(Carbon::parse('2026-10-15 12:00:00'));

    $lecturer = dosenRiwayatTestUser('dosen');
    $otherLecturer = dosenRiwayatTestUser('dosen');
    $student = dosenRiwayatTestUser('mahasiswa');

    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Topik Oktober',
        'Tim Oktober',
        Bimbingan::DISETUJUI,
        Carbon::parse('2026-10-10 09:00:00'),
        'Catatan keputusan Oktober.',
    );
    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Topik September',
        'Tim September',
        Bimbingan::DITOLAK,
        Carbon::parse('2026-09-08 09:00:00'),
        'Alasan penolakan September.',
    );
    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Permintaan menunggu',
        'Tim menunggu',
        Bimbingan::MENUNGGU,
        null,
        null,
    );
    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Topik tambahan 1',
        'Tim tambahan 1',
        Bimbingan::DISETUJUI,
        Carbon::parse('2026-10-01 09:00:00'),
        'Catatan tambahan 1.',
    );
    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Topik tambahan 2',
        'Tim tambahan 2',
        Bimbingan::DISETUJUI,
        Carbon::parse('2026-10-02 09:00:00'),
        'Catatan tambahan 2.',
    );
    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Topik tambahan 3',
        'Tim tambahan 3',
        Bimbingan::DISETUJUI,
        Carbon::parse('2026-10-03 09:00:00'),
        'Catatan tambahan 3.',
    );
    dosenRiwayatTestBimbingan(
        $student,
        $lecturer,
        'Topik tambahan 4',
        'Tim tambahan 4',
        Bimbingan::DISETUJUI,
        Carbon::parse('2026-10-04 09:00:00'),
        'Catatan tambahan 4.',
    );
    dosenRiwayatTestBimbingan(
        $student,
        $otherLecturer,
        'Permintaan dosen lain',
        'Tim dosen lain',
        Bimbingan::DISETUJUI,
        Carbon::parse('2026-10-09 09:00:00'),
        'Catatan milik dosen lain.',
    );

    Konsultasi::create([
        'bimbingan_id' => Bimbingan::query()->where('dosen_id', $lecturer->id)->firstOrFail()->getKey(),
        'mahasiswa_id' => $student->id,
        'dosen_id' => $lecturer->id,
        'pertanyaan' => 'Pertanyaan konsultasi Oktober',
        'jawaban_arahan' => 'Catatan tanggapan Oktober.',
        'status' => Konsultasi::DITANGGAPI,
        'responded_at' => Carbon::parse('2026-10-12 09:00:00'),
    ]);

    $this->actingAs($lecturer)
        ->get('/dosen/riwayat')
        ->assertOk()
        ->assertSee('K-001')
        ->assertSee('B-001')
        ->assertSee('dari 7 data')
        ->assertSee('Berikutnya')
        ->assertSee('Tanggapan terpilih')
        ->assertSee('Catatan tanggapan Oktober.')
        ->assertDontSee('Permintaan menunggu')
        ->assertDontSee('Permintaan dosen lain');

    $this->get('/dosen/riwayat?page=2')->assertOk()->assertSee('B-002')->assertDontSee('K-001');
    $this->get('/dosen/riwayat?jenis=konsultasi&periode=2026-10')
        ->assertOk()
        ->assertSee('K-001')
        ->assertSee('Pertanyaan konsultasi Oktober')
        ->assertDontSee('Topik Oktober')
        ->assertDontSee('Topik September');

    $this->get('/dosen/riwayat?jenis=bimbingan&periode=2026-09')
        ->assertOk()
        ->assertSee('B-002')
        ->assertSee('Topik September')
        ->assertDontSee('K-001')
        ->assertDontSee('B-001');

    $this->get('/dosen/riwayat?jenis=konsultasi&periode=2026-09')
        ->assertOk()
        ->assertSee('Belum ada permintaan yang telah ditanggapi atau diputuskan');

    $this->actingAs($student)
        ->get('/dosen/riwayat')
        ->assertForbidden();
});
