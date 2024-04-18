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
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_saldo')->constrained('saldo')->onDelete('cascade');
            $table->string('nama');
            $table->string('password');
            $table->string('email')->unique();
            $table->string('no_telpn');
            $table->date('tanggal_lahir');
            $table->enum('gender', array('male', 'female'))->nullable();
            $table->string('poin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
