<?php

namespace Database\Seeders;

use App\Models\DetailPenjualan;
use App\Models\HargaTelur;
use App\Models\Penjualan;
use App\Models\ProduksiTelur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PenjualanTelurSeeder extends Seeder
{
    private $namaHasil = [
        'CV Maju Sejahtera', 'Toko Emas', 'Warung Mak Ijah', 'Restoran Padang',
        'Toko Kelontong', 'Pedagang Pasar', 'Koperasi Desa', 'UD Sukses',
        'Toko Buah', 'Warung Minum', 'Toko Swalayan', 'Warung Mak Siti',
        'Toko Modern', 'Pabrik Roti', 'Restoran Cepat Saji', 'Toko Oleh-oleh',
    ];

    private $omelette = [
        'Pesan untuk event', 'Pesan reguler', 'Stok untuk restoran', 'Setor mingguan',
        'Belanja untuk warung', 'Pesanan tepat waktu', 'Proyek catering', 'Belanja rutinitas',
        'Pesanan khusus', 'Pengisian ulang stok',
    ];

    public function run(): void
    {
        // Pre-calculate total production for the period
        $allProduction = ProduksiTelur::where('tanggal_produksi', '>=', '2026-01-01')
            ->where('tanggal_produksi', '<=', '2026-04-07')
            ->get();
        
        $totalProdAvailable = (int)$allProduction->sum('jumlah_butir');
        $targetFinalStock = 500;  // End with ~500 butir
        $maxTotalSales = $totalProdAvailable - $targetFinalStock;
        
        $startDate = Carbon::createFromDate(2026, 1, 1);
        $endDate = Carbon::createFromDate(2026, 4, 7);
        
        $currentDate = $startDate->copy();
        $cumulativeProduction = 0;
        $cumulativeSales = 0;
        $butirsPerKg = 16;
        
        echo "\n=== PENJUALAN TELUR (No Negative Stock) ===\n\n";
        
        while ($currentDate <= $endDate) {
            // Get today's production
            $prodToday = (int)ProduksiTelur::where('tanggal_produksi', $currentDate->toDateString())
                ->sum('jumlah_butir');
            
            // Get today's prices
            $hargas = HargaTelur::where('tanggal_berlaku', $currentDate->toDateString())->get();
            
            if ($hargas->isEmpty() || $prodToday == 0) {
                $currentDate->addDay();
                continue;
            }
            
            $cumulativeProduction += $prodToday;
            
            // Calculate max we can sell today
            $maxCanSell = $maxTotalSales - $cumulativeSales;
            
            // Target: 98-99% of daily production, but not exceeding max available
            $targetSale = (int)(rand(98, 99) / 100 * $prodToday);
            $saleToday = min($targetSale, $maxCanSell);
            
            // Ensure at least 80% of production if we have room
            $minSale = (int)ceil($prodToday * 0.80);
            $saleToday = max($minSale, min($saleToday, $maxCanSell));
            
            $cumulativeSales += $saleToday;
            
            // Create 8-20 transactions
            $txCount = rand(8, 20);
            $remaining = $saleToday;
            
            for ($i = 0; $i < $txCount && $remaining > 0; $i++) {
                $qty = ($i == $txCount - 1) ? $remaining : rand(150, min(500, $remaining));
                $remaining -= $qty;
                
                $price = $hargas->random();
                $satuan = rand(1, 100) <= 70 ? 'butir' : 'kg';
                
                if ($satuan === 'kg') {
                    $jual = round($qty / $butirsPerKg, 2);
                    $hargaSatuan = $price->harga_per_kg;
                } else {
                    $jual = $qty;
                    $hargaSatuan = $price->harga_per_butir;
                }
                
                $subtotal = $jual * $hargaSatuan;
                $jam = sprintf('%02d:%02d', rand(8, 17), rand(0, 59));
                
                $penjualan = Penjualan::create([
                    'user_id' => 1,
                    'tanggal_jual' => $currentDate->toDateString(),
                    'nama_pembeli' => $this->namaHasil[array_rand($this->namaHasil)],
                    'total_harga' => $subtotal,
                    'keterangan' => $this->omelette[array_rand($this->omelette)],
                ]);
                
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'harga_telur_id' => $price->id,
                    'satuan_jual' => $satuan,
                    'jumlah_jual' => $jual,
                    'jumlah_butir' => (int)$qty,
                    'jumlah_kg' => round($qty / $butirsPerKg, 2),
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'harga_per_butir_saat_jual' => $price->harga_per_butir,
                    'harga_per_kg_saat_jual' => $price->harga_per_kg,
                    'jam_penjualan' => $jam,
                ]);
            }
            
            $stock = $cumulativeProduction - $cumulativeSales;
            $pct = round(($saleToday / $prodToday) * 100, 1);
            
            echo "✓ {$currentDate->format('d M')} | P:{$prodToday} | J:{$saleToday} ({$pct}%) | S:{$stock}\n";
            
            $currentDate->addDay();
        }
        
        $finalStock = $cumulativeProduction - $cumulativeSales;
        $txCount = Penjualan::count();
        
        echo "\n✓ {$txCount} transaksi | Prod: {$cumulativeProduction} | Jual: {$cumulativeSales} | Stock: {$finalStock}\n";
    }
}
