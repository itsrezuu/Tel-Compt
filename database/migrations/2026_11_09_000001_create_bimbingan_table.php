<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCRUM-28 + SCRUM-29 digabung: fitur konsultasi dihapus, semua jadi "bimbingan".
 * Migration lama (users.role, konsultasi) TIDAK diubah; tabel konsultasi di-drop di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bimbingan', function (Blueprint $table) {
            $table->id('bimbingan_id');
            $table->foreignId('mahasiswa_id')->constrained('users');
            $table->foreignId('dosen_id')->constrained('users');
            $table->unsignedBigInteger('lomba_id')->nullable(); // tanpa FK dulu, nunggu SCRUM-23

            $table->string('topik_judul');
            $table->text('ringkasan_kebutuhan');
            $table->string('nama_tim');
            $table->string('ketua_tim');
            $table->text('anggota_tim');       // satu anggota per baris
            $table->text('rencana_kegiatan');

            $table->string('status')->default('menunggu'); // menunggu | disetujui | ditolak
            $table->text('catatan_dosen')->nullable();     // catatan setuju / alasan tolak
            $table->boolean('ditolak_otomatis')->default(false);
            $table->timestamp('batas_respons_at');         // created_at + 1 hari
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'status']);
            $table->index(['status', 'batas_respons_at']);
        });

        Schema::dropIfExists('konsultasi');
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan');
    }
};
