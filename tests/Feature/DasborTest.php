<?php

use App\Models\Bimbingan;
use App\Models\Konsultasi;
use App\Models\Lomba;
use App\Models\PengajuanPrestasi;
use App\Models\UsulanLomba;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function dasborUser(string $role, array $atribut = []): User
{
    $user = User::factory()->create($atribut);
    $user->forceFill(['role' => $role])->save();

    return $user->refresh();
}

function dasborBimbingan(User $mahasiswa, User $dosen, string $status, array $ubah = []): Bimbingan
{
    $bimbingan = Bimbingan::create([
        'mahasiswa_id' => $mahasiswa->id,
        'dosen_id' => $dosen->id,
        'lomba_id' => null,
        'topik_judul' => 'Topik bimbingan contoh',
        'ringkasan_kebutuhan' => 'Membutuhkan arahan.',
        'nama_tim' => 'Tim Contoh',
        'ketua_tim' => $mahasiswa->name,
        'anggota_tim' => 'Anggota 1',
        'rencana_kegiatan' => 'Menyusun rencana.',
        'status' => $status,
        'batas_respons_at' => now()->addDay(),
        'responded_at' => $status === Bimbingan::MENUNGGU ? null : now(),
    ]);

    if ($ubah !== []) {
        $bimbingan->forceFill($ubah)->saveQuietly();
    }

    return $bimbingan->refresh();
}

function dasborKonsultasi(Bimbingan $bimbingan, string $status, ?Carbon $dibuat = null): Konsultasi
{
    $konsultasi = Konsultasi::create([
        'bimbingan_id' => $bimbingan->bimbingan_id,
        'mahasiswa_id' => $bimbingan->mahasiswa_id,
        'dosen_id' => $bimbingan->dosen_id,
        'pertanyaan' => 'Pertanyaan contoh',
        'status' => $status,
        'responded_at' => $status === Konsultasi::MENUNGGU ? null : now(),
    ]);

    if ($dibuat !== null) {
        $konsultasi->forceFill(['created_at' => $dibuat])->saveQuietly();
    }

    return $konsultasi->refresh();
}

function dasborUsulan(User $pengaju, string $status, ?int $lombaId = null): UsulanLomba
{
    return UsulanLomba::create([
        'pengaju_id' => $pengaju->id,
        'judul_lomba' => 'Usulan contoh',
        'link_resmi' => 'https://contoh.example/usulan',
        'deskripsi' => 'Deskripsi contoh.',
        'status' => $status,
        'lomba_id' => $lombaId,
    ]);
}

function dasborPrestasi(User $mahasiswa, string $status, string $statusArsip = 'belum_ditinjau'): PengajuanPrestasi
{
    return PengajuanPrestasi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'lomba' => 'Lomba contoh',
        'prestasi_hasil' => 'Juara contoh',
        'tanggal_pencapaian' => now()->subDays(10)->toDateString(),
        'peserta_tim' => 'Tim contoh',
        'bukti_pendukung_path' => 'demo/bukti.pdf',
        'keluaran_lomba_path' => 'demo/keluaran.pdf',
        'judul_arsip' => 'Arsip contoh',
        'pengalaman_proses' => 'Proses contoh.',
        'pembelajaran_saran' => 'Saran contoh.',
        'persetujuan_publikasi' => true,
        'status' => $status,
        'status_arsip' => $statusArsip,
    ]);
}

function dasborLomba(string $kategori): Lomba
{
    return Lomba::create([
        'judul_lomba' => 'Lomba '.$kategori,
        'penyelenggara' => 'Penyelenggara contoh',
        'kategori' => $kategori,
        'tingkat' => 'Nasional',
        'tenggat_pendaftaran' => now()->addDays(30)->toDateString(),
        'link_pendaftaran' => 'https://contoh.example/lomba',
        'jenis_pendampingan' => 'tanpa pendampingan',
        'status_publikasi' => 'dipublikasikan',
    ]);
}

it('redirects guests away from both dashboards', function (): void {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('kaprodi.dashboard'))->assertRedirect(route('login'));
    $this->get(route('kaprodi.aktivitas.index'))->assertRedirect(route('login'));
});

it('lets only the admin open the admin dashboard', function (string $role, int $status): void {
    $this->actingAs(dasborUser($role))->get(route('admin.dashboard'))->assertStatus($status);
})->with([
    'mahasiswa' => ['mahasiswa', 403],
    'dosen' => ['dosen', 403],
    'kaprodi' => ['kaprodi', 403],
    'admin' => ['admin', 200],
]);

it('lets only the kaprodi open the lecturer activity pages', function (string $role): void {
    $dosen = dasborUser('dosen');

    $this->actingAs(dasborUser($role));

    $this->get(route('kaprodi.dashboard'))->assertForbidden();
    $this->get(route('kaprodi.aktivitas.index'))->assertForbidden();
    $this->get(route('kaprodi.aktivitas.show', $dosen))->assertForbidden();
})->with(['mahasiswa', 'dosen', 'admin']);

it('counts the waiting work on the admin dashboard with the same definitions as the review pages', function (): void {
    $admin = dasborUser('admin');
    $mahasiswa = dasborUser('mahasiswa');

    dasborUsulan($mahasiswa, 'menunggu');
    dasborUsulan($mahasiswa, 'menunggu');
    dasborUsulan($mahasiswa, 'diproses');
    dasborUsulan($mahasiswa, 'ditolak');

    dasborPrestasi($mahasiswa, PengajuanPrestasi::STATUS_MENUNGGU_VERIFIKASI);
    dasborPrestasi($mahasiswa, PengajuanPrestasi::STATUS_DISETUJUI, PengajuanPrestasi::STATUS_ARSIP_MENUNGGU);
    dasborPrestasi($mahasiswa, PengajuanPrestasi::STATUS_DISETUJUI, PengajuanPrestasi::STATUS_ARSIP_DISETUJUI);
    dasborPrestasi($mahasiswa, PengajuanPrestasi::STATUS_DITOLAK, PengajuanPrestasi::STATUS_ARSIP_DITOLAK);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Ringkasan operasional')
        ->assertViewHas('usulanMenunggu', 2)
        ->assertViewHas('usulanBaru', 2)
        ->assertViewHas('prestasiMenunggu', 1)
        ->assertViewHas('arsipMenunggu', 1)
        // 2 usulan menunggu + 1 diproses + 1 prestasi + 1 arsip
        ->assertViewHas('antrean', fn ($antrean): bool => $antrean->count() === 5)
        ->assertViewHas('grafik', fn (array $grafik): bool => collect($grafik['usulan'])->pluck('value')->all() === [2, 1, 0, 1]
            && $grafik['prestasi']['values'] === [1, 1, 1, 1]);
});

it('draws at least three charts on the admin dashboard', function (): void {
    $html = $this->actingAs(dasborUser('admin'))->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pengajuan masuk per bulan')
        ->assertSee('Status usulan lomba')
        ->assertSee('Lomba tayang per kategori')
        ->assertSee('Prestasi & arsip menurut tahap')
        ->getContent();

    expect(substr_count($html, 'role="img"'))->toBeGreaterThanOrEqual(3);
});

it('splits published lomba per category by their origin using a join with usulan lomba', function (): void {
    $mahasiswa = dasborUser('mahasiswa');
    $dariUsulan = dasborLomba('Teknologi');
    dasborLomba('Teknologi');
    dasborUsulan($mahasiswa, 'dipublikasikan', $dariUsulan->lomba_id);

    $this->actingAs(dasborUser('admin'))->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('grafik', fn (array $grafik): bool => $grafik['kategori']['labels'] === ['Teknologi']
            && $grafik['kategori']['series'][0]['values'] === [1]
            && $grafik['kategori']['series'][1]['values'] === [1]);
});

it('summarises lecturer activity and keeps automatic rejections out of the decisions', function (): void {
    $dosen = dasborUser('dosen');
    $mahasiswa = dasborUser('mahasiswa');
    $duaHariLalu = now()->subHours(48);

    $disetujui = dasborBimbingan($mahasiswa, $dosen, Bimbingan::DISETUJUI, ['created_at' => $duaHariLalu]);
    dasborBimbingan($mahasiswa, $dosen, Bimbingan::DITOLAK, ['created_at' => $duaHariLalu]);
    dasborBimbingan($mahasiswa, $dosen, Bimbingan::DITOLAK, ['ditolak_otomatis' => true, 'created_at' => $duaHariLalu]);

    dasborKonsultasi($disetujui, Konsultasi::DITANGGAPI, $duaHariLalu);
    dasborKonsultasi($disetujui, Konsultasi::MENUNGGU);

    $this->actingAs(dasborUser('kaprodi'))->get(route('kaprodi.aktivitas.index'))
        ->assertOk()
        ->assertSee('Aktivitas dosen')
        ->assertViewHas('stat', fn (array $stat): bool => $stat['jumlah_dosen'] === 1
            && $stat['konsultasi_ditanggapi'] === 1
            && $stat['bimbingan_setuju'] === 1
            && $stat['bimbingan_tolak'] === 1
            && $stat['bimbingan_diproses'] === 2
            && $stat['bimbingan_otomatis'] === 1
            && $stat['rata'] === '2 hari');
});

it('only evaluates lecturers and never the kaprodi', function (): void {
    dasborUser('dosen', ['name' => 'Dimas Arunika, M.Kom.']);
    dasborUser('kaprodi');

    $this->actingAs(dasborUser('kaprodi'))->get(route('kaprodi.aktivitas.index'))
        ->assertOk()
        ->assertSee('Dimas Arunika')
        ->assertViewHas('ringkasan', fn ($ringkasan): bool => $ringkasan->count() === 1);
});

it('filters by period and falls back to the current month for an invalid period', function (): void {
    $dosen = dasborUser('dosen');
    $mahasiswa = dasborUser('mahasiswa');
    dasborBimbingan($mahasiswa, $dosen, Bimbingan::DISETUJUI);

    $kaprodi = dasborUser('kaprodi');
    $bulanLalu = Carbon::now('Asia/Jakarta')->startOfMonth()->subMonth()->format('Y-m');
    $bulanIni = Carbon::now('Asia/Jakarta')->format('Y-m');

    $this->actingAs($kaprodi)->get(route('kaprodi.aktivitas.index', ['periode' => $bulanLalu]))
        ->assertOk()
        ->assertViewHas('periodeKey', $bulanLalu)
        ->assertViewHas('stat', fn (array $stat): bool => $stat['bimbingan_diproses'] === 0);

    $this->get(route('kaprodi.aktivitas.index', ['periode' => '2020-01']))
        ->assertOk()
        ->assertViewHas('periodeKey', $bulanIni)
        ->assertViewHas('stat', fn (array $stat): bool => $stat['bimbingan_diproses'] === 1);
});

it('narrows the summary to one lecturer when a lecturer is selected', function (): void {
    $pertama = dasborUser('dosen');
    $kedua = dasborUser('dosen');
    $mahasiswa = dasborUser('mahasiswa');
    dasborBimbingan($mahasiswa, $pertama, Bimbingan::DISETUJUI);
    dasborBimbingan($mahasiswa, $kedua, Bimbingan::DISETUJUI);

    $this->actingAs(dasborUser('kaprodi'))->get(route('kaprodi.aktivitas.index', ['dosen' => $pertama->id]))
        ->assertOk()
        ->assertViewHas('stat', fn (array $stat): bool => $stat['jumlah_dosen'] === 1 && $stat['bimbingan_diproses'] === 1);
});

it('rejects expired pending bimbingan before counting them as not responded', function (): void {
    $dosen = dasborUser('dosen');
    $mahasiswa = dasborUser('mahasiswa');
    $kedaluwarsa = dasborBimbingan($mahasiswa, $dosen, Bimbingan::MENUNGGU, [
        'created_at' => now()->subDays(2),
        'batas_respons_at' => now()->subDay(),
    ]);

    $this->actingAs(dasborUser('kaprodi'))->get(route('kaprodi.aktivitas.index'))
        ->assertOk()
        ->assertViewHas('stat', fn (array $stat): bool => $stat['bimbingan_otomatis'] === 1 && $stat['bimbingan_diproses'] === 0);

    expect($kedaluwarsa->refresh()->status)->toBe(Bimbingan::DITOLAK)
        ->and($kedaluwarsa->ditolak_otomatis)->toBeTrue();
});

it('shows the lecturer detail with the activity list and 404s for a non lecturer', function (): void {
    $dosen = dasborUser('dosen', ['name' => 'Dimas Arunika, M.Kom.']);
    $mahasiswa = dasborUser('mahasiswa');
    $bimbingan = dasborBimbingan($mahasiswa, $dosen, Bimbingan::DISETUJUI);
    $konsultasi = dasborKonsultasi($bimbingan, Konsultasi::DITANGGAPI, now()->subDay());

    $this->actingAs(dasborUser('kaprodi'));

    $this->get(route('kaprodi.aktivitas.show', $dosen))
        ->assertOk()
        ->assertSee('Detail aktivitas · Dimas Arunika')
        ->assertSee($konsultasi->kode)
        ->assertSee($bimbingan->kode)
        ->assertSee('Kembali ke aktivitas dosen');

    $this->get(route('kaprodi.aktivitas.show', $mahasiswa))->assertNotFound();
});

it('draws at least three charts on the kaprodi dashboard', function (): void {
    $dosen = dasborUser('dosen');
    dasborBimbingan(dasborUser('mahasiswa'), $dosen, Bimbingan::DISETUJUI);

    $html = $this->actingAs(dasborUser('kaprodi'))->get(route('kaprodi.dashboard'))
        ->assertOk()
        ->assertSee('Aktivitas per dosen')
        ->assertSee('Rata-rata waktu tanggapan')
        ->assertSee('Keputusan bimbingan per dosen')
        ->assertSee('Aktivitas mingguan')
        ->assertViewHas('grafik', fn (array $grafik): bool => array_keys($grafik) === ['perDosen', 'waktu', 'keputusan', 'mingguan'])
        ->getContent();

    expect(substr_count($html, 'role="img"'))->toBeGreaterThanOrEqual(3);
});
