<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminHima\LombaController;

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
