<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_prestasi', function (Blueprint $table) {
            $table->string('status_arsip')->default('belum_ditinjau');
            $table->boolean('bukti_sesuai')->default(false);
            $table->boolean('keluaran_lomba_tersedia')->default(false);
            $table->boolean('pengalaman_layak')->default(false);
            $table->text('catatan_keputusan')->nullable();
            $table->boolean('publikasikan_prestasi')->default(false);
            $table->boolean('publikasikan_arsip')->default(false);
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->index(['status', 'created_at']);
            $table->index(['status_arsip', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_prestasi', function (Blueprint $table) {
            $table->dropForeign(['ditinjau_oleh']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['status_arsip', 'created_at']);
            $table->dropColumn([
                'status_arsip', 'bukti_sesuai', 'keluaran_lomba_tersedia', 'pengalaman_layak',
                'catatan_keputusan', 'publikasikan_prestasi', 'publikasikan_arsip', 'ditinjau_oleh', 'ditinjau_pada',
            ]);
        });
    }
};
