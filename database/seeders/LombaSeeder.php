<?php

namespace Database\Seeders;

use App\Models\Lomba;
use Illuminate\Database\Seeder;

class LombaSeeder extends Seeder
{
    /** Data fiktif untuk mencoba katalog. Aman dijalankan berulang. */
    public function run(): void
    {
        $data = [
            // judul, penyelenggara, kategori, tingkat, pendampingan, hari ke tenggat, host, status
            ['Kompetisi Desain Aksesibel 2026', 'Komunitas Reka Akses (fiktif)', 'Desain', 'Nasional', 'konsultasi ringan', 22, 'reka.example', 'dipublikasikan'],
            ['Tantangan Data Kampus 2026', 'Forum Data Muda (fiktif)', 'Teknologi', 'Regional', 'konsultasi ringan', 25, 'data.example', 'dipublikasikan'],
            ['Gagasan Bisnis Berkelanjutan', 'Komunitas Usaha Muda (fiktif)', 'Bisnis', 'Nasional', 'pembimbing resmi wajib', 32, 'usaha.example', 'dipublikasikan'],
            ['Lomba Karya Tulis Ilmiah Nasional', 'Asosiasi Peneliti Muda (fiktif)', 'Karya tulis', 'Nasional', 'pembimbing resmi wajib', 40, 'kti.example', 'dipublikasikan'],
            ['Hackathon Kota Cerdas', 'Smart City Lab (fiktif)', 'Teknologi', 'Internasional', 'tanpa pendampingan', 45, 'hack.example', 'dipublikasikan'],
            ['Kompetisi Poster Internal Kampus', 'BEM Fakultas (fiktif)', 'Desain', 'Internal kampus', 'tanpa pendampingan', 60, 'bem.example', 'dipublikasikan'],
            // Tidak tampil di katalog: belum dipublikasikan dan sudah lewat tenggat.
            ['Usulan Lomba Belum Dikurasi', 'Penyelenggara Baru (fiktif)', 'Desain', 'Regional', 'tanpa pendampingan', 30, 'baru.example', 'pending'],
            ['Lomba Poster Digital (Sudah Berakhir)', 'Studio Visual (fiktif)', 'Desain', 'Regional', 'tanpa pendampingan', -5, 'poster.example', 'dipublikasikan'],
        ];

        foreach ($data as [$judul, $penyelenggara, $kategori, $tingkat, $pendampingan, $hari, $host, $status]) {
            Lomba::updateOrCreate(
                ['judul_lomba' => $judul],
                [
                    'penyelenggara'       => $penyelenggara,
                    'kategori'            => $kategori,
                    'tingkat'             => $tingkat,
                    'jenis_pendampingan'  => $pendampingan,
                    'pendaftaran_dibuka'  => now()->subDays(7)->toDateString(),
                    'tenggat_pendaftaran' => now()->addDays($hari)->toDateString(),
                    'deskripsi'           => "Deskripsi singkat untuk {$judul}. Data ini fiktif untuk keperluan pengembangan.",
                    'link_pendaftaran'    => "https://{$host}/daftar",
                    'status_publikasi'    => $status,
                ]
            );
        }
    }
}
