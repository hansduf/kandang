<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // Seed all data (kandang, users, production, sales, pricing)
        $this->call([
            UserSeeder::class,
        ]);

        // Seed production data (Jan 1 - Apr 7, 2026)
        $this->call([
            ProductionDataSeeder::class,
        ]);

        // Seed price data (Apr 1 - Apr 7, 2026)
        $this->call([
            PriceDataSeeder::class,
        ]);

        // Seed sales data (Jan 1 - Apr 7, 2026)
        $this->call([
            PenjualanTelurSeeder::class,
        ]);

        // Seed default settings only
        // (kandang, users, production, sales, pricing are all handled by UserSeeder)
        \App\Models\Pengaturan::create([
            'kunci' => 'konversi_butir_per_kg',
            'nilai' => '16',
            'tipe_data' => 'integer',
            'keterangan' => 'Default konversi: 16 butir = 1 kg',
        ]);
    }
}
