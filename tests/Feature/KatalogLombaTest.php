<?php

namespace Tests\Feature;

use App\Models\Lomba;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogLombaTest extends TestCase
{
    use RefreshDatabase;

    private function buat(array $o = []): Lomba
    {
        return Lomba::create($o + [
            'judul_lomba'         => 'Lomba Contoh',
            'penyelenggara'       => 'Penyelenggara Contoh',
            'kategori'            => 'Teknologi',
            'tingkat'             => 'Nasional',
            'jenis_pendampingan'  => 'konsultasi ringan',
            'pendaftaran_dibuka'  => now()->subDays(3)->toDateString(),
            'tenggat_pendaftaran' => now()->addDays(10)->toDateString(),
            'deskripsi'           => 'Deskripsi',
            'link_pendaftaran'    => 'https://contoh.example/daftar',
            'status_publikasi'    => 'dipublikasikan',
        ]);
    }

    public function test_katalog_bisa_dibuka_tanpa_login(): void
    {
        $this->buat(['judul_lomba' => 'Lomba Terbuka']);

        $this->get('/mahasiswa/lomba')->assertOk()->assertSee('Lomba Terbuka');
    }

    public function test_hanya_lomba_dipublikasikan_dan_masih_buka_yang_tampil(): void
    {
        $this->buat(['judul_lomba' => 'Tampil']);
        $this->buat(['judul_lomba' => 'Masih Pending', 'status_publikasi' => 'pending']);
        $this->buat(['judul_lomba' => 'Sudah Lewat', 'tenggat_pendaftaran' => now()->subDay()->toDateString()]);

        $this->get('/mahasiswa/lomba')
            ->assertSee('Tampil')
            ->assertDontSee('Masih Pending')
            ->assertDontSee('Sudah Lewat');
    }

    public function test_pencarian_berdasarkan_judul_dan_penyelenggara(): void
    {
        $this->buat(['judul_lomba' => 'Hackathon Kota', 'penyelenggara' => 'Lab A']);
        $this->buat(['judul_lomba' => 'Desain Poster', 'penyelenggara' => 'Studio Biru']);

        $this->get('/mahasiswa/lomba?q=Hackathon')->assertSee('Hackathon Kota')->assertDontSee('Desain Poster');
        $this->get('/mahasiswa/lomba?q=Biru')->assertSee('Desain Poster')->assertDontSee('Hackathon Kota');
    }

    public function test_filter_kategori_tingkat_dan_pendampingan(): void
    {
        $this->buat(['judul_lomba' => 'A', 'kategori' => 'Desain', 'tingkat' => 'Regional', 'jenis_pendampingan' => 'pembimbing resmi wajib']);
        $this->buat(['judul_lomba' => 'B', 'kategori' => 'Bisnis', 'tingkat' => 'Nasional', 'jenis_pendampingan' => 'tanpa pendampingan']);

        $this->get('/mahasiswa/lomba?kategori=Desain')->assertSee('1 lomba tersedia');
        $this->get('/mahasiswa/lomba?tingkat=Nasional')->assertSee('1 lomba tersedia');
        $this->get('/mahasiswa/lomba?' . http_build_query(['pendampingan' => 'pembimbing resmi wajib']))->assertSee('1 lomba tersedia');
        $this->get('/mahasiswa/lomba?kategori=Desain&tingkat=Nasional')->assertSee('Belum ada lomba yang cocok');
    }

    public function test_nilai_filter_tidak_valid_ditolak(): void
    {
        $this->get('/mahasiswa/lomba?kategori=Ngawur')->assertSessionHasErrors('kategori');
    }

    public function test_detail_menampilkan_tautan_pendaftaran(): void
    {
        $lomba = $this->buat();

        $this->get('/mahasiswa/lomba/' . $lomba->getKey())
            ->assertOk()
            ->assertSee('https://contoh.example/daftar');
    }

    public function test_detail_lomba_belum_dipublikasikan_tidak_ditemukan(): void
    {
        $lomba = $this->buat(['status_publikasi' => 'pending']);

        $this->get('/mahasiswa/lomba/' . $lomba->getKey())->assertNotFound();
    }

    public function test_simpan_lomba_bisa_dinyalakan_dan_dimatikan(): void
    {
        $lomba = $this->buat();
        $url = '/mahasiswa/lomba/' . $lomba->getKey() . '/simpan';

        $this->post($url)->assertSessionHas('lomba_tersimpan', [(int) $lomba->getKey()]);
        $this->post($url)->assertSessionHas('lomba_tersimpan', []);
    }
}
