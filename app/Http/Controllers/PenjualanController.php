<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\HargaTelur;
use App\Models\Pengaturan;
use App\Models\Penjualan;
use App\Models\ProduksiTelur;
use App\Models\StokTelur;
use App\Services\StockService;
use App\Services\PenjualanReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        // Period filter for analytics
        $periode = $request->periode ?? 'bulan';
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        // Determine date range based on periode
        if ($periode === 'bulan') {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        } elseif ($periode === '3bulan') {
            $endDate = now()->endOfDay();
            $startDate = $endDate->copy()->subMonths(3)->startOfDay();
        } elseif ($periode === '6bulan') {
            $endDate = now()->endOfDay();
            $startDate = $endDate->copy()->subMonths(6)->startOfDay();
        } elseif ($periode === 'semua') {
            $endDate = now()->endOfDay();
            $startDate = \Carbon\Carbon::createFromDate(2020, 1, 1)->startOfDay();
        } else {
            $endDate = now()->endOfDay();
            $startDate = $endDate->copy()->subMonths(1)->startOfDay();
        }

        // Query detail tabel dengan filter periode
        $query = Penjualan::select('id', 'user_id', 'tanggal_jual', 'nama_pembeli', 'total_harga')
            ->with([
                'user:id,name',
                'detail' => function($q) {
                    $q->select('id', 'penjualan_id', 'harga_telur_id', 'jumlah_butir', 'jumlah_jual', 'jumlah_kg', 'satuan_jual', 'harga_satuan', 'subtotal', 'harga_per_kg_saat_jual', 'harga_per_butir_saat_jual')
                       ->with('hargaTelur:id,jenis_harga,harga_per_kg,harga_per_butir');
                }
            ])
            ->whereBetween('tanggal_jual', [$startDate, $endDate]);

        $penjualan = $query->latest('tanggal_jual')
            ->paginate($request->per_page ?? 50);

        // Calculate KPI data
        $kpiQuery = Penjualan::with(['detail' => function($q) {
            $q->with('hargaTelur');
        }])
            ->whereBetween('tanggal_jual', [$startDate, $endDate]);

        $allPenjualan = $kpiQuery->get();
        $totalTransaksi = $allPenjualan->count();
        $totalButir = $allPenjualan->pluck('detail')->flatten()->sum('jumlah_butir');
        $totalKg = $allPenjualan->pluck('detail')->flatten()->sum('jumlah_kg');
        $totalHarga = $allPenjualan->sum('total_harga');

        // Produksi dalam periode yang sama
        $totalProduktButir = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('jumlah_butir');
        $totalProduktKg = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('jumlah_kg');

        // Get konversi factor
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;

        // Calculate stock telur (opening + production - sales)
        $stockService = new StockService();
        $openingButir = $stockService->calculateAvailableStock(
            \Carbon\Carbon::parse($startDate)->subDay()->startOfDay(),
            \Carbon\Carbon::parse($startDate)->subDay()->endOfDay()
        );
        $stockholTelur = (int) ($openingButir + $totalProduktButir - $totalButir);
        $stockholTelurKg = $stockholTelur / $konversi;

        // Chart data - use unified report service to match laporan.penjualan
        $reportService = new PenjualanReportService();
        $chartData = $reportService->preparePenjualanChartByHargaWithStock($allPenjualan, $startDate, $endDate);

        return view('penjualan.index', compact(
            'penjualan', 'totalTransaksi', 'totalButir', 'totalKg', 'totalHarga',
            'totalProduktButir', 'totalProduktKg', 'stockholTelur', 'stockholTelurKg',
            'periode', 'bulan', 'tahun', 'chartData'
        ));
    }

    private function preparePenjualanChart($penjualan, $startDate, $endDate)
    {
        // Group by tanggal
        $groupedByDate = $penjualan->groupBy(function($item) {
            return $item->tanggal_jual->format('d-m-Y');
        });

        $labels = [];
        $salesData = [];
        $productionData = [];

        // Get production data per date
        $productionByDate = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal_produksi) as tgl, SUM(jumlah_butir) as total_butir')
            ->groupByRaw('DATE(tanggal_produksi)')
            ->get()
            ->keyBy('tgl');

        // Build chart data
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('d-m');
            $labels[] = $dateStr;

            // Sales for this date
            $dateKey = $currentDate->format('d-m-Y');
            $daySales = $groupedByDate[$dateKey] ?? collect();
            $dayTotal = $daySales->sum('total_harga') / 1000000; // Convert to millions
            $salesData[] = round($dayTotal, 2);

            // Production for this date
            $dateFull = $currentDate->format('Y-m-d');
            $dayProduction = $productionByDate[$dateFull]->total_butir ?? 0;
            $productionData[] = $dayProduction;

            $currentDate->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Penjualan (Juta Rp)',
                    'data' => $salesData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Produksi (Butir)',
                    'data' => $productionData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                ],
            ]
        ];
    }

    public function create()
    {
        $hargaTelur = HargaTelur::aktif()->get();
        
        // Fetch konversi setting untuk frontend
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;

        return view('penjualan.create', compact('hargaTelur', 'konversi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_jual'  => 'required|date',
            'jam_jual'      => 'nullable|date_format:H:i',
            'nama_pembeli'  => 'nullable|string|max:100',
            'items'         => 'required|array|min:1',
            'items.*.harga_telur_id' => 'required|exists:harga_telur,id',
            'items.*.satuan_jual'    => 'required|in:butir,kg',
            'items.*.jumlah_jual'    => 'required|numeric|min:0',
            'items.*.jumlah_butir'   => 'nullable|numeric|min:1',
            'items.*.jumlah_kg'      => 'nullable|numeric|min:0.01',
        ]);

        // Ensure jumlah_butir or jumlah_kg is provided
        foreach ($request->items as $index => $item) {
            if (empty($item['jumlah_butir']) && empty($item['jumlah_kg'])) {
                return redirect()->back()
                    ->with('error', 'Jumlah perkiraan (KG atau Butir) harus diisi pada item ' . ($index + 1))
                    ->withInput();
            }
        }

        // Cek stok sebelum proses
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;
        $stokTersedia = $this->hitungStokTersedia();

        $totalButirDijual = 0;
        foreach ($request->items as $item) {
            // FIXED: Use jumlah_jual directly based on satuan
            $satuan = $item['satuan_jual'];
            $jumlahJual = (float) $item['jumlah_jual'];
            
            if ($satuan === 'butir') {
                $jumlahButir = (int) round($jumlahJual);
            } else {
                $jumlahButir = (int) round($jumlahJual * $konversi);
            }
            $totalButirDijual += $jumlahButir;
        }

        if ($totalButirDijual > $stokTersedia) {
            return redirect()->back()
                ->with('warning', "⚠️ Stok tidak cukup! Stok tersedia: {$stokTersedia} butir, diminta: {$totalButirDijual} butir")
                ->withInput();
        }

        DB::transaction(function () use ($request, $konversi) {
            // Create penjualan
            $penjualan = Penjualan::create([
                'user_id'      => auth()->id(),
                'tanggal_jual' => $request->tanggal_jual,
                'nama_pembeli' => $request->nama_pembeli,
                'total_harga'  => 0,
            ]);

            $total = 0;
            $tanggalPenjualan = $request->tanggal_jual;
            $jamPenjualan = $request->jam_jual ?? now()->format('H:i:s');
            $totalButirDijual = 0;

            foreach ($request->items as $item) {
                $harga = HargaTelur::findOrFail($item['harga_telur_id']);
                
                // FIXED: Use jumlah_jual as the actual quantity in declared satuan
                $satuan = $item['satuan_jual'];
                $jumlahJual = (float) $item['jumlah_jual']; // Actual quantity user input
                
                // Calculate jumlah_butir based on satuan
                if ($satuan === 'butir') {
                    // User input is in butir - use it directly!
                    $jumlahButir = (int) round($jumlahJual);
                    $jumlahKg = round($jumlahButir / $konversi, 3);
                } else {
                    // User input is in kg - use it directly!
                    $jumlahKg = round($jumlahJual, 3);
                    $jumlahButir = (int) round($jumlahJual * $konversi);
                }
                
                $totalButirDijual += $jumlahButir;
                
                // Determine unit price
                $hargaSatuan = $satuan === 'kg'
                    ? $harga->harga_per_kg
                    : $harga->harga_per_butir;

                // Subtotal: quantity (in declared satuan) × price
                $subtotal = $jumlahJual * $hargaSatuan;
                
                $total += $subtotal;

                // Create detail penjualan
                DetailPenjualan::create([
                    'penjualan_id'   => $penjualan->id,
                    'harga_telur_id' => $item['harga_telur_id'],
                    'satuan_jual'    => $satuan,
                    'jumlah_jual'    => $item['jumlah_jual'],
                    'jumlah_butir'   => $jumlahButir,  // INTEGER - no decimals
                    'jumlah_kg'      => round($jumlahKg, 3),
                    'harga_satuan'   => $hargaSatuan,
                    'subtotal'       => $subtotal,
                    'harga_per_butir_saat_jual' => $harga->harga_per_butir,
                    'harga_per_kg_saat_jual'    => $harga->harga_per_kg,
                    'jam_penjualan'     => $jamPenjualan,
                ]);
            }

            // Update penjualan total
            $penjualan->update(['total_harga' => $total]);
            
            // Note: Stock is now calculated dynamically via StockService, 
            // no manual updates to StokTelur table needed
        });

        return redirect()->route('penjualan.index')
                         ->with('success', 'Transaksi penjualan berhasil disimpan!');
    }

    public function show(string $id)
    {
        $penjualan = Penjualan::with('user', 'detail.hargaTelur')->findOrFail($id);
        return view('penjualan.show', compact('penjualan'));
    }

    public function edit(string $id)
    {
        $penjualan = Penjualan::with('detail')->findOrFail($id);
        $hargaTelur = HargaTelur::where('status', 'aktif')->get();
        
        // Fetch konversi setting untuk frontend
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;
        
        return view('penjualan.edit', compact('penjualan', 'hargaTelur', 'konversi'));
    }

    public function update(Request $request, string $id)
    {
        $penjualan = Penjualan::with('detail')->findOrFail($id);

        $request->validate([
            'tanggal_jual'  => 'required|date',
            'nama_pembeli'  => 'nullable|string|max:100',
            'items'         => 'required|array|min:1',
            'items.*.harga_telur_id' => 'required|exists:harga_telur,id',
            'items.*.satuan_jual'    => 'required|in:butir,kg',
            'items.*.jumlah_jual'    => 'required|numeric|min:0',
            'items.*.jumlah_butir'   => 'nullable|numeric|min:1',
            'items.*.jumlah_kg'      => 'nullable|numeric|min:0.01',
        ]);

        // Ensure jumlah_butir or jumlah_kg is provided
        foreach ($request->items as $index => $item) {
            if (empty($item['jumlah_butir']) && empty($item['jumlah_kg'])) {
                return redirect()->back()
                    ->with('error', 'Jumlah perkiraan (KG atau Butir) harus diisi pada item ' . ($index + 1))
                    ->withInput();
            }
        }

        // Check stock considering old items (will be removed)
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;
        $oldTotalButir = $penjualan->detail->sum('jumlah_butir');
        
        $totalButirDijual = 0;
        foreach ($request->items as $item) {
            // FIXED: Use jumlah_jual directly based on satuan
            $satuan = $item['satuan_jual'];
            $jumlahJual = (float) $item['jumlah_jual'];
            
            if ($satuan === 'butir') {
                $jumlahButir = (int) round($jumlahJual);
            } else {
                $jumlahButir = (int) round($jumlahJual * $konversi);
            }
            $totalButirDijual += $jumlahButir;
        }

        // Calculate available stock considering old items that will be removed
        $stokTersedia = $this->hitungStokTersedia() + $oldTotalButir;

        if ($totalButirDijual > $stokTersedia) {
            return redirect()->back()
                ->with('warning', "⚠️ Stok tidak cukup! Stok tersedia: {$stokTersedia} butir, diminta: {$totalButirDijual} butir")
                ->withInput();
        }

        DB::transaction(function () use ($request, $penjualan, $konversi, $oldTotalButir, $totalButirDijual) {
            // Update penjualan header
            $penjualan->update([
                'tanggal_jual' => $request->tanggal_jual,
                'nama_pembeli' => $request->nama_pembeli,
            ]);

            // Delete old details
            $penjualan->detail()->delete();

            $total = 0;

            // Process new items
            foreach ($request->items as $item) {
                $harga = HargaTelur::findOrFail($item['harga_telur_id']);
                
                // FIXED: Use jumlah_jual as the actual quantity in declared satuan
                $satuan = $item['satuan_jual'];
                $jumlahJual = (float) $item['jumlah_jual']; // Actual quantity user input
                
                // Calculate jumlah_butir based on satuan
                if ($satuan === 'butir') {
                    // User input is in butir - use it directly!
                    $jumlahButir = (int) round($jumlahJual);
                    $jumlahKg = round($jumlahButir / $konversi, 3);
                } else {
                    // User input is in kg - use it directly!
                    $jumlahKg = round($jumlahJual, 3);
                    $jumlahButir = (int) round($jumlahJual * $konversi);
                }
                
                $hargaSatuan = $satuan === 'kg'
                    ? $harga->harga_per_kg
                    : $harga->harga_per_butir;

                // Subtotal: quantity (in declared satuan) × price
                $subtotal = $jumlahJual * $hargaSatuan;
                
                $total += $subtotal;

                // Create detail penjualan
                DetailPenjualan::create([
                    'penjualan_id'   => $penjualan->id,
                    'harga_telur_id' => $item['harga_telur_id'],
                    'satuan_jual'    => $item['satuan_jual'],
                    'jumlah_jual'    => $item['jumlah_jual'],
                    'jumlah_butir'   => $jumlahButir,
                    'jumlah_kg'      => round($jumlahKg, 3),
                    'harga_satuan'   => $hargaSatuan,
                    'subtotal'       => $subtotal,
                    'harga_per_butir_saat_jual' => $harga->harga_per_butir,
                    'harga_per_kg_saat_jual'    => $harga->harga_per_kg,
                ]);
            }

            // Update total harga
            $penjualan->update(['total_harga' => $total]);
            
            // Note: Stock is now calculated dynamically via StockService,
            // no manual updates to StokTelur table needed
        });

        return redirect()->route('penjualan.show', $penjualan)
                         ->with('success', 'Transaksi penjualan berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $penjualan = Penjualan::findOrFail($id);

        DB::transaction(function () use ($penjualan) {
            // Delete details and penjualan
            $penjualan->detail()->delete();
            $penjualan->delete();
            
            // Note: Stock is now calculated dynamically via StockService,
            // no manual updates to StokTelur table needed
        });

        return redirect()->route('penjualan.index')
                         ->with('success', 'Transaksi penjualan berhasil dihapus!');
    }

    /**
     * Calculate available stock using StockService (UNIFIED method)
     * Stock = Opening Balance (cumulative all-time) + Production (this month/period) - Sales (this month/period)
     * 
     * @param string $startDate (optional) - Start date of period, default: current month start
     * @param string $endDate (optional) - End date of period, default: current month end
     */
    public function hitungStokTersedia($startDate = null, $endDate = null)
    {
        // Default: current month
        if (!$startDate) {
            $startDate = now()->startOfMonth();
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth();
        }
        
        $stockService = new StockService();
        return $stockService->calculateAvailableStock($startDate, $endDate);
    }

    public function getHargaByDate(Request $request)
    {
        $tanggal = $request->query('tanggal', now()->toDateString());
        
        // Get latest aktif harga for each jenis_harga on or before the given date
        $hargaTelur = HargaTelur::where('status', 'aktif')
            ->where('tanggal_berlaku', '<=', $tanggal)
            ->where(function($q) use ($tanggal) {
                $q->whereNull('tanggal_akhir')
                  ->orWhere('tanggal_akhir', '>=', $tanggal);
            })
            ->orderByDesc('tanggal_berlaku')
            ->orderByDesc('id')
            ->get()
            ->unique('jenis_harga')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $hargaTelur->map(function($h) {
                return [
                    'id'            => $h->id,
                    'jenis_harga'   => $h->jenis_harga,
                    'harga_per_kg'  => $h->harga_per_kg,
                    'harga_per_butir' => $h->harga_per_butir,
                    'display' => 'Rp ' . number_format($h->harga_per_kg, 0, ',', '.') . '/kg - ' . ucfirst($h->jenis_harga),
                ];
            })->toArray(),
        ]);
    }

    /**
     * Get available stock with carryover logic (for front-end display)
     */
    public function getStok(Request $request)
    {
        // Get date from request, default: today
        $tanggal = $request->query('tanggal', now()->toDateString());
        
        // Extract year and month from the date
        $tanggal_obj = \Carbon\Carbon::parse($tanggal);
        $bulanStart = $tanggal_obj->startOfMonth();
        $bulanEnd = $tanggal_obj->endOfMonth();
        
        // Calculate stock for the month of the selected date
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;
        $stokButir = $this->hitungStokTersedia($bulanStart, $bulanEnd);
        $stokKg = $stokButir > 0 ? round($stokButir / $konversi, 2) : 0;
        
        return response()->json([
            'success' => true,
            'stok_butir' => $stokButir,
            'stok_kg' => $stokKg,
        ]);
    }
}
