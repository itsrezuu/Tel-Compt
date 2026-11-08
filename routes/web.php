<?php

use App\Http\Controllers\Mahasiswa\LombaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth' /*, 'role:mahasiswa' */])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/lomba', [LombaController::class, 'index'])->name('lomba.index');
        Route::get('/lomba/{lomba}', [LombaController::class, 'show'])->name('lomba.show');
        Route::post('/lomba/{lomba}/simpan', [LombaController::class, 'simpan'])->name('lomba.simpan');
    });

    Route::get('/login-tes', function () {
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'mhs@test.com'],
        ['name' => 'Nadia Pramesti', 'password' => bcrypt('password')]
    );
    auth()->login($user);
    return redirect()->route('mahasiswa.lomba.index');
})->name('login');
