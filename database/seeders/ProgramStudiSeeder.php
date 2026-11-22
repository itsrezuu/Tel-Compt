<?php

namespace Database\Seeders;

use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Bandung' => [
                'S1' => [
                    'Teknik Telekomunikasi',
                    'Teknik Elektro',
                    'Smart Science & Technology',
                    'Teknik Komputer',
                    'Teknik Biomedis',
                    'Electrical Energy Engineering',
                    'Informatika',
                    'Data Sains',
                    'Teknologi Informasi',
                    'Rekayasa Perangkat Lunak',
                    'Teknik Industri',
                    'Sistem Informasi',
                    'Digital Supply Chain',
                    'Manajemen Rekayasa Industri',
                    'Manajemen Bisnis Telekomunikasi & Informatika',
                    'Akuntansi',
                    'Administrasi Bisnis',
                    'Manajemen Bisnis Rekreasi',
                    'Digital Business',
                    'Ilmu Komunikasi',
                    'Digital Public Relation',
                    'Digital Content Broadcasting',
                    'Psikologi',
                    'Desain Komunikasi Visual',
                    'Desain Produk & Inovasi',
                    'Desain Interior',
                    'Creative Arts (Intermedia Visual Arts)',
                    'Kriya Fashion & Textile Design',
                    'Film & Animasi',
                ],
                'D3' => [
                    'Teknik Telekomunikasi',
                    'Teknik Informatika',
                    'Teknik Komputer',
                    'Sistem Informasi',
                    'Sistem Informasi Akuntansi',
                    'Hospitality & Culinary Art',
                    'Digital Marketing',
                ],
                'D4' => [
                    'Digital Creative Multimedia',
                    'Sistem Informasi Kota Cerdas',
                ],
            ],

            'Jakarta' => [
                'S1' => [
                    'Teknik Telekomunikasi',
                    'Teknologi Informasi',
                    'Sistem Informasi',
                    'Desain Komunikasi Visual',
                ],
            ],

            'Surabaya' => [
                'S1' => [
                    'Sistem Informasi',
                    'Teknologi Informasi',
                    'Informatika',
                    'Rekayasa Perangkat Lunak',
                    'Data Sains',
                    'Teknik Elektro',
                    'Teknik Industri',
                    'Teknik Komputer',
                    'Digital Supply Chain',
                    'Teknik Telekomunikasi',
                    'Digital Business',
                ],
            ],

            'Purwokerto' => [
                'S1' => [
                    'Teknik Telekomunikasi',
                    'Teknik Elektro',
                    'Teknik Biomedis',
                    'Rekayasa Perangkat Lunak',
                    'Informatika',
                    'Sistem Informasi',
                    'Data Sains',
                    'Teknik Industri',
                    'Teknologi Pangan',
                    'Desain Komunikasi Visual',
                    'Desain Produk',
                    'Digital Business',
                    'Digital Supply Chain',
                ],
                'D3' => [
                    'Teknik Telekomunikasi',
                ],
            ],
        ];

        foreach ($data as $kampus => $jenjangList) {
            foreach ($jenjangList as $jenjang => $daftarProdi) {
                foreach ($daftarProdi as $namaProdi) {
                    ProgramStudi::firstOrCreate([
                        'nama_prodi' => $namaProdi,
                        'jenjang' => $jenjang,
                        'kampus' => $kampus,
                    ]);
                }
            }
        }
    }
}
