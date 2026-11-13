<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_lomba', function (Blueprint $table) {
            $table->id('usulan_id');

            // Siapa yang mengusulkan (users.id). Sengaja tanpa foreign key
            // supaya tidak error kalau tabel users milik tim berbeda.
            $table->unsignedBigInteger('pengaju_id')->nullable()->index();

            $table->string('judul_lomba');
            $table->text('link_resmi');
            $table->text('deskripsi');

            // menunggu -> diproses -> dipublikasikan / ditolak
            $table->string('status')->default('menunggu')->index();
            $table->text('catatan_admin')->nullable();

            // Terisi saat usulan dipublikasikan menjadi baris di tabel lomba
            $table->unsignedBigInteger('lomba_id')->nullable();
            $table->unsignedBigInteger('diproses_oleh')->nullable();

            $table->timestamps();

            // Kalau lomba dihapus di Kelola Lomba, usulan tetap aman (lomba_id jadi NULL)
            $table->foreign('lomba_id')->references('lomba_id')->on('lomba')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_lomba');
    }
};