<?php

namespace App\Http\Controllers;

use App\Models\Kandang;
use App\Models\Pengaturan;
use App\Models\ProduksiTelur;
use App\Models\StokTelur;
use Illuminate\Http\Request;

class ProduksiTelurController extends Controller
{
    public function index()
    {
        $produksi = ProduksiTelur::with('kandang', 'user')
            ->where('user_id', auth()->id())
            ->latest('tanggal_produksi')
            ->paginate(10);

        return view('produksi.index', compact('produksi'));
    }

    public function create()
    {
        $kandang = Kandang::where('status', 'aktif')->get();
        
        // Hitung ayam hidup saat ini untuk setiap kandang (base - cumulative deaths)
        $kandangWithCurrentCount = $kandang->map(function($k) {
            $totalAyamMati = ProduksiTelur::where('kandang_id', $k->id)->sum('ayam_mati');
            $k->ayam_hidup_saat_ini = $k->jumlah_ayam - $totalAyamMati;
            return $k;
        });
        
        // Fetch konversi setting untuk frontend
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;
        
        return view('produksi.create', compact('kandangWithCurrentCount', 'konversi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_produksi' => 'required|date',
            'satuan_input'     => 'required|in:butir,kg',
            'jumlah_input'     => 'required|numeric|min:0',
            'ayam_mati'        => 'nullable|integer|min:0',
            'catatan'          => 'nullable|string|max:500',
        ]);

        // Ambil nilai konversi dari tabel pengaturan
        $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;

        // Hitung jumlah butir dan kg
        if ($request->satuan_input === 'butir') {
            $jumlah_butir = (int) $request->jumlah_input;
            $jumlah_kg    = round($jumlah_butir / $konversi, 3);
        } else {
            $jumlah_kg    = (float) $request->jumlah_input;
            $jumlah_butir = (int) round($jumlah_kg * $konversi);
        }

        // Ambil kandang untuk mendapatkan jumlah ayam awal
        $kandang = auth()->user()->kandang;
        $jumlah_ayam_awal = $kandang->jumlah_ayam;
        $ayam_mati = (int) ($request->ayam_mati ?? 0);
        
        // Calculate ayam_hidup from total deaths recorded
        $total_ayam_mati = ProduksiTelur::where('kandang_id', $kandang->id)->sum('ayam_mati');
        $ayam_hidup = $jumlah_ayam_awal - $total_ayam_mati - $ayam_mati;
        
        // Ensure ayam_hidup doesn't go negative
        if ($ayam_hidup < 0) {
            $ayam_hidup = 0;
        }

        // Hitung metrics
        // HDP = (Jumlah telur / Jumlah ayam hidup) × 100
        $hdp = $ayam_hidup > 0 ? ($jumlah_butir / $ayam_hidup) * 100 : 0;

        // HHP = (Jumlah telur / Jumlah ayam awal) × 100
        $hhp = $jumlah_ayam_awal > 0 ? ($jumlah_butir / $jumlah_ayam_awal) * 100 : 0;

        // Mortality = (Jumlah ayam mati / Total ayam awal) × 100
        $mortality = $jumlah_ayam_awal > 0 ? ($ayam_mati / $jumlah_ayam_awal) * 100 : 0;

        // Simpan produksi
        ProduksiTelur::create([
            'kandang_id'       => $kandang->id,
            'user_id'          => auth()->id(),
            'tanggal_produksi' => $request->tanggal_produksi,
            'satuan_input'     => $request->satuan_input,
            'jumlah_input'     => $request->jumlah_input,
            'jumlah_butir'     => $jumlah_butir,
            'jumlah_kg'        => $jumlah_kg,
            'ayam_mati'        => $ayam_mati,
            'catatan'          => $request->catatan,
            'ayam_hidup'       => $ayam_hidup,
            'hdp'              => $hdp,
            'hhp'              => $hhp,
            'mortality'        => $mortality,
        ]);

        // Note: Stock is now calculated dynamically via StockService,
        // no manual updates to StokTelur table needed

        return redirect()->route('produksi.index')
                         ->with('success', 'Data produksi berhasil disimpan!');
    }

    public function show(string $id)
    {
        $produksi = ProduksiTelur::with('kandang', 'user')->findOrFail($id);
        return view('produksi.show', compact('produksi'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $produksi = ProduksiTelur::findOrFail($id);

        // Delete production record
        // Note: Stock is now calculated dynamically via StockService
        $produksi->delete();

        return redirect()->route('produksi.index')
                         ->with('success', 'Data produksi berhasil dihapus!');
    }
}
