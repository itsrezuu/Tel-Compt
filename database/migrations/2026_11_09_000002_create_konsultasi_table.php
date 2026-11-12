<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konsultasi = bagian dari bimbingan. Hanya mahasiswa yang bimbingannya sudah
 * disetujui yang bisa mengirim konsultasi, dan selalu ke pembimbingnya.
 * (Tabel konsultasi lama sudah di-drop oleh migration create_bimbingan_table.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('konsultasi');

        Schema::create('konsultasi', function (Blueprint $table) {
            $table->id('konsultasi_id');
            $table->unsignedBigInteger('bimbingan_id');
            $table->foreign('bimbingan_id')->references('bimbingan_id')->on('bimbingan')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('users');
            $table->foreignId('dosen_id')->constrained('users'); // = pembimbing pada bimbingan terkait

            $table->text('pertanyaan');
            $table->string('status')->default('menunggu'); // menunggu | ditanggapi | ditolak
            $table->text('jawaban_arahan')->nullable();    // tanggapan atau alasan penolakan
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['bimbingan_id', 'status']);
            $table->index(['dosen_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi');
    }
};
