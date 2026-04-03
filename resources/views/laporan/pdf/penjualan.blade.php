<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 11px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 3px 0;
            color: #666;
        }
        .summary-section {
            margin-bottom: 15px;
        }
        .summary {
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 8px;
            background-color: #f9f9f9;
            display: inline-block;
            width: 32%;
            margin-right: 1%;
            vertical-align: top;
        }
        .summary-box:nth-child(3n) {
            margin-right: 0;
        }
        .summary-box span {
            display: block;
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }
        .summary-label {
            color: #666;
            font-size: 9px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #333;
            color: #fff;
            padding: 8px;
            text-align: left;
            border: 1px solid #333;
            font-size: 10px;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .amount {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #eee;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐔 LAPORAN PENJUALAN TELUR</h1>
        <p>Hans Jaya Poultry</p>
        <p>Periode: {{ $periodeName ?? 'Periode' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary-section">
        <div class="summary">
            <div class="summary-box">
                <div class="summary-label">Total Transaksi</div>
                <span>{{ number_format($totalTransaksi ?? 0) }}</span>
            </div>
            <div class="summary-box">
                <div class="summary-label">Total Butir Terjual</div>
                <span>{{ number_format($totalButir ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="summary-box">
                <div class="summary-label">Total KG Terjual</div>
                <span>{{ number_format(($totalButir ?? 0) / 16, 3, ',', '.') }}</span>
            </div>
        </div>

        <div class="summary">
            <div class="summary-box">
                <div class="summary-label">Total Penjualan</div>
                <span>Rp {{ number_format($totalHarga ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="summary-box">
                <div class="summary-label">Rata-rata per Transaksi</div>
                <span>Rp {{ number_format(($totalTransaksi ?? 0) > 0 ? ($totalHarga ?? 0) / ($totalTransaksi ?? 1) : 0, 0, ',', '.') }}</span>
            </div>
            <div class="summary-box">
                <div class="summary-label">Rata-rata Price per KG</div>
                <span>Rp {{ number_format(($totalButir ?? 0) > 0 ? ($totalHarga ?? 0) / (($totalButir ?? 0) / 16) : 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">Pembeli</th>
                <th style="width: 12%;">Jenis Harga</th>
                <th style="width: 8%;">Butir</th>
                <th style="width: 8%;">KG</th>
                <th style="width: 10%;">Harga/KG</th>
                <th style="width: 10%;">Harga/Butir</th>
                <th style="width: 12%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expandedData ?? collect() as $penjualan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $penjualan->tanggal_jual->format('d/m/Y') }}</td>
                    <td>{{ $penjualan->nama_pembeli ?? 'Umum' }}</td>
                    <td>
                        @switch($penjualan->jenis_harga_filter ?? 'unknown')
                            @case('kandang')
                                Kandang
                                @break
                            @case('grosir')
                                Grosir
                                @break
                            @case('konsumen')
                                Konsumen
                                @break
                            @default
                                {{ $penjualan->jenis_harga_filter }}
                        @endswitch
                    </td>
                    <td style="text-align: center;">{{ number_format($penjualan->detail->sum('jumlah_butir'), 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ number_format($penjualan->detail->sum('jumlah_kg'), 3, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($penjualan->detail->first()?->harga_per_kg_saat_jual ?? 0, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($penjualan->detail->first()?->harga_per_butir_saat_jual ?? 0, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format($penjualan->detail->sum('subtotal'), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">Belum ada data penjualan</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL:</td>
                <td style="text-align: center;">{{ number_format($totalButir ?? 0, 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ number_format(($totalButir ?? 0) / 16, 3, ',', '.') }}</td>
                <td colspan="2"></td>
                <td class="amount">Rp {{ number_format($totalHarga ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dicetak oleh: {{ auth()->user()->name }} | {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="footer">
        <p>Laporan ini dicetak oleh: {{ auth()->user()->name }}</p>
    </div>
</body>
</html>
