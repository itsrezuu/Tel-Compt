<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminHima\LombaController; // <-- Jangan lupa use Controller-nya di atas

Route::get('/', function () {
    return view('welcome');
});

// Route khusus Admin HIMA untuk kelola data lomba (CRUD)
Route::prefix('admin-hima')->name('admin.lomba.')->group(function () {
    Route::get('/lomba', [LombaController::class, 'index'])->name('index');
    Route::get('/lomba/create', [LombaController::class, 'create'])->name('create');
    Route::post('/lomba', [LombaController::class, 'store'])->name('store');
    Route::get('/lomba/{lomba}/edit', [LombaController::class, 'edit'])->name('edit');
    Route::put('/lomba/{lomba}', [LombaController::class, 'update'])->name('update');
    Route::delete('/lomba/{lomba}', [LombaController::class, 'destroy'])->name('destroy');
});
