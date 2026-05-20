<?php

namespace App\Http\Controllers;

use App\Models\Kandang;
use App\Models\ProduksiTelur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KandangController extends Controller
{
    public function index(Request $request)
    {
        $kandang = Kandang::with('pic')
            ->latest()
            ->paginate(10);

        // Get filter parameters
        $periode = $request->get('periode', 'bulan');
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        // Determine date range
        $startDate = null;
        $endDate = Carbon::now();

        switch ($periode) {
            case 'bulan':
                $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                break;
            case '3bulan':
                $startDate = now()->subMonths(3)->startOfDay();
                break;
            case '6bulan':
                $startDate = now()->subMonths(6)->startOfDay();
                break;
            case 'semua':
                $startDate = ProduksiTelur::min('tanggal_produksi') 
                    ? Carbon::parse(ProduksiTelur::min('tanggal_produksi'))
                    : now()->subYears(1);
                break;
        }

        // Get production data for chart
        $produksiBulanIni = ProduksiTelur::selectRaw('DATE(tanggal_produksi) as tanggal, kandang_id, SUM(jumlah_butir) as total_butir, SUM(ayam_mati) as total_mati')
            ->whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->groupBy('tanggal', 'kandang_id')
            ->orderBy('tanggal')
            ->get();

        // Group by date for chart
        $chartLabels = $produksiBulanIni->pluck('tanggal')->unique()->sort()->values();
        $kandangChartData = [];
        $kandangChartMati = [];

        foreach ($kandang as $k) {
            $data = [];
            $matiData = [];
            foreach ($chartLabels as $label) {
                $prod = $produksiBulanIni->where('tanggal', $label)
                    ->where('kandang_id', $k->id)
                    ->first();
                $data[] = $prod ? $prod->total_butir : 0;
                $matiData[] = $prod ? $prod->total_mati : 0;
            }
            $kandangChartData[$k->id] = $data;
            $kandangChartMati[$k->id] = $matiData;
        }

        // Load summary stats for each kandang - optimized with single query
        $kandangStats = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('kandang_id, 
                         SUM(jumlah_butir) as produksi_total, 
                         SUM(jumlah_kg) as produksi_kg, 
                         AVG(hdp) as rata_rata_hdp, 
                         AVG(hhp) as rata_rata_hhp, 
                         AVG(mortality) as rata_rata_mortality,
                         SUM(ayam_mati) as total_ayam_mati_periode,
                         MAX(tanggal_produksi) as tanggal_produksi_terakhir')
            ->groupBy('kandang_id')
            ->get()
            ->keyBy('kandang_id');
        
        // Get all-time death stats in single query
        $kandangAllTimeStats = ProduksiTelur::selectRaw('kandang_id, SUM(ayam_mati) as total_ayam_mati_all_time')
            ->groupBy('kandang_id')
            ->get()
            ->keyBy('kandang_id');
        
        // Build kandang data from pre-computed stats
        $kandangData = [];
        $totalKematian = 0;
        foreach ($kandang as $k) {
            $stats = $kandangStats->get($k->id);
            $allTimeStats = $kandangAllTimeStats->get($k->id);
            
            $kematian = $stats->total_ayam_mati_periode ?? 0;
            $totalKematian += $kematian;
            
            $totalKematianAllTime = $allTimeStats->total_ayam_mati_all_time ?? 0;
            $ayamAktualSekarang = $k->jumlah_ayam - $totalKematianAllTime;

            $kandangData[$k->id] = [
                'produksi_total' => $stats->produksi_total ?? 0,
                'produksi_kg' => $stats->produksi_kg ?? 0,
                'rata_rata_hdp' => $stats->rata_rata_hdp ?? 0,
                'rata_rata_hhp' => $stats->rata_rata_hhp ?? 0,
                'rata_rata_mortality' => $stats->rata_rata_mortality ?? 0,
                'total_ayam_mati' => $kematian,
                'total_ayam_mati_all_time' => $totalKematianAllTime,
                'ayam_aktual_sekarang' => $ayamAktualSekarang,
                'tanggal_produksi_terakhir' => $stats->tanggal_produksi_terakhir ?? null,
            ];
        }

        return view('kandang.index', compact(
            'kandang',
            'kandangData',
            'totalKematian',
            'periode',
            'bulan',
            'tahun',
            'chartLabels',
            'kandangChartData',
            'kandangChartMati'
        ));
    }

    public function create()
    {
        $pekerja = User::where('role', 'pekerja')->get();
        return view('kandang.create', compact('pekerja'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kandang' => 'required|string|max:100',
            'jumlah_ayam'  => 'required|integer|min:0',
            'status'       => 'required|in:aktif,nonaktif',
            'pic_id'       => 'nullable|exists:users,id',
        ]);

        Kandang::create($request->all());

        return redirect()->route('kandang.index')
                         ->with('success', 'Kandang berhasil ditambahkan!');
    }

    public function show(Kandang $kandang)
    {
        return view('kandang.show', compact('kandang'));
    }

    public function edit(Kandang $kandang)
    {
        $pekerja = User::where('role', 'pekerja')->get();
        return view('kandang.edit', compact('kandang', 'pekerja'));
    }

    public function update(Request $request, Kandang $kandang)
    {
        $request->validate([
            'nama_kandang' => 'required|string|max:100',
            'jumlah_ayam'  => 'required|integer|min:0',
            'status'       => 'required|in:aktif,nonaktif',
            'pic_id'       => 'nullable|exists:users,id',
        ]);

        $kandang->update($request->all());

        return redirect()->route('kandang.index')
                         ->with('success', 'Kandang berhasil diperbarui!');
    }

    public function destroy(Kandang $kandang)
    {
        $kandang->delete();

        return redirect()->route('kandang.index')
                         ->with('success', 'Kandang berhasil dihapus!');
    }
}
