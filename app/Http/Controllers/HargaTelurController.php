<?php

namespace App\Http\Controllers;

use App\Models\HargaTelur;
use Illuminate\Http\Request;

class HargaTelurController extends Controller
{
    public function index(Request $request)
    {
        // Auto-update status harga lama
        $this->updateHargaStatus();
        
        // OPTIMIZED: Use pagination instead of loading all aktif harga
        $hargaAktif = HargaTelur::where('status', 'aktif')
                                ->orderBy('tanggal_berlaku', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate(20);
        $hargaHangus = HargaTelur::where('status', 'hangus')
                                ->orderBy('tanggal_berlaku', 'desc')
                                ->paginate(10);
        
        // Get month filter dari request
        $selectedMonth = $request->query('bulan', null);
        
        // OPTIMIZED: Limit to 1 year of history to reduce data load
        $hargaHistory = HargaTelur::where('tanggal_berlaku', '>=', now()->subYear())
            ->orderBy('tanggal_berlaku')
            ->orderBy('created_at')
            ->get()
            ->groupBy('jenis_harga');
        $chartData = $this->prepareChartData($hargaHistory, $selectedMonth);
        
        return view('harga.index', compact('hargaAktif', 'hargaHangus', 'chartData', 'selectedMonth'));
    }

    public function create()
    {
        return view('harga.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_harga'     => 'required|in:kandang,grosir,konsumen',
            'harga_per_kg'    => 'required|numeric|min:0',
            'harga_per_butir' => 'nullable|numeric|min:0',
            'tanggal_berlaku' => 'required|date',
        ]);

        try {
            $tanggalBerlaku = \Carbon\Carbon::parse($request->tanggal_berlaku)->toDateString();
            $hariIni = now()->toDateString();

            // Gunakan transaction untuk memastikan atomicity
            \DB::transaction(function () use ($request, $tanggalBerlaku, $hariIni) {
                // Tentukan status dan tanggal akhir harga lama
                if ($tanggalBerlaku < $hariIni) {
                    // Tanggal lalu → langsung hangus
                    $status = 'hangus';
                    $tanggalAkhir = $tanggalBerlaku;
                } else {
                    // Hari ini atau depan → aktif
                    $status = 'aktif';
                    $tanggalAkhir = null;
                    
                    // Tentukan kapan harga lama hangus
                    // PENTING: Jika input untuk hari ini, set ke hari INI untuk track multiple prices per hari
                    if ($tanggalBerlaku === $hariIni) {
                        // Set ke hari ini - memungkinkan tracking berdasarkan created_at timestamp
                        $tanggalAkhirHargaLama = $hariIni;
                    } else {
                        // Untuk hari depan, set ke hari sebelumnya
                        $tanggalAkhirHargaLama = \Carbon\Carbon::parse($tanggalBerlaku)->subDay()->toDateString();
                    }
                    
                    // Update harga aktif lama menjadi hangus
                    HargaTelur::where('jenis_harga', $request->jenis_harga)
                              ->where('status', 'aktif')
                              ->update([
                                  'status' => 'hangus',
                                  'tanggal_akhir' => $tanggalAkhirHargaLama,
                              ]);
                }
                
                // Create harga baru
                $hargaBaru = HargaTelur::create([
                    'jenis_harga'     => $request->jenis_harga,
                    'harga_per_kg'    => (float)$request->harga_per_kg,
                    'harga_per_butir' => $request->harga_per_butir ? (float)$request->harga_per_butir : round((float)$request->harga_per_kg / 16, 2),
                    'tanggal_berlaku' => $tanggalBerlaku,
                    'status'          => $status,
                    'tanggal_akhir'   => $tanggalAkhir,
                    'user_id'         => auth()->id(),
                    'keterangan'      => $request->keterangan ?? null,
                ]);
                
                // Verify data tersimpan
                if (!$hargaBaru->id || !$hargaBaru->jenis_harga || !$hargaBaru->tanggal_berlaku) {
                    throw new \Exception('Gagal menyimpan data harga! Data tidak lengkap.');
                }
            });

            $message = $tanggalBerlaku < $hariIni 
                ? 'Harga telur untuk tanggal lalu berhasil ditambahkan dan langsung masuk data hangus!'
                : 'Harga telur baru berhasil ditambahkan! Harga lama otomatis hangus.';
            
            return redirect()->route('harga.index')
                             ->with('success', $message);
                             
        } catch (\Exception $e) {
            \Log::error('Store Harga Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal menyimpan harga: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $hargaTelur = HargaTelur::findOrFail($id);
        return view('harga.show', compact('hargaTelur'));
    }

    public function edit($id)
    {
        // Gunakan find() explicit daripada implicit binding
        $hargaTelur = HargaTelur::findOrFail($id);
        
        // Jika harga lama/hangus, tidak bisa di edit
        if ($hargaTelur->status === 'hangus') {
            return redirect()->route('harga.index')
                           ->with('error', 'Harga hangus tidak bisa diedit. Buat harga baru hari ini!');
        }
        
        return view('harga.edit', compact('hargaTelur'));
    }

    public function update(Request $request, $id)
    {
        $hargaTelur = HargaTelur::findOrFail($id);
        
        if ($hargaTelur->status === 'hangus') {
            return redirect()->route('harga.index')
                           ->with('error', 'Harga hangus tidak bisa diupdate!');
        }

        $request->validate([
            'harga_per_kg'    => 'required|numeric|min:0',
            'harga_per_butir' => 'nullable|numeric|min:0',
            'keterangan'      => 'nullable|string',
        ]);

        $hargaTelur->update([
            'harga_per_kg'    => (float)$request->harga_per_kg,
            'harga_per_butir' => $request->harga_per_butir ? (float)$request->harga_per_butir : round((float)$request->harga_per_kg / 16, 2),
            'keterangan'      => $request->keterangan,
        ]);

        return redirect()->route('harga.index')
                         ->with('success', 'Harga telur berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $hargaTelur = HargaTelur::findOrFail($id);
        
        // Soft delete - mark as hangus
        $hargaTelur->update([
            'status' => 'hangus',
            'tanggal_akhir' => now()->toDateString(),
        ]);
        
        return redirect()->route('harga.index')
                         ->with('success', 'Harga telur dihapus (hangus)!');
    }

    // Helper methods
    private function updateHargaStatus()
    {
        // Auto-mark hangus jika tanggal akhir sudah terlewat
        HargaTelur::where('status', 'aktif')
                  ->where('tanggal_akhir', '<', now()->toDateString())
                  ->update(['status' => 'hangus']);
    }

    private function prepareChartData($hargaHistory, $selectedMonth = null)
    {
        $datasets = [];
        $colorMap = ['kandang' => '#3b82f6', 'grosir' => '#f59e0b', 'konsumen' => '#10b981'];
        $rgbMap = [
            'kandang' => 'rgba(59, 130, 246, 0.1)',
            'grosir' => 'rgba(245, 158, 11, 0.1)',
            'konsumen' => 'rgba(16, 185, 129, 0.1)'
        ];
        $allDates = [];
        
        // OPTIMIZED: Single pass to build date index
        foreach ($hargaHistory as $jenis => $hargaList) {
            foreach ($hargaList as $h) {
                if ($selectedMonth === null || $h->tanggal_berlaku->format('Y-m') === $selectedMonth) {
                    $dateKey = $h->tanggal_berlaku->format('Y-m-d');
                    $allDates[$dateKey] = $h->tanggal_berlaku->format('d-m-Y');
                }
            }
        }
        
        ksort($allDates);
        
        // OPTIMIZED: Build datasets with single pass
        foreach ($hargaHistory as $jenis => $hargaList) {
            $priceByDate = [];
            
            // Map harga by tanggal_berlaku - ambil yang paling baru
            foreach ($hargaList as $h) {
                if ($selectedMonth === null || $h->tanggal_berlaku->format('Y-m') === $selectedMonth) {
                    $dateKey = $h->tanggal_berlaku->format('Y-m-d');
                    if (!isset($priceByDate[$dateKey]) || $h->created_at > $priceByDate[$dateKey]['created_at']) {
                        $priceByDate[$dateKey] = [
                            'harga' => $h->harga_per_kg,
                            'created_at' => $h->created_at,
                        ];
                    }
                }
            }
            
            // Build prices array sesuai urutan sorted dates
            $prices = [];
            foreach ($allDates as $dateKey => $dateFormatted) {
                $prices[] = $priceByDate[$dateKey]['harga'] ?? null;
            }

            $datasets[] = [
                'label' => ucfirst($jenis),
                'data' => $prices,
                'borderColor' => $colorMap[$jenis] ?? '#6b7280',
                'backgroundColor' => $rgbMap[$jenis] ?? 'rgba(107, 114, 128, 0.1)',
                'tension' => 0.4,
                'fill' => true,
                'spanGaps' => true,
            ];
        }

        return [
            'labels' => array_values($allDates),
            'datasets' => $datasets,
        ];
    }

    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgb($r, $g, $b)";
    }
}
