<?php

namespace Database\Seeders;

use App\Models\Kandang;
use App\Models\ProduksiTelur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductionDataSeeder extends Seeder
{
    private $randomNotes = [
        'Pemberian vitamin kompleks',
        'Program vaksin flu burung',
        'Pemeriksaan rutin kesehatan ayam',
        'Pembersihan kandang menyeluruh',
        'Pemberian vitamin A dan D3',
        'Vaksin ND (Newcastle Disease)',
        'Pemberian mineral dan kalsium',
        'Kontrol kualitas pakan',
        'Penyesuaian pencahayaan kandang',
        'Program pemberian probiotik',
        'Vaksin bronchitis',
        'Pemeriksaan BOD (Body Condition)',
        'Proses desinfeksi kandang',
        'Evaluasi pH air minum',
        'Pemberian feed additives',
    ];

    public function run(): void
    {
        // Set jumlah ayam ke 2000 untuk setiap kandang
        Kandang::query()->update(['jumlah_ayam' => 2000]);

        $kandangList = Kandang::all();
        $startDate = Carbon::createFromDate(2026, 1, 1);
        $endDate = Carbon::createFromDate(2026, 4, 7);
        
        $konversiButirPerKg = 16;
        $totalDays = $endDate->diff($startDate)->days + 1; // 97 hari
        
        foreach ($kandangList as $kandang) {
            $pekerja = User::where('kandang_id', $kandang->id)->where('role', 'pekerja')->first();
            if (!$pekerja) continue;

            $ayamAwal = 2000;
            $ayamHidupSekarang = 2000;
            $currentDate = $startDate->copy();
            $dayCounter = 0;
            
            // Untuk random deaths: tentukan hari kematian di awal per minggu
            $weeksDeathDays = [];
            $numWeeks = ceil($totalDays / 7);
            for ($w = 0; $w < $numWeeks; $w++) {
                $deathCount = rand(1, 3); // 1-3 kematian per minggu
                $deathDays = [];
                
                // Tentukan hari mana dalam minggu yang akan ada kematian
                for ($dc = 0; $dc < $deathCount; $dc++) {
                    $randomDay = rand(0, 6); // 0-6 = Senin-Minggu
                    while (in_array($randomDay, $deathDays)) {
                        $randomDay = rand(0, 6); // Hindari duplikasi
                    }
                    $deathDays[] = $randomDay;
                }
                $weeksDeathDays[$w] = $deathDays;
            }
            
            // Track catatan yang sudah dibuat di minggu tertentu
            $notesAddedWeeks = [];
            
            while ($currentDate <= $endDate) {
                $dayCounter++;
                $weekNumber = floor(($dayCounter - 1) / 7);
                $dayOfWeek = ($dayCounter - 1) % 7; // 0-6
                
                // Cek apakah hari ini ada kematian
                $deathsToday = 0;
                if (isset($weeksDeathDays[$weekNumber]) && in_array($dayOfWeek, $weeksDeathDays[$weekNumber])) {
                    $deathsToday = 1;
                    $ayamHidupSekarang = max(1500, $ayamHidupSekarang - 1); // Minimal 1500 ayam
                }
                
                // Produksi telur: 85-90% dari ayam hidup per hari
                $productionPercentage = rand(85, 90) / 100;
                $jumlahButir = (int) ($ayamHidupSekarang * $productionPercentage);
                $jumlahKg = round($jumlahButir / $konversiButirPerKg, 3);
                
                // Hitung metrics
                $hdp = $ayamHidupSekarang > 0 ? ($jumlahButir / $ayamHidupSekarang) * 100 : 0;
                $hhp = $ayamAwal > 0 ? ($jumlahButir / $ayamAwal) * 100 : 0;
                $mortalityTotal = $ayamAwal - $ayamHidupSekarang;
                $mortality = $ayamAwal > 0 ? (($mortalityTotal / $ayamAwal) * 100) : 0;
                
                // Random catatan: beberapa minggu sekali (sekitar 20% chance per minggu, max 1 per minggu)
                $catatan = '';
                if (!in_array($weekNumber, $notesAddedWeeks) && rand(1, 100) <= 25) {
                    $catatan = $this->randomNotes[array_rand($this->randomNotes)];
                    $notesAddedWeeks[] = $weekNumber;
                }
                
                // Insert produksi telur (format sore hari: 16:30)
                ProduksiTelur::create([
                    'kandang_id'       => $kandang->id,
                    'user_id'          => $pekerja->id,
                    'tanggal_produksi' => $currentDate->toDateString(),
                    'satuan_input'     => 'butir',
                    'jumlah_input'     => $jumlahButir,
                    'jumlah_butir'     => $jumlahButir,
                    'jumlah_kg'        => $jumlahKg,
                    'ayam_mati'        => $deathsToday,
                    'ayam_hidup'       => $ayamHidupSekarang,
                    'hdp'              => round($hdp, 2),
                    'hhp'              => round($hhp, 2),
                    'mortality'        => round($mortality, 2),
                    'catatan'          => $catatan ? $catatan . ' | Input: ' . $currentDate->format('d M Y 16:30') : 'Input: ' . $currentDate->format('d M Y 16:30'),
                ]);
                
                $currentDate->addDay();
            }
            
            $totalKematian = $ayamAwal - $ayamHidupSekarang;
            echo "✓ {$kandang->nama_kandang}: {$totalDays} hari data | Total kematian: {$totalKematian} ekor | Akhir: {$ayamHidupSekarang} ekor\n";
        }
        
        echo "\n✓ Production data seeding complete! (Jan 1 - Apr 7, 2026)\n";
        echo "✓ Spec: 85-90% production per day\n";
        echo "✓ Deaths: 1-3 random per week\n";
        echo "✓ Random activity notes included\n";
    }
}
