<?php

use App\Http\Controllers\AdminHima\LombaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dosen\ProfilKeahlianController;
use App\Http\Controllers\Mahasiswa\LombaController as MahasiswaLombaController;
use App\Http\Controllers\Mahasiswa\PrestasiController as MahasiswaPrestasiController;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
    Route::get('/lomba', [LombaController::class, 'index'])->name('index');
    Route::get('/lomba/create', [LombaController::class, 'create'])->name('create');
    Route::post('/lomba', [LombaController::class, 'store'])->name('store');
    Route::get('/lomba/{lomba}/edit', [LombaController::class, 'edit'])->name('edit');
    Route::put('/lomba/{lomba}', [LombaController::class, 'update'])->name('update');
    Route::delete('/lomba/{lomba}', [LombaController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/usulan.php';

Route::middleware('auth')->group(function () {
    Route::get('/prestasi-arsip', [MahasiswaPrestasiController::class, 'create'])
        ->name('mahasiswa.prestasi.create');
    Route::post('/prestasi-arsip', [MahasiswaPrestasiController::class, 'store'])
        ->name('mahasiswa.prestasi.store');
    Route::get('/riwayat-pengajuan', [MahasiswaPrestasiController::class, 'index'])
        ->name('mahasiswa.prestasi.index');
    Route::get('/riwayat-pengajuan/{pengajuan}/berkas/{jenis}', [MahasiswaPrestasiController::class, 'download'])
        ->whereNumber('pengajuan')
        ->name('mahasiswa.prestasi.berkas');
});

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

Route::prefix('notifikasi')->name('mahasiswa.notifikasi.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
});

Route::prefix('admin-hima/notifikasi')->name('admin-hima.notifikasi.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
});

Route::prefix('dosen/notifikasi')->name('dosen.notifikasi.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
});

// Login khusus development lokal
if (app()->environment('local')) {
    Route::get('/dev/login/{role}', function (string $role) {
        $query = User::where('role', $role);

        if (request()->filled('id')) {
            $query->whereKey(request()->integer('id'));
        }

        $user = $query->orderBy('id')->firstOrFail();
        Auth::login($user);

        return redirect(match ($role) {
            'mahasiswa' => route('bimbingan.index'),
            'dosen' => route('dosen.bimbingan.index'),
            'admin' => route('admin-hima.notifikasi.index'),
        });
    })->whereIn('role', ['mahasiswa', 'dosen', 'admin']);
}
