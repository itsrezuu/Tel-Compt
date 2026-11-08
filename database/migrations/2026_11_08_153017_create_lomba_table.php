<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lomba', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('penyelenggara');
            $table->string('kategori');
            $table->string('tingkat');
            $table->string('jenis_pendampingan');          // formal, opsional, tanpa
            $table->date('batas_pendaftaran');
            $table->text('deskripsi')->nullable();
            $table->string('link_pendaftaran');
            $table->string('status')->default('pending');  // pending, published, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lomba');
    }
};
