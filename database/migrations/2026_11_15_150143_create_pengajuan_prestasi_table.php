<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_prestasi', function (Blueprint $table) {
            $table->id('pengajuan_id');
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('lomba');
            $table->string('prestasi_hasil');
            $table->date('tanggal_pencapaian');
            $table->string('peserta_tim');
            $table->string('bukti_pendukung_path');
            $table->string('keluaran_lomba_path');
            $table->string('judul_arsip');
            $table->text('pengalaman_proses');
            $table->text('pembelajaran_saran');
            $table->string('dokumen_pengalaman_path')->nullable();
            $table->boolean('persetujuan_publikasi')->default(false);
            $table->string('status')->default('menunggu_verifikasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_prestasi');
    }
};
