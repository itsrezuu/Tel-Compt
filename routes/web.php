
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminHima\LombaController;
use App\Http\Controllers\Mahasiswa\LombaController as MahasiswaLombaController;
use App\Http\Controllers\Dosen\ProfilKeahlianController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin-hima')->name('admin.lomba.')->group(function () {
    Route::get('/lomba', [LombaController::class, 'index'])->name('index');
    Route::get('/lomba/create', [LombaController::class, 'create'])->name('create');
    Route::post('/lomba', [LombaController::class, 'store'])->name('store');
    Route::get('/lomba/{lomba}/edit', [LombaController::class, 'edit'])->name('edit');
    Route::put('/lomba/{lomba}', [LombaController::class, 'update'])->name('update');
    Route::delete('/lomba/{lomba}', [LombaController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/usulan.php';

Route::prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/profil-keahlian', [ProfilKeahlianController::class, 'index'])
        ->name('profil-keahlian');

    Route::post('/profil-keahlian', [ProfilKeahlianController::class, 'update'])
        ->name('profil-keahlian.update');
});

Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/lomba', [MahasiswaLombaController::class, 'index'])->name('lomba.index');
        Route::get('/lomba/{lomba}', [MahasiswaLombaController::class, 'show'])->name('lomba.show');
        Route::post('/lomba/{lomba}/simpan', [MahasiswaLombaController::class, 'simpan'])->name('lomba.simpan');
    });

require __DIR__.'/bimbingan.php';

if (app()->environment('local')) {
    Route::get('/dev/login/{role}', function (string $role) {
        $user = \App\Models\User::where('role', $role)->orderBy('id')->firstOrFail();
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect($role === 'mahasiswa' ? '/bimbingan' : '/dosen/permintaan-bimbingan');
    })->whereIn('role', ['mahasiswa', 'dosen']);
}
