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
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->foreignId('harga_telur_id')->constrained('harga_telur');
            $table->enum('satuan_jual', ['butir', 'kg']);
            $table->decimal('jumlah_jual', 10, 2);
            $table->unsignedInteger('jumlah_butir')->default(0);
            $table->decimal('jumlah_kg', 10, 3)->default(0);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};
