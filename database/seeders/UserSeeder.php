<?php

namespace Database\Seeders;

use App\Models\Kandang;
use App\Models\User;
use App\Models\ProduksiTelur;
use App\Models\HargaTelur;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ========== CREATE KANDANG ==========
        $kandangA = Kandang::create([
            'nama_kandang' => 'Kandang A - Premium',
            'jumlah_ayam' => 500,
            'keterangan' => 'Kandang utama dengan ayam premium',
            'status' => 'aktif',
        ]);

        $kandangB = Kandang::create([
            'nama_kandang' => 'Kandang B - Standard',
            'jumlah_ayam' => 450,
            'keterangan' => 'Kandang ekspansi untuk produksi standar',
            'status' => 'aktif',
        ]);

        // ========== CREATE USERS ==========
        $pemilik = User::create([
            'name' => 'Hans Jaya',
            'username' => 'hansjaya',
            'email' => 'pemilik@hansjaya.com',
            'password' => bcrypt('password123'),
            'role' => 'pemilik',
            'kandang_id' => null,
            'email_verified_at' => now(),
        ]);
        $pemilik->assignRole('pemilik');

        // Pekerja untuk Kandang A
        $pekerjaA = User::create([
            'name' => 'Budi',
            'username' => 'budi',
            'email' => 'budi@hansjaya.com',
            'password' => bcrypt('password123'),
            'role' => 'pekerja',
            'kandang_id' => $kandangA->id,
            'email_verified_at' => now(),
        ]);
        $pekerjaA->assignRole('pekerja');

        // Pekerja untuk Kandang B
        $pekerjaB = User::create([
            'name' => 'Rini',
            'username' => 'rini',
            'email' => 'rini@hansjaya.com',
            'password' => bcrypt('password123'),
            'role' => 'pekerja',
            'kandang_id' => $kandangB->id,
            'email_verified_at' => now(),
        ]);
        $pekerjaB->assignRole('pekerja');

        // ========== CREATE PRICING ==========
        $hargaKandang = HargaTelur::create([
            'user_id' => $pemilik->id,
            'jenis_harga' => 'kandang',
            'harga_per_kg' => 85000,
            'harga_per_butir' => 5312,
            'tanggal_berlaku' => now(),
            'status' => 'aktif',
        ]);

        $hargaGrosir = HargaTelur::create([
            'user_id' => $pemilik->id,
            'jenis_harga' => 'grosir',
            'harga_per_kg' => 75000,
            'harga_per_butir' => 4687,
            'tanggal_berlaku' => now(),
            'status' => 'aktif',
        ]);

        $hargaKonsumen = HargaTelur::create([
            'user_id' => $pemilik->id,
            'jenis_harga' => 'konsumen',
            'harga_per_kg' => 95000,
            'harga_per_butir' => 5937,
            'tanggal_berlaku' => now(),
            'status' => 'aktif',
        ]);

        // ========== CREATE PRODUCTION DATA (30 days) ==========
        $now = now();
        for ($i = 30; $i >= 1; $i--) {
            $tanggal = $now->copy()->subDays($i);
            
            // Kandang A Production
            ProduksiTelur::create([
                'user_id' => $pekerjaA->id,
                'kandang_id' => $kandangA->id,
                'tanggal_produksi' => $tanggal,
                'jumlah_butir' => rand(8000, 9500),
                'jumlah_kg' => round(rand(500, 600) / 16, 3),
                'jumlah_input' => rand(8000, 9500),
                'hdp' => round(rand(85, 98), 2),
                'hhp' => round(rand(75, 95), 2),
                'mortality' => round(rand(1, 5), 2),
                'ayam_mati' => rand(0, 3),
                'keterangan' => 'Produksi harian Kandang A',
            ]);

            // Kandang B Production
            ProduksiTelur::create([
                'user_id' => $pekerjaB->id,
                'kandang_id' => $kandangB->id,
                'tanggal_produksi' => $tanggal,
                'jumlah_butir' => rand(7500, 9000),
                'jumlah_kg' => round(rand(450, 580) / 16, 3),
                'jumlah_input' => rand(7500, 9000),
                'hdp' => round(rand(82, 96), 2),
                'hhp' => round(rand(72, 92), 2),
                'mortality' => round(rand(1, 6), 2),
                'ayam_mati' => rand(0, 4),
                'keterangan' => 'Produksi harian Kandang B',
            ]);
        }

        // ========== CREATE SALES DATA ==========
        $transactions = [
            ['date' => $now->copy()->subDays(15), 'pembeli' => 'Toko Swalayan Maju', 'jenis' => 'kandang', 'butir' => 3000],
            ['date' => $now->copy()->subDays(14), 'pembeli' => 'Pedagang Pasar Pusat', 'jenis' => 'grosir', 'butir' => 5000],
            ['date' => $now->copy()->subDays(12), 'pembeli' => 'Kafe Kopi Nusantara', 'jenis' => 'konsumen', 'butir' => 1200],
            ['date' => $now->copy()->subDays(10), 'pembeli' => 'Toko Telur Premium', 'jenis' => 'kandang', 'butir' => 2500],
            ['date' => $now->copy()->subDays(8), 'pembeli' => 'Reseller Karawaci', 'jenis' => 'grosir', 'butir' => 4500],
            ['date' => $now->copy()->subDays(5), 'pembeli' => 'Restoran XXL', 'jenis' => 'konsumen', 'butir' => 800],
            ['date' => $now->copy()->subDays(3), 'pembeli' => 'Pedagang Pasar Pusat', 'jenis' => 'grosir', 'butir' => 6000],
            ['date' => $now->copy()->subDays(1), 'pembeli' => 'Toko Telur Premium', 'jenis' => 'kandang', 'butir' => 2800],
        ];

        $konversi = 16; // butir per kg

        foreach ($transactions as $trans) {
            $penjualan = Penjualan::create([
                'user_id' => $pemilik->id,
                'tanggal_jual' => $trans['date'],
                'nama_pembeli' => $trans['pembeli'],
                'total_harga' => 0,
            ]);

            $harga = match($trans['jenis']) {
                'kandang' => $hargaKandang,
                'grosir' => $hargaGrosir,
                'konsumen' => $hargaKonsumen,
            };

            $jumlahKg = round($trans['butir'] / $konversi, 3);
            $subtotal = $jumlahKg * $harga->harga_per_kg;

            DetailPenjualan::create([
                'penjualan_id' => $penjualan->id,
                'harga_telur_id' => $harga->id,
                'jumlah_butir' => $trans['butir'],
                'jumlah_jual' => $trans['butir'],
                'jumlah_kg' => $jumlahKg,
                'satuan_jual' => 'butir',
                'harga_satuan' => $harga->harga_per_butir,
                'subtotal' => $subtotal,
            ]);

            $penjualan->update(['total_harga' => $subtotal]);
        }

        echo "✓ Database seeding complete!\n";
        echo "✓ 2 Kandang created\n";
        echo "✓ 2 Pekerja assigned\n";
        echo "✓ 60 Production records (30 days × 2 kandang)\n";
        echo "✓ 8 Sales transactions\n";
        echo "✓ 3 Pricing tiers (Kandang, Grosir, Konsumen)\n";
    }
}
