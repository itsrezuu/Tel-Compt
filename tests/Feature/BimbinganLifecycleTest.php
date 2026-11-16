<?php

use App\Models\Bimbingan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bimbinganLifecycleUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role])->save();

    return $user->refresh();
}

function bimbinganLifecycleCreate(User $student, User $lecturer, string $status): Bimbingan
{
    return Bimbingan::create([
        'mahasiswa_id' => $student->id,
        'dosen_id' => $lecturer->id,
        'lomba_id' => null,
        'topik_judul' => 'Topik bimbingan contoh',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan untuk menyusun proposal.',
        'nama_tim' => 'Tim Contoh',
        'ketua_tim' => $student->name,
        'anggota_tim' => 'Anggota 1',
        'rencana_kegiatan' => 'Menyusun proposal dan jadwal latihan.',
        'status' => $status,
        'batas_respons_at' => now()->addDay(),
        'responded_at' => $status === Bimbingan::MENUNGGU ? null : now(),
    ]);
}

function bimbinganLifecyclePayload(User $lecturer): array
{
    return [
        'lomba_id' => null,
        'dosen_id' => $lecturer->id,
        'topik_judul' => 'Topik baru setelah bimbingan selesai',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan untuk rencana baru.',
        'nama_tim' => 'Tim Contoh Berikutnya',
        'ketua_tim' => 'Mahasiswa Contoh',
        'anggota_tim' => 'Anggota 2',
        'rencana_kegiatan' => 'Menyusun rencana kegiatan baru.',
    ];
}

it('blocks another request only while a bimbingan is pending or approved', function (string $status, bool $blocked): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $otherLecturer = bimbinganLifecycleUser('kaprodi');
    bimbinganLifecycleCreate($student, $lecturer, $status);

    $index = $this->actingAs($student)->get(route('bimbingan.index'))->assertOk();
    $create = $this->get(route('bimbingan.create'));

    if ($blocked) {
        $index->assertSee('<span class="bm-btn bm-btn-disabled">Ajukan bimbingan</span>', false)
            ->assertSee('Kamu masih punya pengajuan Menunggu atau bimbingan Disetujui', false);
        $create->assertRedirect(route('bimbingan.index'));

        $this->post(route('bimbingan.store'), bimbinganLifecyclePayload($otherLecturer))
            ->assertSessionHasErrors('dosen_id');
        $this->assertDatabaseCount('bimbingan', 1);

        return;
    }

    $index->assertSee('<a href="'.route('bimbingan.create').'" class="bm-btn bm-btn-primary">Ajukan bimbingan</a>', false);
    $create->assertOk();

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload($otherLecturer))
        ->assertRedirect(route('bimbingan.index'));

    $this->assertDatabaseCount('bimbingan', 2);
    expect(Bimbingan::query()->latest('bimbingan_id')->firstOrFail()->status)->toBe(Bimbingan::MENUNGGU);
})->with([
    'menunggu blocks a new request' => [Bimbingan::MENUNGGU, true],
    'approved and unfinished blocks a new request' => [Bimbingan::DISETUJUI, true],
    'completed allows a new request' => [Bimbingan::SELESAI, false],
    'rejected allows a new request' => [Bimbingan::DITOLAK, false],
]);

it('lets only the assigned lecturer finish an approved bimbingan and then allows another lecturer request', function (): void {
    $student = bimbinganLifecycleUser('mahasiswa');
    $lecturer = bimbinganLifecycleUser('dosen');
    $otherLecturer = bimbinganLifecycleUser('kaprodi');
    $bimbingan = bimbinganLifecycleCreate($student, $lecturer, Bimbingan::DISETUJUI);

    $this->actingAs($lecturer)
        ->get(route('dosen.bimbingan.show', $bimbingan))
        ->assertOk()
        ->assertSee('Tandai bimbingan selesai')
        ->assertSee(route('dosen.bimbingan.selesai', $bimbingan));

    $this->actingAs($student)
        ->patch(route('dosen.bimbingan.selesai', $bimbingan))
        ->assertForbidden();
    $this->actingAs($otherLecturer)
        ->patch(route('dosen.bimbingan.selesai', $bimbingan))
        ->assertForbidden();
    expect($bimbingan->fresh()->status)->toBe(Bimbingan::DISETUJUI);

    $this->actingAs($lecturer)
        ->patch(route('dosen.bimbingan.selesai', $bimbingan))
        ->assertRedirect(route('dosen.bimbingan.show', $bimbingan))
        ->assertSessionHas('success', 'Bimbingan ditandai selesai. Mahasiswa kini dapat mengajukan bimbingan lagi.');

    expect($bimbingan->fresh()->status)->toBe(Bimbingan::SELESAI);
    expect($student->notifications()->firstOrFail()->data['title'])->toBe('Bimbingan selesai');

    $this->actingAs($lecturer)
        ->get(route('dosen.bimbingan.show', $bimbingan))
        ->assertOk()
        ->assertSee('Bimbingan selesai')
        ->assertDontSee('Tandai bimbingan selesai');

    $this->actingAs($student)
        ->get(route('bimbingan.index'))
        ->assertOk()
        ->assertSee('Selesai')
        ->assertSee('<a href="'.route('bimbingan.create').'" class="bm-btn bm-btn-primary">Ajukan bimbingan</a>', false);

    $this->get(route('bimbingan.show', $bimbingan))
        ->assertOk()
        ->assertSee('Bimbingan selesai')
        ->assertSee('Ajukan bimbingan baru');

    $this->post(route('bimbingan.store'), bimbinganLifecyclePayload($otherLecturer))
        ->assertRedirect(route('bimbingan.index'));

    $this->assertDatabaseCount('bimbingan', 2);
    expect(Bimbingan::query()->latest('bimbingan_id')->firstOrFail()->dosen_id)->toBe($otherLecturer->id);
    expect(Bimbingan::query()->latest('bimbingan_id')->firstOrFail()->status)->toBe(Bimbingan::MENUNGGU);
});
