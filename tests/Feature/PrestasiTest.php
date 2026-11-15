<?php

use App\Models\PengajuanPrestasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function scrum30User(string $role = 'mahasiswa'): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role])->save();

    return $user->refresh();
}

function scrum30Payload(array $overrides = []): array
{
    return array_merge([
        'lomba' => 'Kompetisi Desain Aksesibel 2025',
        'prestasi_hasil' => 'Juara 2 · Nasional',
        'tanggal_pencapaian' => '2025-11-20',
        'peserta_tim' => 'Nadia Pramesti · Tim Akses',
        'bukti_pendukung' => UploadedFile::fake()->create('sertifikat.pdf', 120, 'application/pdf'),
        'keluaran_lomba' => UploadedFile::fake()->create('prototype.png', 240, 'image/png'),
        'judul_arsip' => 'Menguji aksesibilitas prototipe',
        'pengalaman_proses' => 'Tim menyusun tugas dan menguji navigasi dengan peserta.',
        'pembelajaran_saran' => 'Dokumentasikan temuan sejak awal.',
        'dokumen_pengalaman' => UploadedFile::fake()->create('catatan.pdf', 80, 'application/pdf'),
        'persetujuan_publikasi' => '1',
    ], $overrides);
}

function scrum30Record(User $mahasiswa, array $overrides = []): PengajuanPrestasi
{
    return PengajuanPrestasi::create(array_merge([
        'mahasiswa_id' => $mahasiswa->getKey(),
        'lomba' => 'Kompetisi Contoh',
        'prestasi_hasil' => 'Juara 2 · Nasional',
        'tanggal_pencapaian' => '2025-11-20',
        'peserta_tim' => 'Tim Akses',
        'bukti_pendukung_path' => 'prestasi/bukti.pdf',
        'keluaran_lomba_path' => 'prestasi/keluaran.pdf',
        'judul_arsip' => 'Arsip pengalaman mahasiswa',
        'pengalaman_proses' => 'Pengalaman proses lomba.',
        'pembelajaran_saran' => 'Saran untuk peserta berikutnya.',
        'dokumen_pengalaman_path' => null,
        'persetujuan_publikasi' => true,
        'status' => PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI,
    ], $overrides));
}

it('allows only signed in mahasiswa to open the form and their submission history', function (): void {
    $this->get('/prestasi-arsip')->assertRedirect(route('login'));

    $admin = scrum30User('admin');
    $this->actingAs($admin)->get('/prestasi-arsip')->assertForbidden();
    $this->post('/prestasi-arsip', scrum30Payload())->assertForbidden();
    $this->get('/riwayat-pengajuan')->assertForbidden();

    $mahasiswa = scrum30User();
    $this->actingAs($mahasiswa)
        ->get('/prestasi-arsip')
        ->assertOk()
        ->assertSee('Data prestasi')
        ->assertSee('Bukti pendukung')
        ->assertSee('Keluaran lomba')
        ->assertSee('Pembelajaran &amp; saran', false);

    $this->get('/riwayat-pengajuan')
        ->assertOk()
        ->assertSee('Belum ada pengajuan prestasi');
});

it('stores a mahasiswa submission, its private files, consent, and initial verification status', function (): void {
    Storage::fake('local');
    $mahasiswa = scrum30User();

    $this->actingAs($mahasiswa)
        ->post('/prestasi-arsip', scrum30Payload())
        ->assertRedirect(route('mahasiswa.prestasi.create'))
        ->assertSessionHas('pengajuan_baru');

    $pengajuan = PengajuanPrestasi::query()->firstOrFail();

    $this->assertDatabaseHas('pengajuan_prestasi', [
        'pengajuan_id' => $pengajuan->getKey(),
        'mahasiswa_id' => $mahasiswa->getKey(),
        'lomba' => 'Kompetisi Desain Aksesibel 2025',
        'prestasi_hasil' => 'Juara 2 · Nasional',
        'peserta_tim' => 'Nadia Pramesti · Tim Akses',
        'judul_arsip' => 'Menguji aksesibilitas prototipe',
        'status' => 'menunggu_verifikasi',
        'persetujuan_publikasi' => true,
    ]);

    expect($pengajuan->tanggal_pencapaian->toDateString())->toBe('2025-11-20');
    expect($pengajuan->mahasiswa->is($mahasiswa))->toBeTrue();
    expect($pengajuan->status_label)->toBe('Menunggu Verifikasi');

    Storage::disk('local')->assertExists([
        $pengajuan->bukti_pendukung_path,
        $pengajuan->keluaran_lomba_path,
        $pengajuan->dokumen_pengalaman_path,
    ]);

    $this->get('/prestasi-arsip')
        ->assertOk()
        ->assertSee('Dokumen diterima')
        ->assertSee('P-001')
        ->assertSee('Menunggu Verifikasi');
});

it('accepts a submission without the optional experience document', function (): void {
    Storage::fake('local');
    $mahasiswa = scrum30User();
    $data = scrum30Payload();
    unset($data['dokumen_pengalaman']);

    $this->actingAs($mahasiswa)
        ->post('/prestasi-arsip', $data)
        ->assertRedirect(route('mahasiswa.prestasi.create'));

    expect(PengajuanPrestasi::query()->firstOrFail()->dokumen_pengalaman_path)->toBeNull();
});

it('requires the fields marked required in the submission form', function (): void {
    $mahasiswa = scrum30User();

    $this->actingAs($mahasiswa)
        ->post('/prestasi-arsip', [])
        ->assertSessionHasErrors([
            'lomba',
            'prestasi_hasil',
            'tanggal_pencapaian',
            'peserta_tim',
            'bukti_pendukung',
            'keluaran_lomba',
            'judul_arsip',
            'pengalaman_proses',
            'pembelajaran_saran',
            'persetujuan_publikasi',
        ]);

    $this->assertDatabaseCount('pengajuan_prestasi', 0);
});

it('rejects an uploaded file outside the supported formats', function (): void {
    Storage::fake('local');
    $mahasiswa = scrum30User();

    $this->actingAs($mahasiswa)
        ->post('/prestasi-arsip', scrum30Payload([
            'bukti_pendukung' => UploadedFile::fake()->create('bukti.txt', 10, 'text/plain'),
        ]))
        ->assertSessionHasErrors('bukti_pendukung');

    $this->assertDatabaseCount('pengajuan_prestasi', 0);
});

it('rejects an uploaded file larger than ten megabytes', function (): void {
    Storage::fake('local');
    $mahasiswa = scrum30User();

    $this->actingAs($mahasiswa)
        ->post('/prestasi-arsip', scrum30Payload([
            'keluaran_lomba' => UploadedFile::fake()->create('keluaran.pdf', 10241, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('keluaran_lomba');

    $this->assertDatabaseCount('pengajuan_prestasi', 0);
});

it('shows only the mahasiswa own submissions and files', function (): void {
    Storage::fake('local');
    $mahasiswa = scrum30User();
    $mahasiswaLain = scrum30User();

    $this->actingAs($mahasiswa)
        ->post('/prestasi-arsip', scrum30Payload())
        ->assertRedirect(route('mahasiswa.prestasi.create'));

    $milikMahasiswa = PengajuanPrestasi::query()->firstOrFail();
    Storage::disk('local')->put('prestasi/pengguna-lain.pdf', 'berkas privat');
    $milikMahasiswaLain = scrum30Record($mahasiswaLain, [
        'judul_arsip' => 'Arsip yang bukan milik saya',
        'bukti_pendukung_path' => 'prestasi/pengguna-lain.pdf',
    ]);

    $this->get('/riwayat-pengajuan')
        ->assertOk()
        ->assertSee($milikMahasiswa->judul_arsip)
        ->assertDontSee($milikMahasiswaLain->judul_arsip);

    $this->get('/riwayat-pengajuan/'.$milikMahasiswa->getKey().'/berkas/bukti-pendukung')
        ->assertOk();

    $this->get('/riwayat-pengajuan/'.$milikMahasiswaLain->getKey().'/berkas/bukti-pendukung')
        ->assertNotFound();
});

it('escapes student supplied archive text in the history page', function (): void {
    $mahasiswa = scrum30User();
    $judul = '<script>alert(1)</script>';
    scrum30Record($mahasiswa, ['judul_arsip' => $judul]);

    $this->actingAs($mahasiswa)
        ->get('/riwayat-pengajuan')
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee($judul, false);
});
