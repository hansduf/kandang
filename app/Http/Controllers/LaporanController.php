<?php

namespace App\Http\Controllers;

use App\Models\ProduksiTelur;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Kandang;
use App\Models\Pengaturan;
use App\Services\StockService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function produksi(Request $request)
    {
        // Ensure bulan and tahun are integers
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $periode = $request->periode ?? 'bulan'; // bulan, semua, 3bulan, 6bulan
        $kandang_id = $request->kandang_id ?? null;

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

        // OPTIMIZED: Use pagination to avoid loading too much data
        $query = ProduksiTelur::with('kandang', 'user')
            ->whereBetween('tanggal_produksi', [$startDate, $endDate]);

        if ($kandang_id) {
            $query->where('kandang_id', $kandang_id);
        }

        $data = $query->orderBy('tanggal_produksi', 'desc')->paginate(50);

        // OPTIMIZED: Use database-level aggregation instead of loading all data
        $aggregateQuery = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate]);
        if ($kandang_id) {
            $aggregateQuery->where('kandang_id', $kandang_id);
        }
        
        $aggregate = $aggregateQuery->selectRaw('SUM(jumlah_butir) as totalButir, SUM(jumlah_kg) as totalKg, AVG(hdp) as avgHDP, AVG(hhp) as avgHHP, AVG(mortality) as avgMortality')
            ->first();
        
        $totalButir = $aggregate->totalButir ?? 0;
        $totalKg = $aggregate->totalKg ?? 0;
        $avgHDP = $aggregate->avgHDP ?? 0;
        $avgHHP = $aggregate->avgHHP ?? 0;
        $avgMortality = $aggregate->avgMortality ?? 0;

        // Chart data detail - Multi-metric utama
        $chartDataUtama = $this->prepareDetailChart($startDate, $endDate, $kandang_id);

        // OPTIMIZED: Load all kandang and their aggregated data in a SINGLE query
        $kandangs = Kandang::where('status', 'aktif')->get();
        
        $allKandangData = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->groupBy('kandang_id')
            ->selectRaw('kandang_id, SUM(jumlah_butir) as total_butir, SUM(jumlah_kg) as total_kg, 
                        AVG(hdp) as avg_hdp, AVG(hhp) as avg_hhp, AVG(mortality) as avg_mortality, 
                        SUM(ayam_mati) as total_ayam_mati, COUNT(*) as hari_pencatatan')
            ->get()
            ->keyBy('kandang_id');

        $perKandangCharts = [];
        foreach ($kandangs as $kandang) {
            $perKandangCharts[$kandang->id] = $this->prepareDetailChart($startDate, $endDate, $kandang->id);
        }

        // OPTIMIZED: Use pre-calculated aggregated data
        $kpiPerKandang = $kandangs->map(function ($kandang) use ($allKandangData) {
            $data = $allKandangData[$kandang->id] ?? null;
            return [
                'id' => $kandang->id,
                'nama_kandang' => $kandang->nama_kandang,
                'jumlah_ayam' => $kandang->jumlah_ayam,
                'total_produksi_butir' => $data->total_butir ?? 0,
                'total_produksi_kg' => $data->total_kg ?? 0,
                'rata_rata_hdp' => $data ? round($data->avg_hdp, 2) : 0,
                'rata_rata_hhp' => $data ? round($data->avg_hhp, 2) : 0,
                'rata_rata_mortality' => $data ? round($data->avg_mortality, 2) : 0,
                'total_ayam_mati' => $data->total_ayam_mati ?? 0,
                'hari_pencatatan' => $data->hari_pencatatan ?? 0,
            ];
        });

        return view('laporan.produksi', compact('data', 'totalButir', 'totalKg', 'kandangs', 'bulan', 'tahun', 'periode', 'chartDataUtama', 'perKandangCharts', 'avgHDP', 'avgHHP', 'avgMortality', 'kpiPerKandang'));
    }

    private function prepareDetailChart($startDate, $endDate, $kandang_id = null)
    {
        $query = ProduksiTelur::selectRaw('DATE(tanggal_produksi) as tgl, SUM(jumlah_butir) as total_butir, AVG(hdp) as avg_hdp, AVG(hhp) as avg_hhp, AVG(mortality) as avg_mortality, SUM(ayam_mati) as total_ayam_mati')
            ->whereBetween('tanggal_produksi', [$startDate, $endDate]);

        if ($kandang_id) {
            $query->where('kandang_id', $kandang_id);
        }

        $data = $query->groupByRaw("DATE(tanggal_produksi)")
            ->orderByRaw("DATE(tanggal_produksi)")
            ->get();

        $labels = $data->map(fn($d) => \Carbon\Carbon::parse($d->tgl)->format('d-m-Y'))->toArray();
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Produksi (Butir)',
                    'data' => $data->pluck('total_butir')->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'HDP (%)',
                    'data' => $data->map(fn($d) => round($d->avg_hdp, 2))->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'fill' => false,
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                    'pointRadius' => 3,
                ],
                [
                    'label' => 'HHP (%)',
                    'data' => $data->map(fn($d) => round($d->avg_hhp, 2))->toArray(),
                    'borderColor' => '#06b6d4',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'fill' => false,
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                    'pointRadius' => 3,
                ],
                [
                    'label' => 'Mortality (%)',
                    'data' => $data->map(fn($d) => round($d->avg_mortality, 2))->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'fill' => false,
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                    'pointRadius' => 3,
                ],
                [
                    'label' => 'Ayam Mati',
                    'data' => $data->pluck('total_ayam_mati')->toArray(),
                    'backgroundColor' => '#fbbf24',
                    'borderColor' => '#f59e0b',
                    'borderWidth' => 1,
                    'yAxisID' => 'y2',
                    'type' => 'bar',
                ],
            ]
        ];
    }

    public function penjualan(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $periode = $request->periode ?? 'bulan'; // bulan, 3bulan, 6bulan, semua

        // Determine date range based on periode
        $startDate = now();
        if ($periode === 'bulan') {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
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

        // Query penjualan dengan detail & harga
        $query = Penjualan::with(['detail' => function($q) {
            $q->with('hargaTelur');
        }, 'user'])
            ->whereBetween('tanggal_jual', [$startDate, $endDate]);

        $allPenjualan = $query->orderBy('tanggal_jual', 'desc')->get();
        
        // Expand data by jenis_harga untuk clarity
        $expandedData = collect();
        foreach ($allPenjualan as $penjualan) {
            // Group detail by jenis_harga
            $groupedByJenis = $penjualan->detail->groupBy(function($detail) {
                return $detail->hargaTelur->jenis_harga;
            });
            
            // Create separate rows for each jenis_harga
            foreach ($groupedByJenis as $jenis => $details) {
                $expandedPenjualan = $penjualan->replicate();
                $expandedPenjualan->detail = $details;
                $expandedPenjualan->jenis_harga_filter = $jenis;
                $expandedData->push($expandedPenjualan);
            }
        }
        
        // Paginate manually - Get perPage from request, default 50
        $perPage = (int) request('per_page', 50);
        $perPage = min($perPage, 500); // Max 500 to prevent abuse
        $page = request('page', 1);
        $totalExpanded = $expandedData->count();
        $expandedDataPage = $expandedData->forPage($page, $perPage);
        $data = new \Illuminate\Pagination\Paginator(
            $expandedDataPage->values(),
            $perPage,
            $page,
            [
                'path' => route('laporan.penjualan'),
                'query' => request()->query(),
            ]
        );

        // Total and summary
        $totalQuery = Penjualan::with(['detail' => function($q) {
            $q->with('hargaTelur');
        }])
            ->whereBetween('tanggal_jual', [$startDate, $endDate]);

        $allData = $totalQuery->get();
        $totalHarga = $allData->sum('total_harga');
        $totalButir = $allData->pluck('detail')->flatten()->sum('jumlah_butir');
        $totalKg = $allData->pluck('detail')->flatten()->sum('jumlah_kg');
        $totalTransaksi = $allData->count();

        // Produksi dalam periode yang sama
        $totalProduktButir = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('jumlah_butir');
        $totalProduktKg = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('jumlah_kg');

        // Get dynamic konversi factor from Pengaturan (default 16 if not set)
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;

        // Calculate opening balance using StockService (UNIFIED method)
        $stockService = new StockService();
        $openingButir = $stockService->calculateAvailableStock(
            \Carbon\Carbon::parse($startDate)->subDay()->startOfDay(),
            \Carbon\Carbon::parse($startDate)->subDay()->endOfDay()
        );
        
        // Selisih stock (opening + produksi - terjual) with carryover
        // ALWAYS calculate KG from butir to ensure consistency
        // MUST cast to integer to ensure no decimals (eggs are discrete units)
        $selisihButir = (int) ($openingButir + $totalProduktButir - $totalButir);
        $selisihKg = $selisihButir / $konversi;  // Convert dari butir to kg using dynamic konversi

        // Chart data by date dengan jenis harga breakdown + stock info
        $chartData = $this->preparePenjualanChartByHargaWithStock($allData, $startDate, $endDate);

        // Recalculate all KG values from butir for display consistency
        // MUST cast to integer for butir totals (discrete units, no decimals)
        $totalProduktButir = (int) $totalProduktButir;
        $totalButir = (int) $totalButir;
        $totalProduktKgCalc = $totalProduktButir / $konversi;
        $totalKgCalc = $totalButir / $konversi;

        return view('laporan.penjualan', compact('data', 'totalHarga', 'totalButir', 'totalKg', 'totalTransaksi', 'totalProduktButir', 'totalProduktKg', 'totalProduktKgCalc', 'totalKgCalc', 'selisihButir', 'selisihKg', 'bulan', 'tahun', 'periode', 'chartData', 'totalExpanded'));
    }

    private function preparePenjualanChartByHarga($penjualan)
    {
        // Group by tanggal
        $groupedByDate = $penjualan->groupBy(function($item) {
            return $item->tanggal_jual->format('d-m-Y');
        });

        $labels = [];
        $dataByHarga = [];
        $hargaColors = [
            'kandang' => '#3b82f6',
            'grosir' => '#f59e0b',
            'konsumen' => '#10b981',
        ];

        foreach ($groupedByDate as $date => $transactions) {
            $labels[] = $date;
            
            // Group by jenis_harga
            $hargaBreakdown = [];
            foreach ($transactions as $t) {
                foreach ($t->detail as $detail) {
                    $jenis = $detail->hargaTelur->jenis_harga;
                    if (!isset($hargaBreakdown[$jenis])) {
                        $hargaBreakdown[$jenis] = 0;
                    }
                    $hargaBreakdown[$jenis] += $detail->subtotal;
                }
            }

            foreach ($hargaBreakdown as $jenis => $total) {
                if (!isset($dataByHarga[$jenis])) {
                    $dataByHarga[$jenis] = [];
                }
                $dataByHarga[$jenis][] = round($total / 1000000, 2);
            }
        }

        // Build datasets
        $datasets = [];
        foreach ($dataByHarga as $jenis => $data) {
            // Pad data to match labels count
            while (count($data) < count($labels)) {
                $data[] = 0;
            }
            
            $datasets[] = [
                'label' => ucfirst($jenis),
                'data' => array_slice($data, 0, count($labels)),
                'borderColor' => $hargaColors[$jenis] ?? '#6b7280',
                'backgroundColor' => 'transparent',
                'borderWidth' => 2,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function preparePenjualanChartByHargaWithStock($penjualan, $startDate, $endDate)
    {
        // Get produksi untuk periode yang sama - format dates consistently
        $produksiData = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal_produksi) as prod_date, SUM(jumlah_butir) as butir')
            ->groupByRaw("DATE(tanggal_produksi)")
            ->get();

        $produksiByDate = [];
        foreach ($produksiData as $row) {
            $dateKey = \Carbon\Carbon::parse($row->prod_date)->format('d-m-Y');
            $produksiByDate[$dateKey] = $row->butir;
        }

        // Group penjualan by tanggal
        $groupedByDate = $penjualan->groupBy(function($item) {
            return $item->tanggal_jual->format('d-m-Y');
        });

        $labels = [];
        $dataByHarga = [
            'kandang' => [],
            'grosir' => [],
            'konsumen' => [],
        ];
        $dataProduksi = [];
        $dataJualanButir = [];

        $hargaColors = [
            'kandang' => '#3b82f6',
            'grosir' => '#f59e0b',
            'konsumen' => '#10b981',
        ];

        // Get all dates for consistency
        $allDates = array_keys($produksiByDate);
        foreach ($groupedByDate as $date => $transactions) {
            if (!in_array($date, $allDates)) {
                $allDates[] = $date;
            }
        }
        sort($allDates);

        foreach ($allDates as $date) {
            $labels[] = $date;

            // Penjualan for this date
            $jualanHariIni = $groupedByDate->get($date, []);
            $totalJualanButirHariIni = 0;
            $hargaBreakdown = [
                'kandang' => 0,
                'grosir' => 0,
                'konsumen' => 0,
            ];

            foreach ($jualanHariIni as $t) {
                foreach ($t->detail as $detail) {
                    $jenis = $detail->hargaTelur->jenis_harga;
                    if (isset($hargaBreakdown[$jenis])) {
                        $hargaBreakdown[$jenis] += $detail->subtotal;
                    }
                    $totalJualanButirHariIni += $detail->jumlah_butir;
                }
            }

            $dataJualanButir[] = $totalJualanButirHariIni;

            // Add data for each jenis with proper alignment
            foreach ($hargaBreakdown as $jenis => $total) {
                $dataByHarga[$jenis][] = round($total / 1000000, 2);
            }

            // Produksi for this date
            $dataProduksi[] = $produksiByDate[$date] ?? 0;
        }

        // Build datasets - jenis harga breakdown
        $datasets = [];
        foreach ($dataByHarga as $jenis => $data) {
            $datasets[] = [
                'label' => 'Penjualan ' . ucfirst($jenis),
                'data' => $data,
                'borderColor' => $hargaColors[$jenis] ?? '#6b7280',
                'backgroundColor' => 'transparent',
                'borderWidth' => 2,
                'yAxisID' => 'y',
                'tension' => 0.4,
            ];
        }

        // Add produksi dataset
        $datasets[] = [
            'label' => 'Produksi (Butir)',
            'data' => $dataProduksi,
            'borderColor' => '#22c55e',
            'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
            'borderWidth' => 2.5,
            'fill' => true,
            'yAxisID' => 'y1',
            'tension' => 0.4,
        ];

        // Add stok keluar (terjual) dataset
        $datasets[] = [
            'label' => 'Stok Keluar (Butir)',
            'data' => $dataJualanButir,
            'backgroundColor' => '#9ca3af',
            'borderColor' => '#6b7280',
            'borderWidth' => 1,
            'yAxisID' => 'y1',
            'type' => 'bar',
            'alpha' => 0.6,
        ];

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    public function exportProduksiPdf(Request $request)
    {
        $periode = $request->periode ?? 'bulan';
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $kandang_id = $request->kandang_id ?? null;

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
            $startDate = \Carbon\Carbon::createFromDate(2020, 1, 1)->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        }

        $query = ProduksiTelur::with('kandang', 'user')
            ->whereBetween('tanggal_produksi', [$startDate, $endDate]);

        if ($kandang_id) {
            $query->where('kandang_id', $kandang_id);
        }

        $data = $query->orderBy('tanggal_produksi', 'asc')->get();
        $totalButir = $data->sum('jumlah_butir');
        $totalKg = $data->sum('jumlah_kg');
        $totalAyamMati = $data->sum('ayam_mati');
        $avgHDP = $data->avg('hdp') ?? 0;
        $avgHHP = $data->avg('hhp') ?? 0;
        $avgMortality = $data->avg('mortality') ?? 0;

        // Format periode display
        if ($periode === 'semua') {
            $periodeDisplay = 'Semua Data (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($periode === '3bulan') {
            $periodeDisplay = '3 Bulan (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($periode === '6bulan') {
            $periodeDisplay = '6 Bulan (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } else {
            $periodeDisplay = date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun));
        }

        $pdf = Pdf::loadView('laporan.pdf.produksi', compact('data', 'totalButir', 'totalKg', 'totalAyamMati', 'avgHDP', 'avgHHP', 'avgMortality', 'periodeDisplay'))
            ->setPaper('a4', 'landscape');

        // Create filename-safe date range
        $filenameSuffix = $startDate->format('dmY') . '-' . $endDate->format('dmY');
        return $pdf->download("Laporan-Produksi-{$filenameSuffix}.pdf");
    }

    public function exportPenjualanPdf(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $periode = $request->periode ?? 'bulan';

        // Determine date range based on periode (same logic as penjualan method)
        $startDate = now();
        if ($periode === 'bulan') {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
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

        // Get all penjualan data
        $allPenjualan = Penjualan::with(['detail' => function($q) {
            $q->with('hargaTelur');
        }, 'user'])
            ->whereBetween('tanggal_jual', [$startDate, $endDate])
            ->orderBy('tanggal_jual', 'desc')
            ->get();

        // Expand data by jenis_harga
        $expandedData = collect();
        foreach ($allPenjualan as $penjualan) {
            $groupedByJenis = $penjualan->detail->groupBy(function($detail) {
                return $detail->hargaTelur->jenis_harga;
            });
            
            foreach ($groupedByJenis as $jenis => $details) {
                $expandedPenjualan = $penjualan->replicate();
                $expandedPenjualan->detail = $details;
                $expandedPenjualan->jenis_harga_filter = $jenis;
                $expandedData->push($expandedPenjualan);
            }
        }

        // Calculate totals
        $totalHarga = $allPenjualan->sum('total_harga');
        $totalButir = $allPenjualan->pluck('detail')->flatten()->sum('jumlah_butir');
        $totalTransaksi = $allPenjualan->count();

        $periodeName = match($periode) {
            'bulan' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'),
            '3bulan' => 'Last 3 Months',
            '6bulan' => 'Last 6 Months',
            'semua' => 'All Time',
            default => 'Periode'
        };

        $pdf = Pdf::loadView('laporan.pdf.penjualan', compact('expandedData', 'totalHarga', 'totalButir', 'totalTransaksi', 'periodeName', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("Laporan-Penjualan-{$periodeName}-" . now()->format('Y-m-d') . ".pdf");
    }

    public function exportProduksiExcel(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $kandang_id = $request->kandang_id ?? null;
        $periode = $request->periode ?? 'bulan';

        $query = ProduksiTelur::with('kandang', 'user');

        // Determine date range based on periode
        if ($periode === 'bulan') {
            $startDate = Carbon::createFromDate($tahun, $bulan, 1);
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($periode === '3bulan') {
            $endDate = now();
            $startDate = $endDate->copy()->subMonths(3);
        } elseif ($periode === '6bulan') {
            $endDate = now();
            $startDate = $endDate->copy()->subMonths(6);
        } elseif ($periode === 'semua') {
            $startDate = Carbon::createFromDate(2020, 1, 1);
            $endDate = now();
        } else {
            $startDate = Carbon::createFromDate($tahun, $bulan, 1);
            $endDate = $startDate->copy()->endOfMonth();
        }

        $query->whereBetween('tanggal_produksi', [$startDate, $endDate]);

        if ($kandang_id) {
            $query->where('kandang_id', $kandang_id);
        }

        $allData = $query->orderBy('tanggal_produksi', 'asc')->get();

        // Calculate totals
        $totalButir = $allData->sum('jumlah_butir');
        $totalKg = $allData->sum('jumlah_kg');
        $totalAyamMati = $allData->sum('ayam_mati');
        $avgHDP = $allData->avg('hdp');
        $avgHHP = $allData->avg('hhp');
        $avgMortality = $allData->avg('mortality');

        // Format periode display
        if ($periode === 'bulan') {
            $periodeDisplay = Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
        } elseif ($periode === '3bulan') {
            $periodeDisplay = 'Last 3 Months (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($periode === '6bulan') {
            $periodeDisplay = 'Last 6 Months (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } elseif ($periode === 'semua') {
            $periodeDisplay = 'Semua Data (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')';
        } else {
            $periodeDisplay = Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
        }

        // Build structured rows
        $rows = [];
        
        // Header section
        $rows[] = ['LAPORAN PRODUKSI TELUR - HANS JAYA POULTRY', '', '', '', '', '', '', '', ''];
        $rows[] = [];
        $rows[] = ['Periode:', $periodeDisplay, '', '', '', '', '', '', ''];
        $rows[] = ['Tanggal Cetak:', now()->format('d/m/Y H:i:s'), '', '', '', '', '', '', ''];
        $rows[] = [];
        
        // Column headers for detail data
        $rows[] = [
            'NO',
            'TANGGAL',
            'KANDANG',
            'PEKERJA',
            'BUTIR',
            'KG',
            'INPUT',
            'HDP %',
            'HHP %',
            'MORTALITY %',
            'AYAM MATI'
        ];
        
        // Data rows
        $no = 1;
        foreach ($allData as $data) {
            $rows[] = [
                $no,
                $data->tanggal_produksi->format('d/m/Y'),
                $data->kandang->nama_kandang ?? '-',
                $data->user->name ?? '-',
                $data->jumlah_butir,
                number_format($data->jumlah_kg, 3, '.', ''),
                $data->jumlah_input ?? '-',
                number_format($data->hdp ?? 0, 2, '.', ''),
                number_format($data->hhp ?? 0, 2, '.', ''),
                number_format($data->mortality ?? 0, 2, '.', ''),
                $data->ayam_mati ?? '0'
            ];
            $no++;
        }
        
        // Total row
        $rows[] = [];
        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            $totalButir,
            number_format($totalKg, 3, '.', ''),
            '',
            '',
            '',
            '',
            $totalAyamMati
        ];
        
        // Average row
        $rows[] = [
            'RATA-RATA',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($avgHDP, 2, '.', ''),
            number_format($avgHHP, 2, '.', ''),
            number_format($avgMortality, 2, '.', ''),
            ''
        ];
        
        // Summary section
        $rows[] = [];
        $rows[] = ['RINGKASAN', ''];
        $rows[] = [];
        $rows[] = ['Total Hari Produksi', $allData->count()];
        $rows[] = ['Total Butir', $totalButir];
        $rows[] = ['Total KG', number_format($totalKg, 3, '.', '')];
        $rows[] = ['Total Ayam Mati', $totalAyamMati];
        $rows[] = ['Rata-rata HDP (%)', number_format($avgHDP, 2, '.', '')];
        $rows[] = ['Rata-rata HHP (%)', number_format($avgHHP, 2, '.', '')];
        $rows[] = ['Rata-rata Mortality (%)', number_format($avgMortality, 2, '.', '')];
        $rows[] = [];
        $rows[] = ['Dicetak oleh:', auth()->user()->name];
        $rows[] = ['Waktu Cetak:', now()->format('d/m/Y H:i:s')];

        // Convert to CSV
        $csv = "sep=,\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function($cell) {
                // Escape quotes and wrap in quotes if contains comma or quote
                if (is_null($cell)) {
                    return '';
                }
                if (strpos($cell, ',') !== false || strpos($cell, '"') !== false || strpos($cell, "\n") !== false) {
                    return '"' . str_replace('"', '""', $cell) . '"';
                }
                return $cell;
            }, $row)) . "\n";
        }

        $fileName = "Laporan-Produksi-{$startDate->format('dmY')}-{$endDate->format('dmY')}.csv";
        
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportPenjualanExcel(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;
        $periode = $request->periode ?? 'bulan';

        // Determine date range based on periode (same logic as penjualan method)
        $startDate = now();
        if ($periode === 'bulan') {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
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

        // Get all penjualan data with details
        $allPenjualan = Penjualan::with(['detail' => function($q) {
            $q->with('hargaTelur');
        }, 'user'])
            ->whereBetween('tanggal_jual', [$startDate, $endDate])
            ->orderBy('tanggal_jual', 'desc')
            ->get();

        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;

        // Calculate totals from expanded data (per jenis_harga in each transaksi)
        $totalButir = $allPenjualan->pluck('detail')->flatten()->sum('jumlah_butir');
        $totalKg = $allPenjualan->pluck('detail')->flatten()->sum('jumlah_kg');
        $totalHarga = $allPenjualan->sum('total_harga');
        $totalTransaksi = $allPenjualan->count();

        // Build CSV with better formatting
        $rows = [];
        
        // Header section
        $rows[] = ['LAPORAN PENJUALAN TELUR'];
        $rows[] = ['Hans Jaya Poultry'];
        $rows[] = [];
        $rows[] = ['Periode:', $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y')];
        $rows[] = ['Tanggal Cetak:', now()->format('d/m/Y H:i:s')];
        $rows[] = [];
        
        // Column headers
        $rows[] = [
            'NO',
            'TANGGAL',
            'PEMBELI',
            'PENGGUNA',
            'JENIS HARGA',
            'BUTIR',
            'KG',
            'HARGA PER KG',
            'HARGA PER BUTIR',
            'TOTAL HARGA'
        ];
        
        // Data rows - expand by jenis_harga like in the view
        $no = 1;
        foreach ($allPenjualan as $penjualan) {
            // Group by jenis_harga
            $groupedByJenis = $penjualan->detail->groupBy(function($d) {
                return $d->hargaTelur->jenis_harga;
            });
            
            foreach ($groupedByJenis as $jenis => $details) {
                $totalJenisButir = $details->sum('jumlah_butir');
                $totalJenisKg = $details->sum('jumlah_kg');
                $totalJenisHarga = $details->sum('subtotal');
                
                $jenisPenamaan = match($jenis) {
                    'kandang' => 'KANDANG',
                    'grosir' => 'GROSIR',
                    'konsumen' => 'KONSUMEN',
                    default => strtoupper($jenis),
                };
                
                // Get first detail for price info (all should be same for the jenis)
                $firstDetail = $details->first();

                $rows[] = [
                    $no,
                    $penjualan->tanggal_jual->format('d/m/Y'),
                    $penjualan->nama_pembeli ?? 'Umum',
                    $penjualan->user->name ?? '-',
                    $jenisPenamaan,
                    $totalJenisButir,
                    number_format($totalJenisKg, 3, '.', ''),
                    $firstDetail->harga_per_kg_saat_jual ?? 0,
                    $firstDetail->harga_per_butir_saat_jual ?? 0,
                    $totalJenisHarga
                ];
                $no++;
            }
        }
        
        // Total row
        $rows[] = [];
        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            number_format($totalButir, 0, '.', ''),
            number_format($totalKg, 3, '.', ''),
            '',
            '',
            number_format($totalHarga, 0, '.', '')
        ];
        
        // Summary section
        $rows[] = [];
        $rows[] = ['RINGKASAN / SUMMARY'];
        $rows[] = [];
        $rows[] = ['Total Transaksi (Transactions):', $totalTransaksi];
        $rows[] = ['Total Butir Terjual (Total Eggs Sold):', number_format($totalButir, 0, ',', '.')];
        $rows[] = ['Total KG Terjual (Total KG Sold):', number_format($totalKg, 3, ',', '.')];
        $rows[] = ['Total Revenue (Total Harga):', number_format($totalHarga, 0, ',', '.')];
        $rows[] = ['Rata-rata Price per KG:', $totalKg > 0 ? number_format($totalHarga / $totalKg, 0, ',', '.') : '0'];
        $rows[] = ['Rata-rata Price per Butir:', $totalButir > 0 ? number_format($totalHarga / $totalButir, 0, ',', '.') : '0'];
        $rows[] = [];
        $rows[] = ['Dicetak oleh:', auth()->user()->name];
        $rows[] = ['Waktu Cetak:', now()->format('d/m/Y H:i:s')];

        // Convert to CSV
        $csv = "sep=,\n";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function($cell) {
                // Handle null values
                if (is_null($cell)) {
                    return '';
                }
                // Escape quotes and wrap in quotes if contains comma or quote
                if (strpos($cell, ',') !== false || strpos($cell, '"') !== false || strpos($cell, "\n") !== false) {
                    return '"' . str_replace('"', '""', $cell) . '"';
                }
                return $cell;
            }, $row)) . "\n";
        }

        // Set filename with date and periode
        $periodeName = match($periode) {
            'bulan' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F_Y'),
            '3bulan' => 'Last3Months',
            '6bulan' => 'Last6Months',
            'semua' => 'AllTime',
            default => 'Periode'
        };
        
        $fileName = "Laporan-Penjualan-{$periodeName}-" . now()->format('Y-m-d') . ".csv";
        
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
