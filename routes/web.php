<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('dosen')->name('dosen.')->group(function () {

    Route::view('/profil-keahlian', 'dosen.profil-keahlian')
        ->name('profil-keahlian');

});
