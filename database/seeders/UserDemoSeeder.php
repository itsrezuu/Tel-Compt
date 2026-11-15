<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun contoh untuk development lokal.
 * Jalankan: php artisan db:seed --class=UserDemoSeeder
 * Semua akun berpassword: password
 */
class UserDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::unguarded(function (): void {
            User::updateOrCreate(
                ['email' => 'nadia@student.telkomuniversity.ac.id'],
                [
                    'name' => 'Nadia Pramesti',
                    'password' => 'password',
                    'role' => 'mahasiswa',
                    'nim_nip' => '1301260001',
                ]
            );

            User::updateOrCreate(
                ['email' => 'dimas.arunika@telkomuniversity.ac.id'],
                [
                    'name' => 'Dimas Arunika, M.Kom.',
                    'password' => 'password',
                    'role' => 'dosen',
                    'nim_nip' => '20990001',
                ]
            );

            User::updateOrCreate(
                ['email' => 'laras.senandika@telkomuniversity.ac.id'],
                [
                    'name' => 'Laras Senandika, M.T.',
                    'password' => 'password',
                    'role' => 'dosen',
                    'nim_nip' => '20990002',
                ]
            );

            User::updateOrCreate(
                ['email' => 'adminhima@student.telkomuniversity.ac.id'],
                [
                    'name' => 'Admin HIMA',
                    'password' => 'password',
                    'role' => 'admin',
                    'nim_nip' => 'ADMIN001',
                ]
            );
        });
    }
}
