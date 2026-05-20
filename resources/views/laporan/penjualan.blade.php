<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900">Laporan Penjualan Telur</h1>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="grid grid-cols-4 gap-4">
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <select name="periode" id="periode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="bulan" {{ $periode == 'bulan' ? 'selected' : '' }}>Bulan</option>
                        <option value="3bulan" {{ $periode == '3bulan' ? 'selected' : '' }}>3 Bulan</option>
                        <option value="6bulan" {{ $periode == '6bulan' ? 'selected' : '' }}>6 Bulan</option>
                        <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                    </select>
                </div>
                <div id="bulan-filter" style="{{ $periode != 'bulan' ? 'display:none' : '' }}">
                    <label for="bulan" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="bulan" id="bulan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="1" {{ $bulan == 1 ? 'selected' : '' }}>Januari</option>
                        <option value="2" {{ $bulan == 2 ? 'selected' : '' }}>Februari</option>
                        <option value="3" {{ $bulan == 3 ? 'selected' : '' }}>Maret</option>
                        <option value="4" {{ $bulan == 4 ? 'selected' : '' }}>April</option>
                        <option value="5" {{ $bulan == 5 ? 'selected' : '' }}>Mei</option>
                        <option value="6" {{ $bulan == 6 ? 'selected' : '' }}>Juni</option>
                        <option value="7" {{ $bulan == 7 ? 'selected' : '' }}>Juli</option>
                        <option value="8" {{ $bulan == 8 ? 'selected' : '' }}>Agustus</option>
                        <option value="9" {{ $bulan == 9 ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $bulan == 10 ? 'selected' : '' }}>Oktober</option>
                        <option value="11" {{ $bulan == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $bulan == 12 ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>
                <div id="tahun-filter" style="{{ $periode == 'bulan' || $periode == '3bulan' || $periode == '6bulan' ? '' : 'display:none' }}">
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <input type="number" name="tahun" id="tahun" value="{{ request('tahun', now()->year) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Chart -->
        @if(count($chartData['labels']) > 0)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Grafik Penjualan dengan Stok Production</h2>
            <p class="text-sm text-gray-600 mb-4">
                <span class="font-medium">Garis Penjualan (Kiri):</span> Penjualan telur per jenis harga (Kandang, Grosir, Konsumen) dalam jutaan Rupiah.
                <span class="font-medium ml-4">Garis & Batang Stok (Kanan):</span> Produksi telur (hijau) versus stok keluar/terjual (kuning) dalam satuan butir.
                <span class="font-medium ml-4">Selisih:</span> Produksi - Stok Keluar = Stok Tersisa.
            </p>
            <canvas id="penjualanChart" height="80"></canvas>
        </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm p-6 border border-blue-200">
                <p class="text-blue-600 text-sm font-medium">Total Transaksi</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($totalTransaksi) }}</p>
                <p class="text-gray-600 text-xs mt-1">transaksi penjualan</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm p-6 border border-green-200">
                <p class="text-green-600 text-sm font-medium">Telur Terjual</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($totalButir, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-1">{{ number_format($totalKgCalc, 2, ',', '.') }} kg</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm p-6 border border-purple-200">
                <p class="text-purple-600 text-sm font-medium">Produksi</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($totalProduktButir, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-1">{{ number_format($totalProduktKgCalc, 2, ',', '.') }} kg</p>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-sm p-6 border border-yellow-200">
                <p class="text-yellow-600 text-sm font-medium">Stock Telur</p>
                <p class="text-3xl font-bold text-yellow-900 mt-2">{{ number_format($selisihButir, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-1">{{ number_format($selisihKg, 2, ',', '.') }} kg</p>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl shadow-sm p-6 border border-indigo-200 col-span-full">
            <p class="text-indigo-600 text-sm font-medium">Total Penjualan (Revenue)</p>
            <p class="text-4xl font-bold text-indigo-900 mt-2">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
            <p class="text-gray-600 text-sm mt-2">Dari {{ $totalTransaksi }} transaksi, {{ number_format($totalButir, 0, ',', '.') }} butir telur terjual</p>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-gray-900">Detail Penjualan</h3>
                <div class="flex items-center gap-2">
                    <label for="per_page" class="text-sm font-medium text-gray-700">Tampilkan per halaman:</label>
                    <form method="GET" class="flex items-center gap-2">
                        @foreach(request()->query() as $key => $value)
                            @if($key !== 'per_page' && $key !== 'page')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select name="per_page" class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="25" {{ request('per_page', 50) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('per_page', 50) == 200 ? 'selected' : '' }}>200</option>
                            <option value="500" {{ request('per_page', 50) == 500 ? 'selected' : '' }}>Semua</option>
                        </select>
                    </form>
                </div>
            </div>
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">No</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Tanggal</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Pembeli</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Pengguna</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jenis Harga</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Harga/KG</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Butir</th>
                        <th class="px-6 py-4 font-bold text-gray-900">KG</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-900">Total Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $idx => $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4 font-medium">{{ $p->tanggal_jual->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $p->nama_pembeli ?? 'Umum' }}</td>
                            <td class="px-6 py-4 text-xs">{{ $p->user->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $jenis = $p->jenis_harga_filter ?? $p->detail->first()?->hargaTelur->jenis_harga ?? 'unknown';
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    @if($jenis === 'kandang') bg-blue-100 text-blue-800
                                    @elseif($jenis === 'grosir') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif
                                ">
                                    {{ ucfirst($jenis) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $harkaPerKgSaat = $p->detail->first()?->harga_per_kg_saat_jual;
                                @endphp
                                @if($harkaPerKgSaat)
                                    <span class="font-medium text-gray-900">Rp {{ number_format($harkaPerKgSaat, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">{{ number_format($p->detail->sum('jumlah_butir'), 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">{{ number_format($p->detail->sum('jumlah_kg'), 3, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold text-green-600 text-right">Rp {{ number_format($p->detail->sum('subtotal'), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data penjualan untuk periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Info & Links -->
        <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col gap-4">
            <div class="text-sm text-gray-600">
                <p class="font-medium">
                    Menampilkan 
                    <span class="font-bold text-gray-900">{{ ($data->currentPage() - 1) * $data->perPage() + 1 }}</span> 
                    sampai 
                    <span class="font-bold text-gray-900">{{ min($data->currentPage() * $data->perPage(), $totalExpanded) }}</span>
                    dari 
                    <span class="font-bold text-gray-900">{{ $totalExpanded }}</span>
                    hasil
                </p>
            </div>
            <div class="flex justify-center">
                {{ $data->links() }}
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="flex gap-4">
            <a href="{{ route('laporan.exportPenjualanPdf', request()->query()) }}" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                Export PDF
            </a>
            <a href="{{ route('laporan.exportPenjualanExcel', request()->query()) }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                Export Excel
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <script>
        // Periode filter logic
        document.getElementById('periode').addEventListener('change', function() {
            const bulanFilter = document.getElementById('bulan-filter');
            const tahunFilter = document.getElementById('tahun-filter');
            
            if (this.value === 'bulan') {
                bulanFilter.style.display = 'block';
                tahunFilter.style.display = 'block';
            } else if (this.value === 'semua') {
                bulanFilter.style.display = 'none';
                tahunFilter.style.display = 'none';
            } else {
                bulanFilter.style.display = 'none';
                tahunFilter.style.display = 'block';
            }
        });

        // Chart
        @if(count($chartData['labels']) > 0)
        const ctx = document.getElementById('penjualanChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: {!! json_encode($chartData['datasets']) !!}
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 12, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    if (context.dataset.yAxisID === 'y1') {
                                        let butir = Math.round(context.parsed.y);
                                        let kg = (butir / 16).toFixed(3);
                                        let butiFormat = butir.toLocaleString('id-ID');
                                        label += butiFormat + ' butir (' + kg + ' kg)';
                                    } else {
                                        label += 'Rp ' + number_format(Math.round(context.parsed.y * 1000000));
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Penjualan (Jutaan Rp)',
                            font: { size: 12, weight: 'bold' }
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value;
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Produksi & Stok (Butir)',
                            font: { size: 12, weight: 'bold' }
                        },
                        ticks: {
                            callback: function(value) {
                                return number_format(value);
                            }
                        }
                    }
                }
            }
        });

        // Helper function for number formatting
        function number_format(num) {
            if (num >= 1000000) {
                return Math.round(num / 1000000) + 'M';
            } else if (num >= 1000) {
                return Math.round(num / 1000) + 'K';
            }
            return Math.round(num);
        }
        @endif
    </script>
</x-app-layout>
