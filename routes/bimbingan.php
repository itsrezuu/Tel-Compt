<?php

use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\KonsultasiController;
use Illuminate\Support\Facades\Route;

// ---- SCRUM-28 + SCRUM-29: Bimbingan (gabungan) + konsultasi di dalam bimbingan ----
// Di-require dari routes/web.php, jadi otomatis ikut grup middleware "web".

// Mahasiswa: bimbingan (urutan penting: /bimbingan/buat harus sebelum /bimbingan/{bimbingan})
Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
Route::get('/bimbingan/buat', [BimbinganController::class, 'create'])->name('bimbingan.create');
Route::post('/bimbingan', [BimbinganController::class, 'store'])->name('bimbingan.store');
Route::get('/bimbingan/{bimbingan}', [BimbinganController::class, 'show'])->whereNumber('bimbingan')->name('bimbingan.show');

// Mahasiswa: konsultasi ke pembimbing (hanya bimbingan yang disetujui)
Route::post('/bimbingan/{bimbingan}/konsultasi', [KonsultasiController::class, 'store'])->whereNumber('bimbingan')->name('konsultasi.store');

// Dosen: permintaan bimbingan (URL sama dengan menu di layouts/dosen.blade.php)
Route::get('/dosen/permintaan-bimbingan', [BimbinganController::class, 'dosenIndex'])->name('dosen.bimbingan.index');
Route::get('/dosen/permintaan-bimbingan/{bimbingan}', [BimbinganController::class, 'dosenShow'])->whereNumber('bimbingan')->name('dosen.bimbingan.show');
Route::patch('/dosen/permintaan-bimbingan/{bimbingan}', [BimbinganController::class, 'dosenRespond'])->whereNumber('bimbingan')->name('dosen.bimbingan.respond');

// Dosen: permintaan konsultasi dari mahasiswa bimbingannya
Route::get('/dosen/permintaan-konsultasi', [KonsultasiController::class, 'dosenIndex'])->name('dosen.konsultasi.index');
Route::get('/dosen/permintaan-konsultasi/{konsultasi}', [KonsultasiController::class, 'dosenShow'])->whereNumber('konsultasi')->name('dosen.konsultasi.show');
Route::patch('/dosen/permintaan-konsultasi/{konsultasi}', [KonsultasiController::class, 'dosenRespond'])->whereNumber('konsultasi')->name('dosen.konsultasi.respond');
