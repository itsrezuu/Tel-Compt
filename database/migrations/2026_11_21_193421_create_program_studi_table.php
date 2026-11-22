
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_prodi', 150);
            $table->string('jenjang', 20);
            $table->string('kampus', 50);
            $table->timestamps();

            $table->unique(
                ['nama_prodi', 'jenjang', 'kampus'],
                'program_studi_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};
