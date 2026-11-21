<?php

namespace Database\Seeders;

use App\Models\Bimbingan;
use App\Models\BidangKeahlian;
use App\Models\Konsultasi;
use App\Models\Lomba;
use App\Models\PengajuanPrestasi;
use App\Models\UsulanLomba;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Data contoh untuk dasbor Admin HIMA (SCRUM-33) dan dasbor Kaprodi (SCRUM-35).
 * Jalankan SETELAH UserDemoSeeder:
 *   php artisan db:seed --class=UserDemoSeeder
 *   php artisan db:seed --class=DasborDemoSeeder
 * Aman dijalankan ulang (dilewati bila data contoh sudah ada). Semua akun berpassword: password
 */
class DasborDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Konsultasi::where('pertanyaan', 'like', '[Demo]%')->exists()) {
            $this->command?->info('Data contoh dasbor sudah ada, dilewati.');

            return;
        }

        $nadia = User::where('email', 'nadia@student.telkomuniversity.ac.id')->first();
        $dimas = User::where('email', 'dimas.arunika@telkomuniversity.ac.id')->first();
        $laras = User::where('email', 'laras.senandika@telkomuniversity.ac.id')->first();

        if (! $nadia || ! $dimas || ! $laras) {
            $this->command?->warn('Jalankan UserDemoSeeder dulu: php artisan db:seed --class=UserDemoSeeder');

            return;
        }

        [, $bayu, $raka, $sinta] = $this->akunTambahan();
        $this->tagKeahlian($dimas, $laras);

        $mahasiswa = [$nadia, $raka, $sinta];
        $dosen = [$dimas, $laras, $bayu];

        // Konsultasi + bimbingan tersebar di bulan ini dan bulan lalu.
        // [dosen, hari lalu dibuat, jam sampai direspons (null = belum), status]
        $konsultasi = [
            [0, 3, 20, 'ditanggapi'], [0, 5, 30, 'ditanggapi'], [0, 9, 48, 'ditolak'], [0, 12, 26, 'ditanggapi'],
            [0, 2, null, 'menunggu'], [0, 34, 22, 'ditanggapi'], [0, 41, 40, 'ditanggapi'],
            [1, 4, 36, 'ditanggapi'], [1, 7, 40, 'ditanggapi'], [1, 15, 52, 'ditanggapi'], [1, 18, 30, 'ditolak'],
            [1, 38, 44, 'ditanggapi'], [1, 46, 38, 'ditanggapi'],
            [2, 6, 62, 'ditanggapi'], [2, 10, 70, 'ditanggapi'], [2, 1, null, 'menunggu'], [2, 36, 58, 'ditanggapi'],
        ];

        $bimbingan = [
            [0, 6, 22, 'disetujui', false], [0, 11, 40, 'disetujui', false], [0, 16, 50, 'ditolak', false],
            [0, 37, 30, 'disetujui', false],
            [1, 8, 30, 'disetujui', false], [1, 13, 46, 'ditolak', false], [1, 20, null, 'ditolak', true],
            [1, 42, 36, 'selesai', false],
            [2, 5, null, 'ditolak', true], [2, 9, 64, 'disetujui', false], [2, 0, null, 'menunggu', false],
        ];

        $urut = 0;

        foreach ($bimbingan as [$d, $hari, $jam, $status, $otomatis]) {
            $dibuat = Carbon::now()->subDays($hari)->subHours($urut % 5);
            $direspons = $jam !== null ? $dibuat->copy()->addHours($jam) : ($otomatis ? $dibuat->copy()->addDay() : null);

            $b = Bimbingan::create([
                'mahasiswa_id' => $mahasiswa[$urut % 3]->id,
                'dosen_id' => $dosen[$d]->id,
                'topik_judul' => '[Demo] Bimbingan tim '.($urut + 1),
                'ringkasan_kebutuhan' => 'Data contoh untuk dasbor.',
                'nama_tim' => 'Tim Contoh '.($urut + 1),
                'ketua_tim' => $mahasiswa[$urut % 3]->name,
                'anggota_tim' => 'Anggota satu',
                'rencana_kegiatan' => 'Rencana contoh.',
                'status' => $status,
                'catatan_dosen' => $direspons ? 'Catatan contoh.' : null,
                'ditolak_otomatis' => $otomatis,
                'batas_respons_at' => $dibuat->copy()->addDay(),
                'responded_at' => $direspons,
            ]);
            $b->forceFill(['created_at' => $dibuat, 'updated_at' => $direspons ?? $dibuat])->saveQuietly();
            $urut++;
        }

        // Konsultasi menempel pada bimbingan yang disetujui milik dosen yang sama
        // (kalau dosen belum punya, dibuatkan satu bimbingan pendamping yang disetujui).
        foreach ($konsultasi as $i => [$d, $hari, $jam, $status]) {
            $induk = Bimbingan::where('dosen_id', $dosen[$d]->id)->whereIn('status', ['disetujui', 'selesai'])->first();

            if (! $induk) {
                continue;
            }

            $dibuat = Carbon::now()->subDays($hari)->subHours($i % 6);
            $direspons = $jam !== null ? $dibuat->copy()->addHours($jam) : null;

            $k = Konsultasi::create([
                'bimbingan_id' => $induk->bimbingan_id,
                'mahasiswa_id' => $induk->mahasiswa_id,
                'dosen_id' => $dosen[$d]->id,
                'pertanyaan' => '[Demo] Pertanyaan konsultasi '.($i + 1),
                'status' => $status,
                'jawaban_arahan' => $direspons ? 'Tanggapan contoh.' : null,
                'responded_at' => $direspons,
            ]);
            $k->forceFill(['created_at' => $dibuat, 'updated_at' => $direspons ?? $dibuat])->saveQuietly();
        }

        $this->dataAdmin($nadia);

        $this->command?->info('Data contoh dasbor dibuat. Login: /dev/login/kaprodi dan /dev/login/admin');
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: User}
     */
    private function akunTambahan(): array
    {
        return User::unguarded(function (): array {
            $buat = fn (string $email, string $nama, string $role, string $nip): User => User::updateOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => 'password', 'role' => $role, 'nim_nip' => $nip, 'program_studi' => 'Sistem Informasi'],
            );

            return [
                $buat('kaprodi@telkomuniversity.ac.id', 'Akun Kaprodi', 'kaprodi', 'KAPRODI01'),
                $buat('bayu.aksara@telkomuniversity.ac.id', 'Bayu Aksara, M.Kom.', 'dosen', '20990003'),
                $buat('raka@student.telkomuniversity.ac.id', 'Raka Bimantara', 'mahasiswa', '1301260002'),
                $buat('sinta@student.telkomuniversity.ac.id', 'Sinta Kinasih', 'mahasiswa', '1301260003'),
            ];
        });
    }

    /** Dimas dan Laras punya tag keahlian; Bayu sengaja dikosongkan (contoh "belum lengkap"). */
    private function tagKeahlian(User $dimas, User $laras): void
    {
        $ambil = fn (string ...$nama): array => collect($nama)
            ->map(fn (string $n): int => BidangKeahlian::firstOrCreate(['nama_bidang' => $n])->getKey())
            ->all();

        $dimas->keahlian()->syncWithoutDetaching($ambil('Interaksi manusia–komputer', 'Rekayasa perangkat lunak', 'Pengujian perangkat lunak'));
        $laras->keahlian()->syncWithoutDetaching($ambil('Visualisasi data', 'Basis data', 'Analitik bisnis'));
    }

    /** Usulan lomba, lomba, serta pengajuan prestasi untuk dasbor Admin HIMA. */
    private function dataAdmin(User $mahasiswa): void
    {
        $lomba = [];

        foreach ([
            ['Teknologi', 3], ['Desain', 21], ['Bisnis', 40], ['Karya tulis', 5],
        ] as $i => [$kategori, $hariTenggat]) {
            $lomba[$i] = Lomba::create([
                'judul_lomba' => '[Demo] Lomba '.$kategori,
                'penyelenggara' => 'Penyelenggara contoh',
                'kategori' => $kategori,
                'tingkat' => 'Nasional',
                'tenggat_pendaftaran' => Carbon::now()->addDays($hariTenggat)->toDateString(),
                'link_pendaftaran' => 'https://contoh.example/lomba-'.($i + 1),
                'deskripsi' => 'Data contoh.',
                'jenis_pendampingan' => 'tanpa pendampingan',
                'status_publikasi' => 'dipublikasikan',
            ]);
        }

        // [status, hari lalu, indeks lomba hasil publikasi]
        foreach ([
            ['menunggu', 0, null], ['menunggu', 2, null], ['menunggu', 9, null], ['diproses', 4, null],
            ['dipublikasikan', 20, 0], ['dipublikasikan', 33, 2], ['ditolak', 14, null], ['ditolak', 45, null],
        ] as $i => [$status, $hari, $indeksLomba]) {
            $u = UsulanLomba::create([
                'pengaju_id' => $mahasiswa->id,
                'judul_lomba' => '[Demo] Usulan '.($i + 1),
                'link_resmi' => 'https://contoh.example/usulan-'.($i + 1),
                'deskripsi' => 'Data contoh.',
                'status' => $status,
                'lomba_id' => $indeksLomba !== null ? $lomba[$indeksLomba]->lomba_id : null,
            ]);
            $u->forceFill(['created_at' => Carbon::now()->subDays($hari), 'updated_at' => Carbon::now()->subDays($hari)])->saveQuietly();
        }

        // [status, status_arsip, hari lalu]
        foreach ([
            ['menunggu_verifikasi', 'belum_ditinjau', 1], ['menunggu_verifikasi', 'belum_ditinjau', 3],
            ['menunggu_verifikasi', 'belum_ditinjau', 6], ['disetujui', 'menunggu', 8], ['disetujui', 'menunggu', 11],
            ['disetujui', 'disetujui', 24], ['disetujui', 'disetujui', 37], ['ditolak', 'ditolak', 16],
        ] as $i => [$status, $arsip, $hari]) {
            $p = PengajuanPrestasi::create([
                'mahasiswa_id' => $mahasiswa->id,
                'lomba' => '[Demo] Lomba prestasi '.($i + 1),
                'prestasi_hasil' => 'Juara contoh',
                'tanggal_pencapaian' => Carbon::now()->subDays($hari + 5)->toDateString(),
                'peserta_tim' => 'Tim contoh',
                'bukti_pendukung_path' => 'demo/bukti.pdf',
                'keluaran_lomba_path' => 'demo/keluaran.pdf',
                'judul_arsip' => '[Demo] Arsip '.($i + 1),
                'pengalaman_proses' => 'Data contoh.',
                'pembelajaran_saran' => 'Data contoh.',
                'persetujuan_publikasi' => true,
                'status' => $status,
                'status_arsip' => $arsip,
            ]);
            $p->forceFill(['created_at' => Carbon::now()->subDays($hari), 'updated_at' => Carbon::now()->subDays($hari)])->saveQuietly();
        }
    }
}
