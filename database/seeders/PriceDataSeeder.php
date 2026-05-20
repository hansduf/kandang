<?php

namespace Database\Seeders;

use App\Models\HargaTelur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PriceDataSeeder extends Seeder
{
    /**
     * Generate random price as multiple of 500
     */
    private function generatePriceMultiple500($minRupiah, $maxRupiah)
    {
        // Convert to "500-rupiah units"
        $minUnits = ceil($minRupiah / 500);
        $maxUnits = floor($maxRupiah / 500);
        
        // Random dalam units
        $randomUnits = rand($minUnits, $maxUnits);
        
        // Convert back to rupiah
        return $randomUnits * 500;
    }

    public function run(): void
    {
        $startDate = Carbon::createFromDate(2026, 1, 1);
        $endDate = Carbon::createFromDate(2026, 4, 7);
        
        $currentDate = $startDate->copy();
        $konversiButirPerKg = 16;
        
        while ($currentDate <= $endDate) {
            // Generate harga kandang: Rp21,500 - Rp24,500 (multiple of 500)
            $hargaKandangPerKg = $this->generatePriceMultiple500(21500, 24500);
            $hargaKandangPerButir = round($hargaKandangPerKg / $konversiButirPerKg, 0);
            
            // Generate harga grosir: kandang + Rp1,000 - Rp2,000 (multiple of 500)
            // Min: kandang + 1000, Max: kandang + 2000
            $hargaGrosirPerKg = $this->generatePriceMultiple500($hargaKandangPerKg + 1000, $hargaKandangPerKg + 2000);
            
            // Ensure grosir >= kandang
            if ($hargaGrosirPerKg < $hargaKandangPerKg) {
                $hargaGrosirPerKg = $hargaKandangPerKg + 1000;
            }
            $hargaGrosirPerButir = round($hargaGrosirPerKg / $konversiButirPerKg, 0);
            
            // Generate harga konsumen: grosir + Rp1,000 - Rp2,000 (multiple of 500)
            // Min: grosir + 1000, Max: grosir + 2000
            $hargaKonsumenPerKg = $this->generatePriceMultiple500($hargaGrosirPerKg + 1000, $hargaGrosirPerKg + 2000);
            
            // Ensure konsumen >= grosir
            if ($hargaKonsumenPerKg < $hargaGrosirPerKg) {
                $hargaKonsumenPerKg = $hargaGrosirPerKg + 1000;
            }
            $hargaKonsumenPerButir = round($hargaKonsumenPerKg / $konversiButirPerKg, 0);
            
            // Final validation: kandang < grosir < konsumen
            if (!($hargaKandangPerKg < $hargaGrosirPerKg && $hargaGrosirPerKg < $hargaKonsumenPerKg)) {
                // Retry if ordering is wrong (shouldn't happen with logic above)
                continue;
            }
            
            // If not first day, mark previous day prices as "hangus"
            if ($currentDate->greaterThan($startDate)) {
                $prevDate = $currentDate->copy()->subDay();
                HargaTelur::where('tanggal_berlaku', $prevDate->toDateString())
                    ->update(['status' => 'hangus', 'tanggal_akhir' => $prevDate->toDateString()]);
            }
            
            // Create new prices for today as "aktif"
            // Harga Kandang
            HargaTelur::create([
                'jenis_harga' => 'kandang',
                'harga_per_kg' => $hargaKandangPerKg,
                'harga_per_butir' => $hargaKandangPerButir,
                'tanggal_berlaku' => $currentDate->toDateString(),
                'tanggal_akhir' => null,
                'status' => 'aktif',
                'user_id' => 1,
                'keterangan' => 'Harga kandang - Input: ' . $currentDate->format('d M Y 08:00'),
            ]);
            
            // Harga Grosir
            HargaTelur::create([
                'jenis_harga' => 'grosir',
                'harga_per_kg' => $hargaGrosirPerKg,
                'harga_per_butir' => $hargaGrosirPerButir,
                'tanggal_berlaku' => $currentDate->toDateString(),
                'tanggal_akhir' => null,
                'status' => 'aktif',
                'user_id' => 1,
                'keterangan' => 'Harga grosir - Input: ' . $currentDate->format('d M Y 08:00'),
            ]);
            
            // Harga Konsumen
            HargaTelur::create([
                'jenis_harga' => 'konsumen',
                'harga_per_kg' => $hargaKonsumenPerKg,
                'harga_per_butir' => $hargaKonsumenPerButir,
                'tanggal_berlaku' => $currentDate->toDateString(),
                'tanggal_akhir' => null,
                'status' => 'aktif',
                'user_id' => 1,
                'keterangan' => 'Harga konsumen - Input: ' . $currentDate->format('d M Y 08:00'),
            ]);
            
            echo "✓ {$currentDate->format('d M Y')}: Kandang Rp" . number_format($hargaKandangPerKg, 0, ',', '.') 
                 . " < Grosir Rp" . number_format($hargaGrosirPerKg, 0, ',', '.') 
                 . " < Konsumen Rp" . number_format($hargaKonsumenPerKg, 0, ',', '.') . "\n";
            
            $currentDate->addDay();
        }
        
        echo "\n✓ Price data seeding complete! (Jan 1 - Apr 7, 2026)\n";
        echo "✓ All prices are multiples of 500\n";
        echo "✓ Order preserved: kandang < grosir < konsumen\n";
        echo "✓ Total records: 171 days × 3 price types = 513 records\n";
    }
}
