<?php

use App\Models\Bimbingan;
use App\Models\User;
use Database\Seeders\UserDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('signs in demo accounts and redirects each role to its own area', function (string $email, string $role, string $path) {
    $this->seed(UserDemoSeeder::class);
    $user = User::where('email', $email)->firstOrFail();

    $this->postJson('/login', [
        'email' => $email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('redirect', url($path));

    $this->assertAuthenticatedAs($user);
    $this->get($path)->assertOk();
    expect($user->role)->toBe($role);
})->with([
    'Mahasiswa' => ['nadia@student.telkomuniversity.ac.id', 'mahasiswa', '/bimbingan'],
    'Admin HIMA' => ['adminhima@student.telkomuniversity.ac.id', 'admin', '/admin-hima/notifikasi'],
    'Dosen' => ['dimas.arunika@telkomuniversity.ac.id', 'dosen', '/dosen/permintaan-bimbingan'],
]);

it('rejects login attempts from email domains outside the campus accounts', function () {
    $this->postJson('/login', [
        'email' => 'demo@example.test',
        'password' => 'password',
    ])->assertForbidden();

    $this->assertGuest();
});

it('connects student requests, lecturer decisions, notifications, and the lecturer profile', function () {
    $this->seed(UserDemoSeeder::class);
    $student = User::where('email', 'nadia@student.telkomuniversity.ac.id')->firstOrFail();
    $lecturer = User::where('email', 'dimas.arunika@telkomuniversity.ac.id')->firstOrFail();

    $this->postJson('/login', [
        'email' => $student->email,
        'password' => 'password',
    ])->assertOk();

    $this->post('/bimbingan', [
        'lomba_id' => null,
        'dosen_id' => $lecturer->id,
        'topik_judul' => 'Pendampingan Kompetisi Demo',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan penyusunan proposal.',
        'nama_tim' => 'Tim Demo Tel-Compt',
        'ketua_tim' => $student->name,
        'anggota_tim' => 'Anggota Demo',
        'rencana_kegiatan' => 'Menyusun proposal dan jadwal latihan.',
    ])->assertRedirect(route('bimbingan.index'));

    $bimbingan = Bimbingan::query()->firstOrFail();
    expect($bimbingan->status)->toBe(Bimbingan::MENUNGGU);
    expect($lecturer->notifications()->count())->toBe(1);

    $this->post('/logout')->assertRedirect(route('login'));
    $this->postJson('/login', [
        'email' => $lecturer->email,
        'password' => 'password',
    ])->assertOk();

    $this->get('/dosen/profil-keahlian')->assertOk();
    $this->get('/dosen/notifikasi')
        ->assertOk()
        ->assertSee('Permintaan bimbingan baru')
        ->assertSee('Pendampingan Kompetisi Demo');
    $this->get(route('dosen.bimbingan.show', $bimbingan))
        ->assertOk()
        ->assertSee('Pendampingan Kompetisi Demo');

    $this->patch(route('dosen.bimbingan.respond', $bimbingan), [
        'aksi' => 'setuju',
        'catatan' => 'Saya bersedia membimbing tim ini.',
    ])->assertRedirect();

    expect($bimbingan->fresh()->status)->toBe(Bimbingan::DISETUJUI);

    $this->post('/logout')->assertRedirect(route('login'));
    $this->postJson('/login', [
        'email' => $student->email,
        'password' => 'password',
    ])->assertOk();

    $this->get(route('bimbingan.show', $bimbingan))
        ->assertOk()
        ->assertSee('Disetujui')
        ->assertSee('Saya bersedia membimbing tim ini.');
    $this->get('/notifikasi')
        ->assertOk()
        ->assertSee('Keputusan bimbingan');
});
