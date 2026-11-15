<?php

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\User;
use App\Models\UsulanLomba;
use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function notificationTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role])->save();

    return $user->refresh();
}

function notificationTestSend(User $user, string $title): DatabaseNotification
{
    $existingIds = $user->notifications()->pluck('id')->all();
    $user->notify(new ActivityNotification($title, 'Isi '.$title, 'test', 1, '/'));

    return $user->notifications()->whereNotIn('id', $existingIds)->firstOrFail();
}

function notificationTestFind(User $user, string $type, int $relatedId): ?DatabaseNotification
{
    /** @var Collection<int, DatabaseNotification> $notifications */
    $notifications = $user->notifications()->where('type', ActivityNotification::class)->get();

    return $notifications->first(fn (DatabaseNotification $notification): bool => $notification->data['type'] === $type
        && (int) $notification->data['related_id'] === $relatedId
    );
}

function notificationTestUsulan(User $student, string $title): UsulanLomba
{
    return UsulanLomba::create([
        'pengaju_id' => $student->id,
        'judul_lomba' => $title,
        'link_resmi' => 'https://example.test/lomba',
        'deskripsi' => 'Deskripsi lomba contoh',
        'status' => 'menunggu',
    ]);
}

function notificationTestBimbingan(User $student, User $lecturer, string $status = Bimbingan::MENUNGGU): Bimbingan
{
    return Bimbingan::create([
        'mahasiswa_id' => $student->id,
        'dosen_id' => $lecturer->id,
        'lomba_id' => null,
        'topik_judul' => 'Topik contoh',
        'ringkasan_kebutuhan' => 'Ringkasan kebutuhan',
        'nama_tim' => 'Tim Contoh',
        'ketua_tim' => $student->name,
        'anggota_tim' => 'Anggota 1',
        'rencana_kegiatan' => 'Rencana kegiatan',
        'status' => $status,
        'batas_respons_at' => now()->addDay(),
    ]);
}

test('notification pages enforce role and user ownership and support filters', function (): void {
    $student = notificationTestUser('mahasiswa');
    $otherStudent = notificationTestUser('mahasiswa');
    $admin = notificationTestUser('admin');
    $lecturer = notificationTestUser('kaprodi');

    $unread = notificationTestSend($student, 'Notifikasi belum dibaca');
    $read = notificationTestSend($student, 'Notifikasi sudah dibaca');
    $read->markAsRead();
    notificationTestSend($otherStudent, 'Notifikasi milik pengguna lain');

    $this->actingAs($student)
        ->get('/notifikasi')
        ->assertOk()
        ->assertSee('Notifikasi belum dibaca')
        ->assertSee('Notifikasi sudah dibaca')
        ->assertDontSee('Notifikasi milik pengguna lain')
        ->assertSee('Notifikasi (1 belum dibaca)', false);

    $this->get('/notifikasi?filter=unread')
        ->assertOk()
        ->assertSee('Notifikasi belum dibaca')
        ->assertDontSee('Notifikasi sudah dibaca');

    $this->get('/notifikasi?filter=read')
        ->assertOk()
        ->assertSee('Notifikasi sudah dibaca')
        ->assertDontSee('Notifikasi belum dibaca');

    $this->get('/admin-hima/notifikasi')->assertForbidden();
    $this->actingAs($admin)->get('/admin-hima/notifikasi')->assertOk();
    $this->actingAs($lecturer)->get('/dosen/notifikasi')->assertOk();
});

test('read actions update only the signed in users notifications', function (): void {
    $student = notificationTestUser('mahasiswa');
    $otherStudent = notificationTestUser('mahasiswa');
    $one = notificationTestSend($student, 'Baca satu');
    $two = notificationTestSend($student, 'Baca semua');
    $other = notificationTestSend($otherStudent, 'Tetap belum dibaca');

    $this->actingAs($student)->post('/notifikasi/'.$one->id.'/read')->assertRedirect();
    expect($student->notifications()->findOrFail($one->id)->read_at)->not->toBeNull();
    expect($otherStudent->notifications()->findOrFail($other->id)->read_at)->toBeNull();

    $this->post('/notifikasi/read-all')->assertRedirect();
    expect($student->notifications()->findOrFail($two->id)->read_at)->not->toBeNull();
    expect($otherStudent->notifications()->findOrFail($other->id)->read_at)->toBeNull();

    $this->post('/notifikasi/'.$other->id.'/read')->assertNotFound();
});

test('new usulan and each admin status change notify the correct users', function (): void {
    $student = notificationTestUser('mahasiswa');
    $admin = notificationTestUser('admin');
    $secondAdmin = notificationTestUser('admin');

    $this->actingAs($student)->post('/usulkan-lomba', [
        'judul_lomba' => 'Kompetisi Baru',
        'link_resmi' => 'https://example.test/kompetisi',
        'deskripsi' => 'Kompetisi untuk mahasiswa.',
    ])->assertRedirect(route('mahasiswa.usulan.create'));

    $created = UsulanLomba::query()->latest('usulan_id')->firstOrFail();
    expect($admin->notifications()->firstOrFail()->data['type'])->toBe('usulan_dibuat');
    expect($secondAdmin->notifications()->count())->toBe(1);
    expect($admin->notifications()->first()->data['url'])->toContain('/admin-hima/usulan/');

    $this->actingAs($admin)
        ->get('/admin-hima/notifikasi')
        ->assertOk()
        ->assertSee('Pengajuan lomba baru')
        ->assertSee('Kompetisi Baru');

    $processing = notificationTestUsulan($student, 'Sedang Diproses');
    $this->actingAs($admin)->patch('/admin-hima/usulan/'.$processing->usulan_id.'/proses')->assertRedirect();
    expect(notificationTestFind($student, 'usulan_status', (int) $processing->usulan_id)?->data['title'])
        ->toBe('Pengajuan diproses');

    $published = notificationTestUsulan($student, 'Dipublikasikan');
    $this->post('/admin-hima/usulan/'.$published->usulan_id.'/publikasikan', [
        'judul_lomba' => 'Dipublikasikan',
        'penyelenggara' => 'Penyelenggara Contoh',
        'kategori' => 'Teknologi',
        'tingkat' => 'Nasional',
        'pendaftaran_dibuka' => now()->toDateString(),
        'tenggat_pendaftaran' => now()->addDays(7)->toDateString(),
        'jenis_pendampingan' => 'konsultasi ringan',
        'link_pendaftaran' => 'https://example.test/daftar',
        'deskripsi' => 'Detail kompetisi.',
    ])->assertRedirect();
    expect(notificationTestFind($student, 'usulan_status', (int) $published->usulan_id)?->data['title'])
        ->toBe('Pengajuan dipublikasikan');

    $rejected = notificationTestUsulan($student, 'Ditolak');
    $this->patch('/admin-hima/usulan/'.$rejected->usulan_id.'/tolak', [
        'catatan_admin' => 'Informasi belum lengkap.',
    ])->assertRedirect();
    expect(notificationTestFind($student, 'usulan_status', (int) $rejected->usulan_id)?->data['title'])
        ->toBe('Pengajuan ditolak');
    expect($created->refresh()->status)->toBe('menunggu');
});

test('new bimbingan and lecturer decisions notify the assigned lecturer and student', function (): void {
    $student = notificationTestUser('mahasiswa');
    $lecturer = notificationTestUser('dosen');
    $otherLecturer = notificationTestUser('dosen');

    $this->actingAs($student)->post('/bimbingan', [
        'lomba_id' => null,
        'dosen_id' => $lecturer->id,
        'topik_judul' => 'Pendampingan Kompetisi',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan.',
        'nama_tim' => 'Tim Mahasiswa',
        'ketua_tim' => $student->name,
        'anggota_tim' => 'Anggota A',
        'rencana_kegiatan' => 'Menyusun proposal.',
    ])->assertRedirect(route('bimbingan.index'));

    $bimbingan = Bimbingan::query()->firstOrFail();
    expect(notificationTestFind($lecturer, 'bimbingan_dibuat', (int) $bimbingan->getKey()))->not->toBeNull();
    expect($otherLecturer->notifications()->count())->toBe(0);

    $this->actingAs($lecturer)->patch('/dosen/permintaan-bimbingan/'.$bimbingan->getKey(), [
        'aksi' => 'setuju',
        'catatan' => 'Saya dapat membimbing tim ini.',
    ])->assertRedirect();

    expect(notificationTestFind($student, 'bimbingan_status', (int) $bimbingan->getKey())?->data['title'])
        ->toBe('Keputusan bimbingan');
});

test('automatic bimbingan rejection notifies only after its transaction commits', function (): void {
    $student = notificationTestUser('mahasiswa');
    $lecturer = notificationTestUser('dosen');
    $bimbingan = notificationTestBimbingan($student, $lecturer);
    $bimbingan->update(['batas_respons_at' => now()->subMinute()]);

    $rolledBack = false;

    try {
        DB::transaction(function (): void {
            Bimbingan::tolakKadaluarsa();
            throw new RuntimeException('Simulasi rollback.');
        });
    } catch (RuntimeException) {
        $rolledBack = true;
    }

    expect($rolledBack)->toBeTrue();
    expect($bimbingan->fresh()->status)->toBe(Bimbingan::MENUNGGU);
    expect($student->notifications()->count())->toBe(0);

    $updated = DB::transaction(fn (): int => Bimbingan::tolakKadaluarsa());

    expect($updated)->toBe(1);
    expect($bimbingan->fresh()->status)->toBe(Bimbingan::DITOLAK);
    expect(notificationTestFind($student, 'bimbingan_status', (int) $bimbingan->getKey())?->data['title'])
        ->toBe('Bimbingan ditolak otomatis');
});

test('consultation requests and responses notify the assigned lecturer and student', function (): void {
    $student = notificationTestUser('mahasiswa');
    $lecturer = notificationTestUser('kaprodi');
    $bimbingan = notificationTestBimbingan($student, $lecturer, Bimbingan::DISETUJUI);

    $this->actingAs($student)->post('/bimbingan/'.$bimbingan->getKey().'/konsultasi', [
        'pertanyaan' => 'Bagaimana menyusun jadwal latihan?',
    ])->assertRedirect(route('bimbingan.show', $bimbingan));

    $konsultasi = Konsultasi::query()->firstOrFail();
    expect(notificationTestFind($lecturer, 'konsultasi_dibuat', (int) $konsultasi->getKey()))->not->toBeNull();

    $this->actingAs($lecturer)->patch('/dosen/permintaan-konsultasi/'.$konsultasi->getKey(), [
        'aksi' => 'tanggapi',
        'catatan' => 'Bagi latihan menjadi beberapa tahap.',
    ])->assertRedirect();

    expect(notificationTestFind($student, 'konsultasi_status', (int) $konsultasi->getKey())?->data['title'])
        ->toBe('Tanggapan konsultasi');
});
