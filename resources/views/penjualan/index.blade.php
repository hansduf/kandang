<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900">Penjualan - Manajemen & Laporan</h1>

        <!-- SECTION 1: Top Action Bar -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <a href="{{ route('penjualan.create') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                + Tambah Penjualan
            </a>
        </div>

        <!-- SECTION 2: Analytics & Reporting -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            
            <!-- Periode Filter -->
            <form method="GET" class="grid grid-cols-4 gap-4 mb-6" id="filter-form">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <select name="periode" id="periode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="bulan" {{ $periode == 'bulan' ? 'selected' : '' }}>Bulan</option>
                        <option value="3bulan" {{ $periode == '3bulan' ? 'selected' : '' }}>3 Bulan</option>
                        <option value="6bulan" {{ $periode == '6bulan' ? 'selected' : '' }}>6 Bulan</option>
                        <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                    </select>
                </div>
                <div id="bulan-filter" style="{{ $periode == 'bulan' ? '' : 'display:none' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <input type="number" name="tahun" id="tahun" value="{{ $tahun }}" min="2020"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Filter
                    </button>
                </div>
            </form>

            <!-- KPI Cards -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm p-4 border border-blue-200">
                    <p class="text-blue-600 text-sm font-medium">Total Transaksi</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ $totalTransaksi }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm p-4 border border-green-200">
                    <p class="text-green-600 text-sm font-medium">Telur Terjual</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($totalButir) }} butir</p>
                    <p class="text-gray-600 text-xs mt-1">{{ number_format($totalKg, 2) }} KG</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm p-4 border border-purple-200">
                    <p class="text-purple-600 text-sm font-medium">Produksi</p>
                    <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($totalProduktButir) }} butir</p>
                    <p class="text-gray-600 text-xs mt-1">{{ number_format($totalProduktKg, 2) }} KG</p>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl shadow-sm p-4 border border-orange-200">
                    <p class="text-orange-600 text-sm font-medium">Stock Telur</p>
                    <p class="text-3xl font-bold text-orange-900 mt-2">{{ number_format($stockholTelur) }} butir</p>
                    <p class="text-gray-600 text-xs mt-1">{{ number_format($stockholTelurKg, 2) }} KG</p>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl shadow-sm p-6 border border-indigo-200 my-6">
                <p class="text-indigo-600 text-sm font-medium">Total Penjualan (Revenue)</p>
                <p class="text-4xl font-bold text-indigo-900 mt-2">Rp {{ number_format($totalHarga ?? 0, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-sm mt-2">Dari {{ $totalTransaksi }} transaksi, {{ number_format($totalButir, 0, ',', '.') }} butir telur terjual</p>
            </div>

            <!-- Chart -->
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Grafik Penjualan dengan Stok Production</h3>
                <p class="text-sm text-gray-600 mb-4">Garis Penjualan (Kiri): Penjualan telur per jenis harga (Kandang, Grosir, Konsumen) dalam jutaan Rupiah. Garis & Batang Stok (Kanan): Produksi telur (hijau) versus stok keluar/terjual (kuning) dalam satuan butir. Selisih: Produksi - Stok Keluar = Stok Tersisa.</p>
                <canvas id="salesChart" height="80"></canvas>
            </div>

            <!-- Detail Table -->
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 font-bold text-gray-900">No</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Tanggal</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Pembeli</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Pengguna</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Jenis Harga</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Total Butir</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Total KG</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Total Harga</th>
                            <th class="px-6 py-4 font-bold text-gray-900">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($penjualan as $p)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">{{ ($penjualan->currentPage() - 1) * $penjualan->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 font-medium">{{ $p->tanggal_jual->format('d-m-Y') }}</td>
                                <td class="px-6 py-4">{{ $p->nama_pembeli ?? '-' }}</td>
                                <td class="px-6 py-4 text-xs">{{ $p->user->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $jenisList = $p->detail->pluck('hargaTelur.jenis_harga')->unique()->values();
                                    @endphp
                                    @foreach($jenisList as $jenis)
                                        <span class="px-2 py-1 rounded text-xs font-medium
                                            @if($jenis === 'kandang') bg-blue-100 text-blue-800
                                            @elseif($jenis === 'grosir') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif
                                        ">
                                            {{ ucfirst($jenis) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 font-bold">{{ number_format($p->detail->sum('jumlah_butir'), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-bold">{{ number_format($p->detail->sum('jumlah_kg'), 2, ',', '.') }}</td>
                                <td class="px-6 py-4 font-bold text-green-600">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('penjualan.show', $p) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-blue-50 text-blue-700 hover:bg-blue-100">Lihat</a>
                                        <a href="{{ route('penjualan.edit', $p) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-yellow-50 text-yellow-700 hover:bg-yellow-100">Edit</a>
                                        <form action="{{ route('penjualan.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Hapus?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-red-50 text-red-700 hover:bg-red-100">Hapus</button>
                                        </form>
                                    </div>
                                </td>
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

            <!-- Pagination -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <label class="text-sm font-medium">Per page: 
                        <select name="per_page" onchange="const params = new URLSearchParams(window.location.search); params.set('per_page', this.value); window.location.href = '?'+params.toString();">
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </label>
                </div>
                <div>
                    {{ $penjualan->links() }}
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-4">
                <a href="{{ route('laporan.exportPenjualanPdf', request()->query()) }}" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                    📥 Export PDF
                </a>
                <a href="{{ route('laporan.exportPenjualanExcel', request()->query()) }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                    📥 Export Excel
                </a>
            </div>

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

        // Chart initialization
        const chartData = {!! json_encode($chartData) !!};
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Penjualan (Juta Rp)',
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Produksi (Butir)',
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                }
            }
        });
    </script>
</x-app-layout>
