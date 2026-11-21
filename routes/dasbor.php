<?php

use App\Http\Controllers\AdminHima\DashboardController;
use App\Http\Controllers\Kaprodi\AktivitasDosenController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ---- SCRUM-33 + SCRUM-35: Dasbor operasional Admin HIMA + dasbor keaktifan dosen Kaprodi ----
// Di-require dari routes/web.php. Role dicek di controller (admin / kaprodi).

Route::middleware('auth')->group(function () {
    Route::get('/admin-hima/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/dashboard', [AktivitasDosenController::class, 'dashboard'])->name('dashboard');
        Route::get('/aktivitas-dosen', [AktivitasDosenController::class, 'index'])->name('aktivitas.index');
        Route::get('/aktivitas-dosen/{dosen}', [AktivitasDosenController::class, 'show'])
            ->whereNumber('dosen')->name('aktivitas.show');
    });
});

// Login khusus development lokal untuk akun Kaprodi.
// (Route /dev/login/{role} di web.php hanya menerima mahasiswa, dosen, dan admin.)
if (app()->environment('local')) {
    Route::get('/dev/login/kaprodi', function () {
        $user = User::where('role', 'kaprodi')->orderBy('id')->firstOrFail();
        Auth::login($user);

        return redirect()->route('kaprodi.dashboard');
    });
}
