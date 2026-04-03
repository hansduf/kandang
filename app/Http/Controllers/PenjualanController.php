<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\HargaTelur;
use App\Models\Pengaturan;
use App\Models\Penjualan;
use App\Models\ProduksiTelur;
use App\Models\StokTelur;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with(['user', 'detail' => function($q) {
            $q->with('hargaTelur');
        }])
            ->latest('tanggal_jual')
            ->paginate(10);

        return view('penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        $hargaTelur = HargaTelur::aktif()->get();

        return view('penjualan.create', compact('hargaTelur'));
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
            // ALWAYS cast jumlah_butir to integer (eggs are discrete units, not decimals)
            $jumlahButir = !empty($item['jumlah_butir']) 
                ? (int) round($item['jumlah_butir'])
                : (int) round($item['jumlah_kg'] * $konversi);
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
                
                // Use jumlah_butir or calculate from jumlah_kg, ALWAYS as integer
                $jumlahButir = !empty($item['jumlah_butir']) 
                    ? (int) round($item['jumlah_butir'])
                    : (int) round($item['jumlah_kg'] * $konversi);
                
                $totalButirDijual += $jumlahButir;
                
                // Determine unit price
                $hargaSatuan = $item['satuan_jual'] === 'kg'
                    ? $harga->harga_per_kg
                    : $harga->harga_per_butir;

                // Calculate kg from butir
                $jumlahKg = round($jumlahButir / $konversi, 3);
                    
                // For kg unit, jumlah_jual is already in decimal (e.g., 1.024 kg)
                // So subtotal = jumlah (kg) × harga_per_kg
                $subtotal = $item['jumlah_jual'] * $hargaSatuan;
                
                $total += $subtotal;

                // Create detail penjualan - jumlah_butir is INTEGER
                // IMPORTANT: Save the actual price used at time of sale
                DetailPenjualan::create([
                    'penjualan_id'   => $penjualan->id,
                    'harga_telur_id' => $item['harga_telur_id'],
                    'satuan_jual'    => $item['satuan_jual'],
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
            
            // CRITICAL: Decrement stok_telur saat penjualan ditambah
            $stok = StokTelur::first();
            if ($stok) {
                $stok->decrement('stok_butir', $totalButirDijual);
                $stok->stok_kg = round($stok->stok_butir / $konversi, 3);
                $stok->save();
            }
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
        return view('penjualan.edit', compact('penjualan', 'hargaTelur'));
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
            // ALWAYS cast jumlah_butir to integer
            $jumlahButir = !empty($item['jumlah_butir']) 
                ? (int) round($item['jumlah_butir'])
                : (int) round($item['jumlah_kg'] * $konversi);
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
                
                // Use jumlah_butir or calculate from jumlah_kg, ALWAYS as integer
                $jumlahButir = !empty($item['jumlah_butir']) 
                    ? (int) round($item['jumlah_butir'])
                    : (int) round($item['jumlah_kg'] * $konversi);
                
                $hargaSatuan = $item['satuan_jual'] === 'kg'
                    ? $harga->harga_per_kg
                    : $harga->harga_per_butir;

                $jumlahKg = round($jumlahButir / $konversi, 3);
                    
                $subtotal = $item['jumlah_jual'] * $hargaSatuan;
                
                $total += $subtotal;

                // IMPORTANT: Save the actual price used at time of sale
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
            
            // CRITICAL: Update stok_telur based on difference (old vs new)
            // If new > old: more eggs sold, decrement stok_telur more
            // If new < old: fewer eggs sold, increment stok_telur back
            $butirDifference = $totalButirDijual - $oldTotalButir;  // positive = more sold, negative = fewer sold
            
            $stok = StokTelur::first();
            if ($stok && $butirDifference !== 0) {
                if ($butirDifference > 0) {
                    // Need to decrement more eggs
                    $stok->decrement('stok_butir', $butirDifference);
                } else {
                    // Return eggs back (fewer sold now)
                    $stok->increment('stok_butir', abs($butirDifference));
                }
                $stok->stok_kg = round($stok->stok_butir / $konversi, 3);
                $stok->save();
            }
        });

        return redirect()->route('penjualan.show', $penjualan)
                         ->with('success', 'Transaksi penjualan berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $penjualan = Penjualan::findOrFail($id);

        DB::transaction(function () use ($penjualan) {
            // Calculate total butir yang akan dihapus
            $totalButirDihapus = $penjualan->detail()->sum('jumlah_butir');
            
            // Delete details and penjualan
            $penjualan->detail()->delete();
            $penjualan->delete();
            
            // CRITICAL: Increment stok_telur kembali saat penjualan dihapus
            $konversi = (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')->value('nilai') ?: 16;
            $stok = StokTelur::first();
            if ($stok) {
                $stok->increment('stok_butir', $totalButirDihapus);
                $stok->stok_kg = round($stok->stok_butir / $konversi, 3);
                $stok->save();
            }
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
