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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['pemilik', 'pekerja'])->after('password');
            $table->foreignId('kandang_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('kandangs')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop columns with conditional check
            if (Schema::hasColumn('users', 'kandang_id')) {
                // Use onDelete('cascade') which will handle the FK constraint automatically on some DB systems
                // so we just drop the column
                $table->dropColumn('kandang_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
