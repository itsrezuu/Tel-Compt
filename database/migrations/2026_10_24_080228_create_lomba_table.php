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
        $table->id('lomba_id'); 
        $table->string('judul_lomba');
        $table->string('penyelenggara');
        $table->string('kategori');
        $table->string('tingkat');
        $table->date('tenggat_pendaftaran');
        $table->text('link_pendaftaran');
        
        
        $table->enum('jenis_pendampingan', ['tanpa pendampingan', 'konsultasi ringan', 'pembimbing resmi wajib']); 
        
        $table->string('status_publikasi')->default('pending');
        
        
        $table->unsignedBigInteger('created_by')->nullable(); 
        
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
