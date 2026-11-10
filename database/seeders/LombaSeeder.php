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
            ['Kompetisi Desain Aksesibel 2026', 'Komunitas Reka Akses (fiktif)', 'Desain', 'Nasional', 'opsional', 22, 'reka.example', 'published'],
            ['Tantangan Data Kampus 2026', 'Forum Data Muda (fiktif)', 'Teknologi', 'Regional', 'opsional', 25, 'data.example', 'published'],
            ['Gagasan Bisnis Berkelanjutan', 'Komunitas Usaha Muda (fiktif)', 'Bisnis', 'Nasional', 'formal', 32, 'usaha.example', 'published'],
            ['Lomba Karya Tulis Ilmiah Nasional', 'Asosiasi Peneliti Muda (fiktif)', 'Karya tulis', 'Nasional', 'formal', 40, 'kti.example', 'published'],
            ['Hackathon Kota Cerdas', 'Smart City Lab (fiktif)', 'Teknologi', 'Internasional', 'tanpa', 45, 'hack.example', 'published'],
            ['Kompetisi Riset Energi Terbarukan', 'Lembaga Riset Energi (fiktif)', 'Riset', 'Internasional', 'formal', 60, 'energi.example', 'published'],
            // Tidak tampil di katalog: belum dikurasi dan sudah lewat tenggat.
            ['Usulan Lomba Belum Dikurasi', 'Penyelenggara Baru (fiktif)', 'Desain', 'Regional', 'tanpa', 30, 'baru.example', 'pending'],
            ['Lomba Poster Digital (Sudah Berakhir)', 'Studio Visual (fiktif)', 'Desain', 'Regional', 'tanpa', -5, 'poster.example', 'published'],
        ];

        foreach ($data as [$judul, $penyelenggara, $kategori, $tingkat, $pendampingan, $hari, $host, $status]) {
            Lomba::updateOrCreate(
                ['judul' => $judul],
                [
                    'penyelenggara'      => $penyelenggara,
                    'kategori'           => $kategori,
                    'tingkat'            => $tingkat,
                    'jenis_pendampingan' => $pendampingan,
                    'batas_pendaftaran'  => now()->addDays($hari)->toDateString(),
                    'deskripsi'          => "Deskripsi singkat untuk {$judul}. Data ini fiktif untuk keperluan pengembangan.",
                    'link_pendaftaran'   => "https://{$host}/daftar",
                    'status'             => $status,
                ]
            );
        }
    }
}
