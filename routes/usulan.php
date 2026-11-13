<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mahasiswa\UsulanLombaController;
use App\Http\Controllers\AdminHima\UsulanController;

// ---- SCRUM-26: Usulan lomba (mahasiswa) + antrean usulan (Admin HIMA) ----
Route::name('mahasiswa.usulan.')->group(function () {
    Route::get('/usulkan-lomba', [UsulanLombaController::class, 'create'])->name('create');
    Route::post('/usulkan-lomba', [UsulanLombaController::class, 'store'])->name('store');
    Route::get('/usulkan-lomba/riwayat', [UsulanLombaController::class, 'index'])->name('index');
});

Route::prefix('admin-hima')->name('admin.usulan.')->group(function () {
    Route::get('/usulan', [UsulanController::class, 'index'])->name('index');
    Route::get('/usulan/{usulan}', [UsulanController::class, 'show'])->name('show');
    Route::patch('/usulan/{usulan}/proses', [UsulanController::class, 'proses'])->name('proses');
    Route::post('/usulan/{usulan}/publikasikan', [UsulanController::class, 'publikasikan'])->name('publikasikan');
    Route::patch('/usulan/{usulan}/tolak', [UsulanController::class, 'tolak'])->name('tolak');
});