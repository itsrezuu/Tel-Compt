<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dosen\DashboardController;
use App\Http\Controllers\Dosen\RiwayatController;
use App\Http\Controllers\Dosen\ProfilKeahlianController;

Route::prefix('dosen')->name('dosen.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profil-keahlian', [ProfilKeahlianController::class, 'index'])
        ->name('profil-keahlian');

    Route::post('/profil-keahlian', [ProfilKeahlianController::class, 'update'])
        ->name('profil-keahlian.update');

    Route::get('/riwayat', [RiwayatController::class, 'index'])
        ->name('riwayat');

});
