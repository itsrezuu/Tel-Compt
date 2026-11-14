<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keahlian_dosen', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('bidang_keahlian_id');

            $table->foreign('bidang_keahlian_id')
                ->references('bidang_keahlian_id')
                ->on('bidang_keahlian')
                ->cascadeOnDelete();

            $table->primary(['user_id', 'bidang_keahlian_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keahlian_dosen');
    }
};
