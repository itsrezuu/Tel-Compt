<?php

use Illuminate\Support\Facades\Route;

// ---- SCRUM-28: Konsultasi ringan ----
Route::get('/konsultasi', [\App\Http\Controllers\KonsultasiController::class, 'index'])->name('konsultasi.index');
Route::get('/konsultasi/buat', [\App\Http\Controllers\KonsultasiController::class, 'create'])->name('konsultasi.create');
Route::post('/konsultasi', [\App\Http\Controllers\KonsultasiController::class, 'store'])->name('konsultasi.store');
Route::get('/dosen/konsultasi', [\App\Http\Controllers\KonsultasiController::class, 'dosenIndex'])->name('dosen.konsultasi.index');

// LOGIN SEMENTARA untuk development lokal. HAPUS setelah login asli jadi.
if (app()->environment('local')) {
    Route::get('/dev/login/{role}', function (string $role) {
        $user = \App\Models\User::where('role', $role)->orderBy('id')->firstOrFail();
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect($role === 'mahasiswa' ? '/konsultasi' : '/dosen/konsultasi');
    })->whereIn('role', ['mahasiswa', 'dosen']);
}
