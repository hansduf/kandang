<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Produksi</title>
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
        .text-center {
            text-align: center;
        }
        .amount {
            text-align: right;
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
        <h1>[LAPORAN] PRODUKSI TELUR</h1>
        <p><strong>Hans Jaya Poultry</strong></p>
        <p>Periode: {{ $periodeDisplay }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary-section">
        <div class="summary">
            <div class="summary-box">
                <div class="summary-label">Total Produksi</div>
                <span>{{ number_format($data->count()) }} Hari</span>
            </div>
            <div class="summary-box">
                <div class="summary-label">Total Butir</div>
                <span>{{ number_format($totalButir, 0, ',', '.') }}</span>
            </div>
            <div class="summary-box">
                <div class="summary-label">Total KG</div>
                <span>{{ number_format($totalKg, 3, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">Kandang</th>
                <th style="width: 15%;">Pekerja</th>
                <th style="width: 12%; text-align: right;">Butir</th>
                <th style="width: 12%; text-align: right;">KG</th>
                <th style="width: 9%; text-align: right;">HDP %</th>
                <th style="width: 9%; text-align: right;">HHP %</th>
                <th style="width: 10%; text-align: right;">Mortality %</th>
                <th style="width: 8%; text-align: right;">Ayam Mati</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $p)
                <tr>
                    <td>{{ $p->tanggal_produksi->format('d/m/Y') }}</td>
                    <td>{{ $p->kandang->nama_kandang }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td style="text-align: right;">{{ number_format($p->jumlah_butir, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($p->jumlah_kg, 3, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($p->hdp ?? 0, 2, ',', '.') }}%</td>
                    <td style="text-align: right;">{{ number_format($p->hhp ?? 0, 2, ',', '.') }}%</td>
                    <td style="text-align: right;">{{ number_format($p->mortality ?? 0, 2, ',', '.') }}%</td>
                    <td style="text-align: right;">{{ $p->ayam_mati ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">Belum ada data produksi</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right;">{{ number_format($totalButir, 0, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($totalKg, 3, ',', '.') }}</td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px; padding: 10px; background-color: #f0f0f0; border: 1px solid #ddd;">
        <div style="margin-bottom: 10px;"><strong>RINGKASAN METRIK</strong></div>
        <table style="width: 100%; margin-top: 8px;">
            <tr>
                <td style="width: 30%; border: 1px solid #ddd; padding: 6px;">Total Ayam Mati</td>
                <td style="width: 20%; border: 1px solid #ddd; padding: 6px; text-align: right;">{{ number_format($totalAyamMati, 0) }}</td>
                <td style="width: 30%; border: 1px solid #ddd; padding: 6px; text-align: right;">Rata-rata HDP</td>
                <td style="width: 20%; border: 1px solid #ddd; padding: 6px; text-align: right;">{{ number_format($avgHDP, 2, ',', '.') }}%</td>
            </tr>
            <tr>
                <td style="width: 30%; border: 1px solid #ddd; padding: 6px;">Rata-rata HHP</td>
                <td style="width: 20%; border: 1px solid #ddd; padding: 6px; text-align: right;">{{ number_format($avgHHP, 2, ',', '.') }}%</td>
                <td style="width: 30%; border: 1px solid #ddd; padding: 6px; text-align: right;">Rata-rata Mortality</td>
                <td style="width: 20%; border: 1px solid #ddd; padding: 6px; text-align: right;">{{ number_format($avgMortality, 2, ',', '.') }}%</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->name }} | <strong>Waktu:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
