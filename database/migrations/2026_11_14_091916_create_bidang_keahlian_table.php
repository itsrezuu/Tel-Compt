<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidang_keahlian', function (Blueprint $table) {
            $table->id('bidang_keahlian_id');
            $table->string('nama_bidang', 100)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang_keahlian');
    }
};
