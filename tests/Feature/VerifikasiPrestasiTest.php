<?php

use App\Models\PengajuanPrestasi;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function scrum31User(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role])->save();

    return $user->refresh();
}

function scrum31Pengajuan(User $mahasiswa, array $overrides = []): PengajuanPrestasi
{
    return PengajuanPrestasi::create(array_merge([
        'mahasiswa_id' => $mahasiswa->id,
        'lomba' => 'Kompetisi Desain Aksesibel',
        'prestasi_hasil' => 'Juara 2 · Nasional',
        'tanggal_pencapaian' => '2025-11-20',
        'peserta_tim' => 'Tim Akses',
        'bukti_pendukung_path' => 'prestasi/sertifikat.pdf',
        'keluaran_lomba_path' => 'prestasi/prototype.png',
        'judul_arsip' => 'Menguji aksesibilitas prototipe',
        'pengalaman_proses' => 'Tim menguji navigasi dengan peserta.',
        'pembelajaran_saran' => 'Dokumentasikan temuan sejak awal.',
        'dokumen_pengalaman_path' => 'prestasi/catatan.pdf',
        'persetujuan_publikasi' => true,
        'status' => PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI,
    ], $overrides));
}

it('lists, searches, filters, and opens applications only for Admin HIMA', function (): void {
    $student = scrum31User('mahasiswa');
    $record = scrum31Pengajuan($student);
    $other = scrum31Pengajuan($student, ['lomba' => 'Lomba Berbeda']);

    $this->get('/admin-hima/verifikasi')->assertRedirect(route('login'));
    $this->actingAs(scrum31User('dosen'))->get('/admin-hima/verifikasi')->assertForbidden();
    $this->actingAs($student)->get('/admin-hima/verifikasi/'.$record->getKey())->assertForbidden();

    $admin = scrum31User('admin');
    $this->actingAs($admin)->get('/admin-hima/verifikasi?status=menunggu_verifikasi&q=P-001')
        ->assertOk()->assertSee($record->nomor_pengajuan)->assertDontSee($other->lomba);
    $this->get('/admin-hima/verifikasi/'.$record->getKey())->assertOk()
        ->assertSee('Prestasi &amp; bukti', false)->assertSee('Moderasi pengalaman');
    $approved = scrum31Pengajuan($student, ['status' => 'disetujui', 'status_arsip' => 'menunggu']);
    $this->get('/admin-hima/verifikasi?status=arsip_menunggu')->assertOk()->assertSee($approved->nomor_pengajuan);
});

it('serves uploaded evidence only through an authorized private route', function (): void {
    Storage::fake('local');
    $student = scrum31User('mahasiswa');
    $record = scrum31Pengajuan($student);
    Storage::disk('local')->put($record->bukti_pendukung_path, 'private evidence');

    $this->get('/admin-hima/verifikasi/'.$record->getKey().'/berkas/bukti-pendukung')->assertRedirect(route('login'));
    $this->actingAs(scrum31User('dosen'))->get('/admin-hima/verifikasi/'.$record->getKey().'/berkas/bukti-pendukung')->assertForbidden();
    $this->actingAs(scrum31User('admin'))->get('/admin-hima/verifikasi/'.$record->getKey().'/berkas/bukti-pendukung')->assertOk();
    $this->get('/admin-hima/verifikasi/'.$record->getKey().'/berkas/unknown')->assertNotFound();
});

it('approves the achievement, keeps an unapproved archive pending, and honors student consent', function (): void {
    $student = scrum31User('mahasiswa');
    $admin = scrum31User('admin');
    $record = scrum31Pengajuan($student);

    $this->actingAs($admin)->patch('/admin-hima/verifikasi/'.$record->getKey().'/setujui', [
        'bukti_sesuai' => '1', 'keluaran_lomba_tersedia' => '1', 'publikasikan_prestasi' => '1',
    ])->assertRedirect(route('admin.verifikasi.show', $record));

    expect($record->fresh()->status)->toBe('disetujui');
    expect($record->fresh()->status_arsip)->toBe('menunggu');
    expect($record->fresh()->publikasikan_prestasi)->toBeTrue();
    expect($record->fresh()->publikasikan_arsip)->toBeFalse();
    expect($student->notifications()->where('type', ActivityNotification::class)->count())->toBe(1);

    $recordTanpaConsent = scrum31Pengajuan($student, ['persetujuan_publikasi' => false]);
    $this->patch('/admin-hima/verifikasi/'.$recordTanpaConsent->getKey().'/setujui', [
        'bukti_sesuai' => '1', 'keluaran_lomba_tersedia' => '1', 'pengalaman_layak' => '1',
        'publikasikan_prestasi' => '1', 'publikasikan_arsip' => '1',
    ])->assertRedirect();
    expect($recordTanpaConsent->fresh()->publikasikan_prestasi)->toBeFalse();
    expect($recordTanpaConsent->fresh()->publikasikan_arsip)->toBeFalse();
});

it('publishes an archive only after a separate moderation approval', function (): void {
    $student = scrum31User('mahasiswa');
    $record = scrum31Pengajuan($student, ['status' => 'disetujui', 'status_arsip' => 'menunggu', 'publikasikan_prestasi' => true]);
    $admin = scrum31User('admin');

    $this->actingAs($admin)->patch('/admin-hima/verifikasi/'.$record->getKey().'/setujui', [
        'bukti_sesuai' => '1', 'keluaran_lomba_tersedia' => '1', 'pengalaman_layak' => '1', 'publikasikan_arsip' => '1',
    ])->assertRedirect();

    expect($record->fresh()->status)->toBe('disetujui');
    expect($record->fresh()->status_arsip)->toBe('disetujui');
    expect($record->fresh()->publikasikan_arsip)->toBeTrue();
    expect($record->fresh()->publikasikan_prestasi)->toBeTrue();
    expect($student->notifications()->where('type', ActivityNotification::class)->count())->toBe(1);
});

it('rejects an archive separately without reversing an approved achievement', function (): void {
    $student = scrum31User('mahasiswa');
    $record = scrum31Pengajuan($student, ['status' => 'disetujui', 'status_arsip' => 'menunggu', 'publikasikan_prestasi' => true]);
    $this->actingAs(scrum31User('admin'))->patch('/admin-hima/verifikasi/'.$record->getKey().'/tolak', [
        'catatan_keputusan' => 'Arsip memuat data pribadi peserta.',
    ])->assertRedirect();

    expect($record->fresh()->status)->toBe('disetujui');
    expect($record->fresh()->status_arsip)->toBe('ditolak');
    expect($record->fresh()->publikasikan_prestasi)->toBeTrue();
    expect($record->fresh()->publikasikan_arsip)->toBeFalse();
    expect($student->notifications()->where('type', ActivityNotification::class)->count())->toBe(1);
});

it('requires decision evidence and a rejection reason and notifies the student', function (): void {
    $student = scrum31User('mahasiswa');
    $admin = scrum31User('admin');
    $record = scrum31Pengajuan($student);

    $this->actingAs($admin)->patch('/admin-hima/verifikasi/'.$record->getKey().'/setujui', [])->assertSessionHasErrors(['bukti_sesuai', 'keluaran_lomba_tersedia']);
    expect($record->fresh()->status)->toBe('menunggu_verifikasi');
    $this->patch('/admin-hima/verifikasi/'.$record->getKey().'/tolak', [])->assertSessionHasErrors('catatan_keputusan');
    $this->patch('/admin-hima/verifikasi/'.$record->getKey().'/tolak', ['catatan_keputusan' => 'Bukti hasil tidak cocok.'])->assertRedirect();

    expect($record->fresh()->status)->toBe('ditolak');
    expect($record->fresh()->publikasikan_prestasi)->toBeFalse();
    expect($student->notifications()->where('type', ActivityNotification::class)->firstOrFail()->data['type'])->toBe('prestasi_status');
});
