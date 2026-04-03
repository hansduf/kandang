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
        Schema::table('produksi_telur', function (Blueprint $table) {
            $table->unsignedInteger('ayam_mati')->default(0)->after('jumlah_kg');
            $table->text('catatan')->nullable()->after('ayam_mati');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_telur', function (Blueprint $table) {
            $table->dropColumn(['ayam_mati', 'catatan']);
        });
    }
};
