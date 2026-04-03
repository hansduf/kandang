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

        // Produksi hari ini (tetap fixed untuk hari ini)
        $produksiHariIni = ProduksiTelur::whereDate('tanggal_produksi', today())
            ->sum('jumlah_butir');

        // Produksi periode yang dipilih
        $produksiPeriode = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('jumlah_butir');

        // Penjualan periode yang dipilih
        $penjualanPeriode = Penjualan::whereBetween('tanggal_jual', [$startDate, $endDate])
            ->sum('total_harga');

        // Jumlah kandang aktif
        $jumlahKandang = Kandang::where('status', 'aktif')->count();

        // Total ayam sekarang (kapasitas - total kematian all time)
        $totalKematianAllTime = ProduksiTelur::sum('ayam_mati');
        $totalKapasitas = Kandang::where('status', 'aktif')->sum('jumlah_ayam');
        $totalAyamSekarang = $totalKapasitas - $totalKematianAllTime;

        // Total ayam awal periode (kapasitas - kematian sebelum periode)
        $totalKematianSebelumPeriode = ProduksiTelur::where('tanggal_produksi', '<', $startDate)
            ->sum('ayam_mati');
        $totalAyamAwal = $totalKapasitas - $totalKematianSebelumPeriode;

        // Total kematian dalam periode
        $totalKematianPeriode = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('ayam_mati');

        // Metrics hari ini (rata-rata dari semua kandang)
        $avgHDPToday = ProduksiTelur::whereDate('tanggal_produksi', today())
            ->avg('hdp') ?? 0;

        $avgHHPToday = ProduksiTelur::whereDate('tanggal_produksi', today())
            ->avg('hhp') ?? 0;

        $avgMortalityToday = ProduksiTelur::whereDate('tanggal_produksi', today())
            ->avg('mortality') ?? 0;

        // Metrics periode (rata-rata dari semua kandang)
        $avgHDPPeriode = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->avg('hdp') ?? 0;

        $avgHHPPeriode = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->avg('hhp') ?? 0;

        $avgMortalityPeriode = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->avg('mortality') ?? 0;

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

        // Produksi hari ini untuk kandang ini
        $produksiHariIni = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereDate('tanggal_produksi', today())
            ->sum('jumlah_butir');

        // HDP hari ini (dari produksi hari ini)
        $hdpHariIni = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereDate('tanggal_produksi', today())
            ->avg('hdp') ?? 0;

        // Rata-rata HDP periode yang dipilih
        $avgHDPPeriode = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->avg('hdp') ?? 0;

        // Rata-rata produksi periode yang dipilih
        $avgProduksiPeriode = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->avg('jumlah_butir');

        // Total ayam mati untuk periode
        $totalAyamMatiPeriode = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('ayam_mati');

        // Data grafik dengan metrics sesuai periode
        $perforamaPeriode = ProduksiTelur::where('kandang_id', $kandang->id)
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal_produksi) as tgl, jumlah_butir as produksi, ayam_mati, hdp, hhp, mortality, catatan')
            ->orderBy('tgl')
            ->get();

        return view('dashboard.pekerja', compact(
            'kandang', 'dataKandang', 'produksiHariIni', 'totalAyamMatiPeriode', 
            'avgProduksiPeriode', 'perforamaPeriode', 'hdpHariIni', 'avgHDPPeriode',
            'periode', 'bulan', 'tahun'
        ));
    }
}
