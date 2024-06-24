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
        Schema::create('hampers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_resep')->constrained('resep');
            $table->string('gambar');
            $table->double('harga');
            $table->string('deskripsi');
            $table->string('nama_hampers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hampers');
    }
};
