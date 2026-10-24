<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konsultasi', function (Blueprint $table) {
            $table->id('konsultasi_id');
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            // Tanpa foreign key dulu: tabel lomba (SCRUM-23) belum masuk dev.
            $table->unsignedBigInteger('lomba_id')->nullable()->index();
            $table->text('pertanyaan');
            $table->text('jawaban_arahan')->nullable();
            $table->string('status')->default('menunggu'); // menunggu | ditanggapi | ditolak
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsultasi');
    }
};