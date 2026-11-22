<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

/**
 * Akun contoh untuk development lokal.
 * Jalankan: php artisan db:seed --class=UserDemoSeeder
 * Semua akun berpassword: password
 */
class UserDemoSeeder extends Seeder
{
    /**
     * @var list<array{name: string, email: string, nim_nip: string}>
     */
    private const MAHASISWA = [
        ['name' => 'Nadia Pramesti', 'email' => 'nadia@student.telkomuniversity.ac.id', 'nim_nip' => '1301260001'],
        ['name' => 'Aditya Ramadhan', 'email' => 'aditya@student.telkomuniversity.ac.id', 'nim_nip' => '1301260002'],
        ['name' => 'Aulia Safitri', 'email' => 'aulia@student.telkomuniversity.ac.id', 'nim_nip' => '1301260003'],
        ['name' => 'Bagas Mahendra', 'email' => 'bagas@student.telkomuniversity.ac.id', 'nim_nip' => '1301260004'],
        ['name' => 'Citra Lestari', 'email' => 'citra@student.telkomuniversity.ac.id', 'nim_nip' => '1301260005'],
        ['name' => 'Daffa Pratama', 'email' => 'daffa@student.telkomuniversity.ac.id', 'nim_nip' => '1301260006'],
        ['name' => 'Dinda Maharani', 'email' => 'dinda@student.telkomuniversity.ac.id', 'nim_nip' => '1301260007'],
        ['name' => 'Farhan Akbar', 'email' => 'farhan@student.telkomuniversity.ac.id', 'nim_nip' => '1301260008'],
        ['name' => 'Gita Anggraini', 'email' => 'gita@student.telkomuniversity.ac.id', 'nim_nip' => '1301260009'],
        ['name' => 'Ilham Nugraha', 'email' => 'ilham@student.telkomuniversity.ac.id', 'nim_nip' => '1301260010'],
        ['name' => 'Intan Permata', 'email' => 'intan@student.telkomuniversity.ac.id', 'nim_nip' => '1301260011'],
        ['name' => 'Kevin Alfarizi', 'email' => 'kevin@student.telkomuniversity.ac.id', 'nim_nip' => '1301260012'],
        ['name' => 'Luthfi Kurniawan', 'email' => 'luthfi@student.telkomuniversity.ac.id', 'nim_nip' => '1301260013'],
        ['name' => 'Maya Puspitasari', 'email' => 'maya@student.telkomuniversity.ac.id', 'nim_nip' => '1301260014'],
        ['name' => 'Naufal Hidayat', 'email' => 'naufal@student.telkomuniversity.ac.id', 'nim_nip' => '1301260015'],
        ['name' => 'Putri Amelia', 'email' => 'putri@student.telkomuniversity.ac.id', 'nim_nip' => '1301260016'],
        ['name' => 'Raka Bimantara', 'email' => 'raka@student.telkomuniversity.ac.id', 'nim_nip' => '1301260017'],
        ['name' => 'Salsabila Putri', 'email' => 'salsabila@student.telkomuniversity.ac.id', 'nim_nip' => '1301260018'],
        ['name' => 'Tegar Prakoso', 'email' => 'tegar@student.telkomuniversity.ac.id', 'nim_nip' => '1301260019'],
        ['name' => 'Zahra Fadhilah', 'email' => 'zahra@student.telkomuniversity.ac.id', 'nim_nip' => '1301260020'],
    ];

    /**
     * @var list<array{name: string, email: string, nim_nip: string}>
     */
    private const DOSEN = [
        ['name' => 'Dimas Arunika, M.Kom.', 'email' => 'dimasarunika@telkomuniversity.ac.id', 'nim_nip' => '20990001'],
        ['name' => 'Laras Senandika, M.T.', 'email' => 'larassenandika@telkomuniversity.ac.id', 'nim_nip' => '20990002'],
        ['name' => 'Agus Santoso, M.Kom.', 'email' => 'agussantoso@telkomuniversity.ac.id', 'nim_nip' => '20990003'],
        ['name' => 'Budi Hartono, M.T.', 'email' => 'budihartono@telkomuniversity.ac.id', 'nim_nip' => '20990004'],
        ['name' => 'Citra Wulandari, M.Kom.', 'email' => 'citrawulandari@telkomuniversity.ac.id', 'nim_nip' => '20990005'],
        ['name' => 'Dewi Anggraini, M.T.', 'email' => 'dewianggraini@telkomuniversity.ac.id', 'nim_nip' => '20990006'],
        ['name' => 'Eko Prasetyo, M.Kom.', 'email' => 'ekoprasetyo@telkomuniversity.ac.id', 'nim_nip' => '20990007'],
        ['name' => 'Fajar Nugroho, M.T.', 'email' => 'fajarnugroho@telkomuniversity.ac.id', 'nim_nip' => '20990008'],
        ['name' => 'Galih Permana, M.Kom.', 'email' => 'galihpermana@telkomuniversity.ac.id', 'nim_nip' => '20990009'],
        ['name' => 'Hani Pertiwi, M.T.', 'email' => 'hanipertiwi@telkomuniversity.ac.id', 'nim_nip' => '20990010'],
        ['name' => 'Indra Saputra, M.Kom.', 'email' => 'indrasaputra@telkomuniversity.ac.id', 'nim_nip' => '20990011'],
        ['name' => 'Joko Wirawan, M.T.', 'email' => 'jokowirawan@telkomuniversity.ac.id', 'nim_nip' => '20990012'],
        ['name' => 'Kartika Sari, M.Kom.', 'email' => 'kartikasari@telkomuniversity.ac.id', 'nim_nip' => '20990013'],
        ['name' => 'Laila Kusuma, M.T.', 'email' => 'lailakusuma@telkomuniversity.ac.id', 'nim_nip' => '20990014'],
        ['name' => 'Miko Setiawan, M.Kom.', 'email' => 'mikosetiawan@telkomuniversity.ac.id', 'nim_nip' => '20990015'],
        ['name' => 'Nanda Putri, M.T.', 'email' => 'nandaputri@telkomuniversity.ac.id', 'nim_nip' => '20990016'],
        ['name' => 'Oki Wibowo, M.Kom.', 'email' => 'okiwibowo@telkomuniversity.ac.id', 'nim_nip' => '20990017'],
        ['name' => 'Rina Maharani, M.T.', 'email' => 'rinamaharani@telkomuniversity.ac.id', 'nim_nip' => '20990018'],
        ['name' => 'Suryo Wicaksono, M.Kom.', 'email' => 'suryowicaksono@telkomuniversity.ac.id', 'nim_nip' => '20990019'],
        ['name' => 'Tia Oktaviani, M.T.', 'email' => 'tiaoktaviani@telkomuniversity.ac.id', 'nim_nip' => '20990020'],
    ];

    public function run(): void
    {
        $this->renameLegacyLecturerEmails();

        User::unguarded(function (): void {
            foreach (self::MAHASISWA as $mahasiswa) {
                User::updateOrCreate(
                    ['email' => $mahasiswa['email']],
                    [
                        'name' => $mahasiswa['name'],
                        'password' => 'password',
                        'role' => 'mahasiswa',
                        'nim_nip' => $mahasiswa['nim_nip'],
                    ],
                );
            }

            foreach (self::DOSEN as $dosen) {
                User::updateOrCreate(
                    ['email' => $dosen['email']],
                    [
                        'name' => $dosen['name'],
                        'password' => 'password',
                        'role' => 'dosen',
                        'nim_nip' => $dosen['nim_nip'],
                    ],
                );
            }

            User::updateOrCreate(
                ['email' => 'adminhima@student.telkomuniversity.ac.id'],
                [
                    'name' => 'Admin HIMA',
                    'password' => 'password',
                    'role' => 'admin',
                    'nim_nip' => 'ADMIN001',
                ],
            );
        });
    }

    private function renameLegacyLecturerEmails(): void
    {
        foreach ([
            'dimas.arunika@telkomuniversity.ac.id' => 'dimasarunika@telkomuniversity.ac.id',
            'laras.senandika@telkomuniversity.ac.id' => 'larassenandika@telkomuniversity.ac.id',
        ] as $legacyEmail => $email) {
            $legacyUser = User::query()->where('email', $legacyEmail)->first();

            if ($legacyUser === null) {
                continue;
            }

            $existingUser = User::query()->where('email', $email)->first();

            if ($existingUser !== null && (int) $existingUser->getKey() !== (int) $legacyUser->getKey()) {
                throw new LogicException('Akun dosen '.$legacyEmail.' dan '.$email.' sudah sama-sama ada; rekonsiliasi diperlukan.');
            }

            $legacyUser->forceFill(['email' => $email])->save();
        }
    }
}
