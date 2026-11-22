
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lomba_bookmarks', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('lomba_id');

            $table->foreign('lomba_id')
                ->references('lomba_id')
                ->on('lomba')
                ->cascadeOnDelete();

            $table->primary(['user_id', 'lomba_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lomba_bookmarks');
    }
};
