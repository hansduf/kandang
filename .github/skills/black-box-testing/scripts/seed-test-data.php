<?php
/**
 * seed-test-data.php - Create test fixtures for black box testing
 * 
 * Usage:
 *   php artisan tinker
 *   > include 'path/to/seed-test-data.php'
 *   > seedTestScenario('stock_testing')
 */

use App\Models\User;
use App\Models\Kandang;
use App\Models\ProduksiTelur;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\HargaTelur;

class TestDataSeeder
{
    /**
     * Seed a complete test scenario with all required entities
     */
    public static function seedStockTestingScenario()
    {
        echo "🌱 Seeding stock testing scenario...\n";

        // 1. Create owner and worker users
        $owner = User::factory()->create(['email' => 'owner@test.local']);
        $owner->assignRole('pemilik');
        
        $worker = User::factory()->create(['email' => 'worker@test.local']);
        $worker->assignRole('pekerja');

        echo "✅ Created users: owner={$owner->id}, worker={$worker->id}\n";

        // 2. Create candangs (coops)
        $kandang1 = Kandang::factory()->create([
            'nama_kandang' => 'Test Kandang A',
            'pic_id' => $owner->id,
            'kapasitas' => 1000,
        ]);
        
        $kandang2 = Kandang::factory()->create([
            'nama_kandang' => 'Test Kandang B',
            'pic_id' => $worker->id,
            'kapasitas' => 500,
        ]);

        echo "✅ Created kandang: {$kandang1->id}, {$kandang2->id}\n";

        // 3. Create production records
        $produksi1 = ProduksiTelur::factory()->create([
            'kandang_id' => $kandang1->id,
            'jumlah_butir' => 500,
            'jumlah_kg' => 31.25,
            'hdp' => 95,
        ]);

        $produksi2 = ProduksiTelur::factory()->create([
            'kandang_id' => $kandang1->id,
            'jumlah_butir' => 300,
            'jumlah_kg' => 18.75,
            'hdp' => 90,
        ]);

        echo "✅ Created production: {$produksi1->id}, {$produksi2->id}\n";

        // 4. Create pricing
        $price = HargaTelur::factory()->create([
            'harga_per_butir' => 500,
            'harga_per_kg' => 8000,
            'status' => 'aktif',
        ]);

        echo "✅ Created pricing: {$price->id}\n";

        // 5. Create sales transactions
        $sale = Penjualan::create([
            'user_id' => $owner->id,
            'pembeli' => 'Test Buyer',
            'tanggal_penjualan' => now(),
            'total' => 100000,
        ]);

        DetailPenjualan::create([
            'penjualan_id' => $sale->id,
            'harga_telur_id' => $price->id,
            'jumlah_butir' => 100,
            'jumlah_kg' => 6.25,
            'harga_satuan' => 500,
            'subtotal' => 50000,
        ]);

        echo "✅ Created sales: {$sale->id}\n";

        echo "\n📊 Test Scenario Summary:\n";
        echo "   Owner: {$owner->email}\n";
        echo "   Worker: {$worker->email}\n";
        echo "   Kandang A (ID={$kandang1->id}): 800 eggs remaining\n";
        echo "   Kandang B (ID={$kandang2->id}): No production\n";

        return [
            'owner' => $owner,
            'worker' => $worker,
            'kandang1' => $kandang1,
            'kandang2' => $kandang2,
            'price' => $price,
        ];
    }

    /**
     * Seed permission testing scenario
     */
    public static function seedPermissionTestingScenario()
    {
        echo "🌱 Seeding permission testing scenario...\n";

        $owner = User::factory()->create(['email' => 'pemilik@test.local']);
        $owner->assignRole('pemilik');
        
        $worker = User::factory()->create(['email' => 'pekerja@test.local']);
        $worker->assignRole('pekerja');

        // Create resources that will be tested for access
        $kandang = Kandang::factory()->create(['pic_id' => $owner->id]);
        $price = HargaTelur::factory()->create();

        echo "✅ Permission scenario ready\n";
        echo "   Owner can: CRUD kandang, pricing, users\n";
        echo "   Worker can: VIEW kandang, CREATE produksi, VIEW pricing\n";

        return ['owner' => $owner, 'worker' => $worker, 'kandang' => $kandang, 'price' => $price];
    }

    /**
     * Reset database to clean state
     */
    public static function resetDatabase()
    {
        echo "🔄 Resetting database...\n";
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
        echo "✅ Database reset complete\n";
    }

    /**
     * Cleanup test data
     */
    public static function cleanup()
    {
        echo "🧹 Cleaning up test data...\n";
        User::whereIn('email', ['owner@test.local', 'worker@test.local', 'pemilik@test.local', 'pekerja@test.local'])->delete();
        echo "✅ Cleanup complete\n";
    }
}

// Provide shortcut functions for Tinker
if (!function_exists('seedStockTesting')) {
    function seedStockTesting() {
        return TestDataSeeder::seedStockTestingScenario();
    }
}

if (!function_exists('seedPermissionTesting')) {
    function seedPermissionTesting() {
        return TestDataSeeder::seedPermissionTestingScenario();
    }
}

if (!function_exists('resetDb')) {
    function resetDb() {
        return TestDataSeeder::resetDatabase();
    }
}

if (!function_exists('cleanupTest')) {
    function cleanupTest() {
        return TestDataSeeder::cleanup();
    }
}

echo "✅ Test data seeder loaded\n";
echo "   Available functions: seedStockTesting(), seedPermissionTesting(), resetDb(), cleanupTest()\n";
