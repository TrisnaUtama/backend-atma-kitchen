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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penitip')->constrained('penitip')->nullable();
            $table->foreignId('id_resep')->constrained('resep')->nullable();
            $table->date('tanggal_penitipan')->nullable();
            $table->string('nama_produk');
            $table->string('gambar');
            $table->string('deskripsi');
            $table->enum('kategori', array('Cake','Roti','Minuman','Titipan'));
            $table->double('harga');
            $table->double('stok');
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
