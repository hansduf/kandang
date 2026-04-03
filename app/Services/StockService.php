<?php

namespace App\Services;

use App\Models\DetailPenjualan;
use App\Models\ProduksiTelur;
use App\Models\Pengaturan;
use Carbon\Carbon;

class StockService
{
    /**
     * Calculate available stock with proper carryover accounting
     * Using CUMULATIVE method (all-time before period)
     * 
     * Formula: Stock = Opening Balance + Production (Period) - Sales (Period)
     * Opening Balance = All Production Before Period - All Sales Before Period
     */
    public function calculateAvailableStock($startDate = null, $endDate = null)
    {
        // Default: current month
        if (!$startDate) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Parse dates
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // 1. Calculate opening balance (CUMULATIVE: all before period)
        $openingBalance = $this->calculateOpeningBalance($startDate);

        // 2. Calculate production in this period
        $productionInPeriod = (int) ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->sum('jumlah_butir');

        // 3. Calculate sales in this period
        $salesInPeriod = (int) DetailPenjualan::whereHas('penjualan', function($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_jual', [$startDate, $endDate]);
        })->sum('jumlah_butir');

        // 4. Calculate available stock
        $availableStock = (int) ($openingBalance + $productionInPeriod - $salesInPeriod);

        return max(0, $availableStock); // Don't go negative
    }

    /**
     * Calculate opening balance = closing of all days BEFORE period start
     * CUMULATIVE method: All Production Before - All Sales Before
     */
    private function calculateOpeningBalance($periodStart)
    {
        // All production BEFORE this period start
        $allProductionBefore = (int) ProduksiTelur::where('tanggal_produksi', '<', $periodStart)
            ->sum('jumlah_butir');

        // All sales BEFORE this period start
        $allSalesBefore = (int) DetailPenjualan::whereHas('penjualan', function($q) use ($periodStart) {
            $q->where('tanggal_jual', '<', $periodStart);
        })->sum('jumlah_butir');

        // Opening balance = accumulated production - accumulated sales
        return (int) ($allProductionBefore - $allSalesBefore);
    }

    /**
     * Get konversi factor from Pengaturan
     */
    public function getKonversiFactor()
    {
        return (float) Pengaturan::where('kunci', 'konversi_butir_per_kg')
            ->value('nilai') ?: 16;
    }

    /**
     * Convert butir to kg
     */
    public function butirToKg($butir)
    {
        return $butir / $this->getKonversiFactor();
    }

    /**
     * Convert kg to butir
     */
    public function kgToButir($kg)
    {
        return $kg * $this->getKonversiFactor();
    }
}
