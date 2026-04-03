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
        Schema::create('harga_telur', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_harga', ['kandang', 'grosir', 'konsumen']);
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('harga_per_butir', 12, 2)->nullable();
            $table->date('tanggal_berlaku');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_telur');
    }
};
