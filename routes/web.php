<?php

use App\Http\Controllers\Mahasiswa\LombaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/lomba', [LombaController::class, 'index'])->name('lomba.index');
        Route::get('/lomba/{lomba}', [LombaController::class, 'show'])->name('lomba.show');
        Route::post('/lomba/{lomba}/simpan', [LombaController::class, 'simpan'])->name('lomba.simpan');
    });
