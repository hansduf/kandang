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

        // Load summary stats for each kandang - based on selected period
        $kandangData = [];
        $totalKematian = 0;
        foreach ($kandang as $k) {
            $produksi = $k->produksiTelur()
                ->whereBetween('tanggal_produksi', [$startDate, $endDate])
                ->orderBy('tanggal_produksi', 'desc')
                ->get();

            $kematian = $produksi->sum('ayam_mati');
            $totalKematian += $kematian;

            // Total kematian all time (untuk menghitung ayam aktual sekarang)
            $totalKematianAllTime = $k->produksiTelur()->sum('ayam_mati');

            // Kematian sebelum periode dipilih (untuk menghitung ayam aktual awal periode)
            $kematianSebelumPeriode = $k->produksiTelur()
                ->where('tanggal_produksi', '<', $startDate)
                ->sum('ayam_mati');

            $ayamAktualSekarang = $k->jumlah_ayam - $totalKematianAllTime;
            $ayamAktualAwal = $k->jumlah_ayam - $kematianSebelumPeriode;

            $kandangData[$k->id] = [
                'produksi_total' => $produksi->sum('jumlah_butir'),
                'produksi_kg' => $produksi->sum('jumlah_kg'),
                'rata_rata_hdp' => $produksi->avg('hdp'),
                'rata_rata_hhp' => $produksi->avg('hhp'),
                'rata_rata_mortality' => $produksi->avg('mortality'),
                'total_ayam_mati' => $kematian,
                'total_ayam_mati_all_time' => $totalKematianAllTime,
                'ayam_aktual_sekarang' => $ayamAktualSekarang,
                'ayam_aktual_awal' => $ayamAktualAwal,
                'tanggal_produksi_terakhir' => $produksi->first()->tanggal_produksi ?? null,
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
