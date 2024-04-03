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
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_customer')->constrained('customer')->onDelete('cascade');
            $table->foreignId('id_penitip')->constrained('penitip')->onDelete('cascade')->nullable();
            $table->timestamp('tanggal_pemesanan')->nullable();
            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->timestamp('tanggal_diambil')->nullable();
            $table->double('jarak_delivery')->nullable();
            $table->double('ongkir')->nullable();
            $table->string('poin_pesanan')->nullable();
            $table->string('potongan_poin')->nullable();
            $table->enum('status_pesanan', array('menunggu pembayaran','diproses','siap di-pickup', 'sedang dikirim', 'sudah di-pickup', 'selesai', 'dibatalkan'))->default('menunggu pembayaran');
            $table->double('uang_customer')->nullable();
            $table->double('tip')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
