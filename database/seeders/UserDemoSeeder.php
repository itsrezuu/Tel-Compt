<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun contoh untuk development lokal SCRUM-28.
 * Jalankan: php artisan db:seed --class=UserDemoSeeder
 * Semua akun berpassword: password
 */
class UserDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::unguarded(function () {
            User::firstOrCreate(
                ['email' => 'nadia@student.telkomuniversity.ac.id'],
                ['name' => 'Nadia Pramesti', 'password' => 'password', 'role' => 'mahasiswa', 'nim_nip' => '1301260001']
            );

            User::firstOrCreate(
                ['email' => 'dimas.arunika@telkomuniversity.ac.id'],
                ['name' => 'Dimas Arunika, M.Kom.', 'password' => 'password', 'role' => 'dosen', 'nim_nip' => '20990001']
            );

            User::firstOrCreate(
                ['email' => 'laras.senandika@telkomuniversity.ac.id'],
                ['name' => 'Laras Senandika, M.T.', 'password' => 'password', 'role' => 'dosen', 'nim_nip' => '20990002']
            );
        });
    }
}