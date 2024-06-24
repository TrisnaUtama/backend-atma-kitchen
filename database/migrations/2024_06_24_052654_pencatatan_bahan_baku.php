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
        Schema::create('pencatatan_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bahan_baku')->constrained('bahan_baku');
            $table->bigInteger('jumlah_terpakai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pencatatan_bahan_baku');
    }
};
