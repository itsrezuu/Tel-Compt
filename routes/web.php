<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminHima\LombaController;
use App\Http\Controllers\Mahasiswa\LombaController as MahasiswaLombaController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1') // batasi 6 percobaan per menit
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin-hima')->name('admin.lomba.')->group(function () {

    Route::get('/lomba', [LombaController::class, 'index'])
        ->name('index');

    Route::get('/lomba/create', [LombaController::class, 'create'])
        ->name('create');

    Route::post('/lomba', [LombaController::class, 'store'])
        ->name('store');

    Route::get('/lomba/{lomba}/edit', [LombaController::class, 'edit'])
        ->name('edit');

    Route::put('/lomba/{lomba}', [LombaController::class, 'update'])
        ->name('update');

    Route::delete('/lomba/{lomba}', [LombaController::class, 'destroy'])
        ->name('destroy');

});

require __DIR__.'/usulan.php';

Route::prefix('dosen')->name('dosen.')->group(function () {

    Route::view('/profil-keahlian', 'dosen.profil-keahlian')
        ->name('profil-keahlian');

});

// ---- SCRUM-24: Katalog lomba mahasiswa (sementara tanpa login) ----
// Saat login sudah ada, tambahkan ->middleware(['auth', 'role:mahasiswa'])
Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/lomba', [MahasiswaLombaController::class, 'index'])->name('lomba.index');
        Route::get('/lomba/{lomba}', [MahasiswaLombaController::class, 'show'])->name('lomba.show');
        Route::post('/lomba/{lomba}/simpan', [MahasiswaLombaController::class, 'simpan'])->name('lomba.simpan');
    });

// ---- SCRUM-28 + SCRUM-29: Bimbingan + konsultasi (route ada di routes/bimbingan.php) ----
require __DIR__.'/bimbingan.php';

// LOGIN SEMENTARA untuk development lokal. HAPUS setelah login asli jadi.
if (app()->environment('local')) {
    Route::get('/dev/login/{role}', function (string $role) {
        $user = \App\Models\User::where('role', $role)->orderBy('id')->firstOrFail();
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect($role === 'mahasiswa' ? '/bimbingan' : '/dosen/permintaan-bimbingan');
    })->whereIn('role', ['mahasiswa', 'dosen']);
}
