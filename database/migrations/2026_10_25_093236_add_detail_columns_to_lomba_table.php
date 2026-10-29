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
        Schema::table('lomba', function (Blueprint $table) {
            $table->date('pendaftaran_dibuka')->nullable()->after('tingkat');
            $table->date('batas_pengumpulan')->nullable()->after('tenggat_pendaftaran');
            $table->date('tanggal_final')->nullable()->after('batas_pengumpulan');
            $table->text('deskripsi')->nullable()->after('link_pendaftaran');
        });
    }

    public function down(): void
    {
        Schema::table('lomba', function (Blueprint $table) {
            $table->dropColumn(['pendaftaran_dibuka', 'batas_pengumpulan', 'tanggal_final', 'deskripsi']);
        });
    }
};
