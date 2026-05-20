<?php

namespace App\Services;

use App\Models\ProduksiTelur;
use Carbon\Carbon;

class PenjualanReportService
{
    /**
     * Prepare chart data for penjualan by jenis harga with stock and production datasets.
     * Returns array with 'labels' and 'datasets' compatible with Chart.js used in laporan.penjualan
     */
    public function preparePenjualanChartByHargaWithStock($penjualan, $startDate, $endDate)
    {
        // Get produksi untuk periode yang sama - format dates consistently
        $produksiData = ProduksiTelur::whereBetween('tanggal_produksi', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal_produksi) as prod_date, SUM(jumlah_butir) as butir')
            ->groupByRaw("DATE(tanggal_produksi)")
            ->get();

        $produksiByDate = [];
        foreach ($produksiData as $row) {
            $dateKey = Carbon::parse($row->prod_date)->format('d-m-Y');
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
}
