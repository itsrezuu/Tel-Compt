<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('basis_pengetahuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('judul');
            $table->string('kategori', 100);
            $table->string('lomba')->nullable();
            $table->string('kontributor')->nullable();
            $table->string('ringkasan', 500);
            $table->longText('isi');
            $table->string('status')->default('draf');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basis_pengetahuan');
    }
};
