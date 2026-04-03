<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-600">{{ now()->format('d F Y') }}</p>
        </div>

        <!-- Filter Periode -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="grid grid-cols-6 gap-4" id="dashboard-filter-form">
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <select name="periode" id="periode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="hari" {{ $periode == 'hari' ? 'selected' : '' }}>Pilih Hari</option>
                        <option value="7hari" {{ $periode == '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                        <option value="bulan" {{ $periode == 'bulan' ? 'selected' : '' }}>Bulan</option>
                        <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua Data</option>
                    </select>
                </div>

                <div id="tanggal-filter" style="{{ $periode != 'hari' ? 'display:none' : '' }}">
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ $tanggal }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
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

                <div id="tahun-filter" style="{{ $periode == 'bulan' ? '' : 'display:none' }}">
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <input type="number" name="tahun" id="tahun" value="{{ $tahun }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Tampilkan
                    </button>
                </div>

                <div class="flex items-end">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">{{ $startDate->format('d M Y') }}</span> 
                        hingga 
                        <span class="font-medium">{{ $endDate->format('d M Y') }}</span>
                    </p>
                </div>
            </form>

            <script>
                // Toggle tanggal, bulan & tahun filter based on periode selection
                document.getElementById('periode').addEventListener('change', function() {
                    const tanggalFilter = document.getElementById('tanggal-filter');
                    const bulanFilter = document.getElementById('bulan-filter');
                    const tahunFilter = document.getElementById('tahun-filter');
                    
                    if (this.value === 'hari') {
                        tanggalFilter.style.display = 'block';
                        bulanFilter.style.display = 'none';
                        tahunFilter.style.display = 'none';
                    } else if (this.value === 'bulan') {
                        tanggalFilter.style.display = 'none';
                        bulanFilter.style.display = 'block';
                        tahunFilter.style.display = 'block';
                    } else {
                        tanggalFilter.style.display = 'none';
                        bulanFilter.style.display = 'none';
                        tahunFilter.style.display = 'none';
                    }
                });
            </script>
        </div>

        <!-- KPI Cards Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Card 1: Stok Telur -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-sm border border-blue-200 p-3">
                <p class="text-blue-600 text-xs font-medium">Stok Telur</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($stok->stok_butir ?? 0, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-0.5">{{ number_format($stok->stok_kg ?? 0, 1, ',', '.') }} kg</p>
            </div>

            <!-- Card 2: Total Ayam Sekarang -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200 p-3">
                <p class="text-green-600 text-xs font-medium">Total Ayam Sekarang</p>
                <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($totalAyamSekarang ?? 0, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-0.5">ekor</p>
                <p class="text-green-600 text-xs mt-1 font-medium">Ayam Awal: {{ number_format($totalAyamAwal ?? 0, 0, ',', '.') }} ekor</p>
            </div>

            <!-- Card 3: Total Kematian Periode -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-sm border border-red-200 p-3">
                <p class="text-red-600 text-xs font-medium">Kematian Periode</p>
                <p class="text-2xl font-bold text-red-900 mt-1">{{ number_format($totalKematianPeriode ?? 0, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-0.5">ekor</p>
            </div>
        </div>

        <!-- KPI Cards Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Card 1: Produksi -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-sm border border-green-200 p-3">
                <p class="text-green-600 text-xs font-medium">
                    @if($periode == 'hari')
                        Produksi ({{ \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal)->format('d F') }})
                    @elseif($periode == '7hari')
                        Produksi (7 Hari)
                    @elseif($periode == 'bulan')
                        Produksi (Bulan)
                    @else
                        Produksi Total
                    @endif
                </p>
                <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($produksiPeriode ?? 0, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-0.5">butir</p>
            </div>

            <!-- Card 2: Penjualan -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-sm border border-yellow-200 p-3">
                <p class="text-yellow-600 text-xs font-medium">
                    @if($periode == 'hari')
                        Penjualan ({{ \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal)->format('d F') }})
                    @elseif($periode == '7hari')
                        Penjualan (7 Hari)
                    @elseif($periode == 'bulan')
                        Penjualan (Bulan)
                    @else
                        Penjualan Total
                    @endif
                </p>
                <p class="text-2xl font-bold text-yellow-900 mt-1">Rp {{ number_format($penjualanPeriode ?? 0, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-0.5">total transaksi</p>
            </div>

            <!-- Card 3: Kandang Aktif -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-sm border border-purple-200 p-3">
                <p class="text-purple-600 text-xs font-medium">Kandang Aktif</p>
                <p class="text-2xl font-bold text-purple-900 mt-1">{{ $jumlahKandang ?? 0 }}</p>
                <p class="text-gray-600 text-xs mt-0.5">unit</p>
            </div>

        </div>

        <!-- Metrics KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- HDP Card -->
            <div class="bg-gradient-to-br from-sky-50 to-sky-100 rounded-lg shadow-sm border border-sky-200 p-3">
                <p class="text-sky-600 text-xs font-medium">Rata-rata HDP</p>
                <p class="text-2xl font-bold text-sky-900 mt-1">{{ number_format($avgHDPPeriode ?? 0, 2) }}%</p>
                <p class="text-gray-600 text-xs mt-0.5">Periode Dipilih</p>
            </div>

            <!-- HHP Card -->
            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-lg shadow-sm border border-cyan-200 p-3">
                <p class="text-cyan-600 text-xs font-medium">Rata-rata HHP</p>
                <p class="text-2xl font-bold text-cyan-900 mt-1">{{ number_format($avgHHPPeriode ?? 0, 2) }}%</p>
                <p class="text-gray-600 text-xs mt-0.5">Periode Dipilih</p>
            </div>

            <!-- Mortality Card -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-sm border border-red-200 p-3">
                <p class="text-red-600 text-xs font-medium">Rata-rata Mortality</p>
                <p class="text-2xl font-bold text-red-900 mt-1">{{ number_format($avgMortalityPeriode ?? 0, 2) }}%</p>
                <p class="text-gray-600 text-xs mt-0.5">Periode Dipilih</p>
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg shadow-sm p-3 border border-emerald-200">
            <p class="text-emerald-600 text-xs font-medium">Status Sistem</p>
            <p class="text-lg font-bold text-emerald-900 mt-1">✅ Aktif</p>
            <p class="text-gray-600 text-xs mt-0.5">semua sistem berjalan</p>
        </div>

        <!-- Charts Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                Produksi 
                @if($periode == 'hari')
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal)->format('d F Y') }}
                @elseif($periode == '7hari')
                    7 Hari Terakhir
                @elseif($periode == 'bulan')
                    Bulan {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y') }}
                @else
                    Total Semua Data
                @endif
            </h2>
            <canvas id="chartProduksiPeriode" height="80"></canvas>
        </div>


    </div>

    <script>
        // Warna untuk setiap kandang
        const colors = [
            { line: 'rgb(59, 130, 246)', bg: 'rgba(59, 130, 246, 0.1)' },    // Blue
            { line: 'rgb(34, 197, 94)', bg: 'rgba(34, 197, 94, 0.1)' },      // Green
            { line: 'rgb(239, 68, 68)', bg: 'rgba(239, 68, 68, 0.1)' },      // Red
            { line: 'rgb(251, 146, 60)', bg: 'rgba(251, 146, 60, 0.1)' },    // Orange
            { line: 'rgb(168, 85, 247)', bg: 'rgba(168, 85, 247, 0.1)' },    // Purple
            { line: 'rgb(236, 72, 153)', bg: 'rgba(236, 72, 153, 0.1)' },    // Pink
        ];

        // Format tanggal periode
        const labelsPeriode = @json($tanggalPeriode).map(tgl => {
            const date = new Date(tgl);
            return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
        });

        // Data per kandang
        const kandangData = @json($kandangProduction);
        const datasets = [];
        let colorIndex = 0;

        Object.keys(kandangData).forEach(kandangId => {
            const kandang = kandangData[kandangId];
            const color = colors[colorIndex % colors.length];
            
            datasets.push({
                label: kandang.nama,
                data: kandang.data,
                borderColor: color.line,
                backgroundColor: color.bg,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: color.line,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            });
            
            colorIndex++;
        });

        // Chart Produksi Multi-Kandang
        new Chart(document.getElementById('chartProduksiPeriode'), {
            type: 'line',
            data: {
                labels: labelsPeriode,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 12, weight: 'bold' },
                            padding: 15,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' butir';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            color: '#666',
                            callback: function(value) {
                                return value + ' butir';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                        }
                    },
                    x: {
                        ticks: { 
                            color: '#666',
                            font: { size: 11 }
                        },
                        grid: {
                            display: false,
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
