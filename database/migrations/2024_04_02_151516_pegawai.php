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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_role')->constrained('role')->onDelete('cascade');
            $table->string('nama');
            $table->string('alamat');
            $table->string('no_telpn');
            $table->date('tanggal_lahir');
            $table->enum('gender', array('male', 'female'))->default('male');
            $table->double('bonus')->nullable();
            $table->double('gaji')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
