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
        Schema::table('detail_penjualan', function (Blueprint $table) {
            // Add columns to store the price at the time of sale
            $table->decimal('harga_per_butir_saat_jual', 12, 2)->nullable()->after('harga_satuan');
            $table->decimal('harga_per_kg_saat_jual', 12, 2)->nullable()->after('harga_per_butir_saat_jual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropColumn(['harga_per_butir_saat_jual', 'harga_per_kg_saat_jual']);
        });
    }
};
