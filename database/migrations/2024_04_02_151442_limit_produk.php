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
        Schema::create('limit_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_produk')->constrained('produk');
            $table->integer('limit');
            $table->date('tanggal_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limit_produk');
    }
};
