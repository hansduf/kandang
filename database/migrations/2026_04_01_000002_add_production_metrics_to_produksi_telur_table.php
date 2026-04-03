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
            $table->unsignedInteger('ayam_hidup')->default(0)->after('catatan');
            $table->decimal('hdp', 5, 2)->default(0)->after('ayam_hidup'); // Hen Day Production
            $table->decimal('hhp', 5, 2)->default(0)->after('hdp');        // Hen House Production
            $table->decimal('mortality', 5, 2)->default(0)->after('hhp');  // Mortality percentage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produksi_telur', function (Blueprint $table) {
            $table->dropColumn(['ayam_hidup', 'hdp', 'hhp', 'mortality']);
        });
    }
};
