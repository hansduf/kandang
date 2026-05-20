<?php

namespace App\Http\Controllers;

use App\Models\Kandang;
use App\Models\Penjualan;
use App\Models\ProduksiTelur;
use App\Models\StokTelur;
use App\Services\StockService;

class DashboardController extends Controller
{
    public function index()
    {
        // Jika pekerja, tampilkan performa kandangnya
        if (auth()->user()->hasRole('pekerja')) {
            return $this->dashboardPekerja();
        }

        // Dashboard pemilik - Calculate stock for today using StockService
        $stockService = new StockService();
        $stockButir = $stockService->calculateAvailableStock(today()->startOfDay(), today()->endOfDay());
        $stockKg = $stockService->butirToKg($stockButir);
        
        // Create a simple object to pass to view (matching old interface)
        $stok = (object) [
            'stok_butir' => $stockButir,
            'stok_kg' => $stockKg,
        ];

        // Filter periode dari request
        $periode = request('periode', '7hari'); // hari, 7hari, bulan, semua
        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);
        $tanggal = request('tanggal', now()->toDateString());

        // Tentukan date range berdasarkan periode
        if ($periode === 'hari') {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal)->startOfDay();
            $endDate = $startDate->copy()->endOfDay();
        } elseif ($periode === '7hari') {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($periode === 'bulan') {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        } elseif ($periode === 'semua') {
            $startDate = \Carbon\Carbon::createFromDate(2020, 1, 1)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Optimize: Single query for today's metrics
        $todayMetrics = ProduksiTelur::whereDate('tanggal_produksi', today())
            ->selectRaw('SUM(jumlah_butir) as produksi, AVG(hdp) as avgHDP, AVG(hhp) as avgHHP, AVG(mortality) as avgMortality')
            ->first();
        
        // Optimize: Single query for period metrics
        $periodMetrics = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('SUM(jumlah_butir) as produksi, SUM(ayam_mati) as kematian, AVG(hdp) as avgHDP, AVG(hhp) as avgHHP, AVG(mortality) as avgMortality')
            ->first();
        
        // Optimize: Single query for kandang & death stats
        $kandangStats = Kandang::where('status', 'aktif')
            ->selectRaw('COUNT(*) as jumlah_kandang, SUM(jumlah_ayam) as total_kapasitas')
            ->first();
        
        $totalKematianAllTime = ProduksiTelur::sum('ayam_mati');
        $totalKematianSebelumPeriode = ProduksiTelur::where('tanggal_produksi', '<', $startDate)->sum('ayam_mati');
        
        // Set values from aggregated queries
        $produksiHariIni = $todayMetrics->produksi ?? 0;
        $produksiPeriode = $periodMetrics->produksi ?? 0;
        $totalKematianPeriode = $periodMetrics->kematian ?? 0;
        
        $jumlahKandang = $kandangStats->jumlah_kandang ?? 0;
        $totalKapasitas = $kandangStats->total_kapasitas ?? 0;
        
        // Penjualan periode yang dipilih
        $penjualanPeriode = Penjualan::whereBetween('tanggal_jual', [$startDate, $endDate])
            ->sum('total_harga');
        
        $totalAyamSekarang = $totalKapasitas - $totalKematianAllTime;
        $totalAyamAwal = $totalKapasitas - $totalKematianSebelumPeriode;
        
        // Metrics hari ini
        $avgHDPToday = $todayMetrics->avgHDP ?? 0;
        $avgHHPToday = $todayMetrics->avgHHP ?? 0;
        $avgMortalityToday = $todayMetrics->avgMortality ?? 0;
        
        // Metrics periode
        $avgHDPPeriode = $periodMetrics->avgHDP ?? 0;
        $avgHHPPeriode = $periodMetrics->avgHHP ?? 0;
        $avgMortalityPeriode = $periodMetrics->avgMortality ?? 0;

        // Grafik periode per kandang
        $produksiPeriodePerKandang = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->with('kandang')
            ->selectRaw('DATE(tanggal_produksi) as tgl, kandang_id, SUM(jumlah_butir) as total')
            ->groupBy('tgl', 'kandang_id')
            ->orderBy('tgl')
            ->orderBy('kandang_id')
            ->get();

        // Format data untuk chart (per kandang)
        $tanggalPeriode = [];
        
        foreach ($produksiPeriodePerKandang as $item) {
            $tgl = $item->tgl;
            if (!in_array($tgl, $tanggalPeriode)) {
                $tanggalPeriode[] = $tgl;
            }
        }
        
        // Kelompokkan per kandang
        $kandangProduction = [];
        foreach ($produksiPeriodePerKandang as $item) {
            $kandangId = $item->kandang_id;
            if (!isset($kandangProduction[$kandangId])) {
                $kandangProduction[$kandangId] = [
                    'nama' => $item->kandang->nama_kandang,
                    'data' => array_fill(0, count($tanggalPeriode), 0)
                ];
            }
            $tglIndex = array_search($item->tgl, $tanggalPeriode);
            $kandangProduction[$kandangId]['data'][$tglIndex] = $item->total;
        }

        return view('dashboard.index', compact(
            'stok', 'produksiHariIni', 'produksiPeriode', 'penjualanPeriode', 'jumlahKandang',
            'totalAyamSekarang', 'totalAyamAwal', 'totalKematianPeriode',
            'tanggalPeriode', 'kandangProduction', 'avgHDPToday', 'avgHHPToday', 'avgMortalityToday',
            'avgHDPPeriode', 'avgHHPPeriode', 'avgMortalityPeriode', 'periode', 'bulan', 'tahun', 'tanggal',
            'startDate', 'endDate'
        ));
    }

    private function dashboardPekerja()
    {
        $kandang = auth()->user()->kandang;
        
        if (!$kandang) {
            return view('dashboard.pekerja', ['kandang' => null]);
        }

        // Filter periode dari request
        $periode = request('periode', '7hari'); // 7hari, bulan, semua
        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        // Tentukan date range berdasarkan periode
        if ($periode === '7hari') {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($periode === 'bulan') {
            $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        } elseif ($periode === 'semua') {
            $startDate = \Carbon\Carbon::createFromDate(2020, 1, 1)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Data kandang
        $dataKandang = [
            'nama' => $kandang->nama_kandang,
            'jumlah_ayam' => $kandang->jumlah_ayam,
            'pic' => $kandang->pic->name ?? '-',
            'status' => $kandang->status,
        ];

        // Optimize: Single query for today's metrics
        $todayData = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereDate('tanggal_produksi', today())
            ->selectRaw('SUM(jumlah_butir) as produksi, AVG(hdp) as avgHDP, SUM(ayam_mati) as kematian')
            ->first();
        
        // Optimize: Single query for period metrics
        $periodData = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('AVG(jumlah_butir) as avgProduksi, AVG(hdp) as avgHDP, SUM(ayam_mati) as kematian, SUM(jumlah_butir) as totalProduksi')
            ->first();
        
        // Set values from aggregated queries
        $produksiHariIni = $todayData->produksi ?? 0;
        $hdpHariIni = $todayData->avgHDP ?? 0;
        $avgHDPPeriode = $periodData->avgHDP ?? 0;
        $avgProduksiPeriode = $periodData->avgProduksi ?? 0;
        $totalAyamMatiPeriode = $periodData->kematian ?? 0;

        // Data grafik dengan metrics sesuai periode
        $perforamaPeriode = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal_produksi) as tgl, SUM(jumlah_butir) as produksi, SUM(ayam_mati) as ayam_mati, AVG(hdp) as hdp, AVG(hhp) as hhp, AVG(mortality) as mortality')
            ->groupByRaw("DATE(tanggal_produksi)")
            ->orderBy('tgl')
            ->get();

        // Untuk tabel, reverse agar data terbaru di atas
        $perforamaPeriodeTable = $perforamaPeriode->reverse()->values();

        return view('dashboard.pekerja', compact(
            'kandang', 'dataKandang', 'produksiHariIni', 'totalAyamMatiPeriode', 
            'avgProduksiPeriode', 'perforamaPeriode', 'perforamaPeriodeTable', 'hdpHariIni', 'avgHDPPeriode',
            'periode', 'bulan', 'tahun'
        ));
    }
}
