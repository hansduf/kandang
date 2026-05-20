<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900">Laporan Produksi Telur</h1>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="grid grid-cols-5 gap-4" id="laporan-filter-form">
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
                <div>
                    <label for="kandang_id" class="block text-sm font-medium text-gray-700 mb-2">Kandang</label>
                    <select name="kandang_id" id="kandang_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Semua Kandang --</option>
                        @foreach($kandangs as $k)
                            <option value="{{ $k->id }}" {{ request('kandang_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kandang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Tampilkan
                    </button>
                </div>
            </form>

            <script>
                // Auto-submit form on first load dengan default values (periode=bulan, current month/year)
                document.addEventListener('DOMContentLoaded', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    // Jika tidak ada parameter periode/bulan/tahun di URL, submit form dengan default
                    if (!urlParams.has('periode') && !urlParams.has('bulan') && !urlParams.has('tahun')) {
                        document.getElementById('laporan-filter-form').submit();
                    }
                });
            </script>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm p-6 border border-blue-200">
                <p class="text-blue-600 text-sm font-medium">Total Produksi Butir</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($totalButir, 0, ',', '.') }} Butir</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm p-6 border border-green-200">
                <p class="text-green-600 text-sm font-medium">Total Produksi KG</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($totalKg, 2, ',', '.') }} KG</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm p-6 border border-purple-200">
                <p class="text-purple-600 text-sm font-medium">Total Produksi</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($data->count()) }} Hari</p>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm p-6 border border-blue-200">
                <p class="text-blue-600 text-sm font-medium">Rata-rata HDP</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($avgHDP, 2) }}%</p>
            </div>
            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl shadow-sm p-6 border border-cyan-200">
                <p class="text-cyan-600 text-sm font-medium">Rata-rata HHP</p>
                <p class="text-3xl font-bold text-cyan-900 mt-2">{{ number_format($avgHHP, 2) }}%</p>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-sm p-6 border border-red-200">
                <p class="text-red-600 text-sm font-medium">Rata-rata Mortality</p>
                <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($avgMortality, 2) }}%</p>
            </div>
        </div>

        <!-- Grafik Production Analytics (Unified Tabs) -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="border-b border-gray-200">
                <div class="flex overflow-x-auto">
                    <button onclick="switchTab('semua')" 
                        class="tab-btn px-6 py-3 font-medium text-gray-700 border-b-2 border-transparent hover:border-blue-500 transition border-b-2 border-blue-600 text-blue-600"
                        data-kandang="semua">
                        📊 Semua Kandang
                    </button>
                    @foreach($kandangs as $idx => $kandang)
                        <button onclick="switchTab({{ $kandang->id }})" 
                            class="tab-btn px-6 py-3 font-medium text-gray-700 border-b-2 border-transparent hover:border-blue-500 transition"
                            data-kandang="{{ $kandang->id }}">
                            {{ $kandang->nama_kandang }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="p-6">
                <!-- Tab: Semua Kandang -->
                <div id="tab-semua" class="tab-content">
                    <h3 class="text-md font-bold text-gray-900 mb-4">📊 Grafik Utama - Semua Indikator Produksi</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Menampilkan ringkasan produksi dari <strong>SEMUA KANDANG</strong> yang digabung
                        (Total Produksi, Rata-rata HDP, HHP, Mortality, Total Ayam Mati)
                    </p>
                    <canvas id="chartUtama" height="80"></canvas>
                </div>

                <!-- Tabs: Individual Kandang -->
                @foreach($kandangs as $idx => $kandang)
                    <div id="tab-{{ $kandang->id }}" class="tab-content" style="display:none">
                        <h3 class="text-md font-bold text-gray-900 mb-4">Detail Produksi: {{ $kandang->nama_kandang }}</h3>
                        <canvas id="chart-kandang-{{ $kandang->id }}" height="80"></canvas>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- KPI Per Kandang Section -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-blue-50 border-l-4 border-blue-600 px-6 py-4">
                <h2 class="text-lg font-bold text-gray-900">KPI Per Kandang</h2>
                <p class="text-sm text-gray-600 mt-1">Ringkasan KPI produksi untuk setiap kandang pada periode ini</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 font-bold text-gray-900">Kandang</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Total Butir</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Total KG</th>
                            <th class="px-6 py-4 font-bold text-gray-900">HDP</th>
                            <th class="px-6 py-4 font-bold text-gray-900">HHP</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Mortality</th>
                            <th class="px-6 py-4 font-bold text-gray-900">Ayam Mati</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($kpiPerKandang as $kpi)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium">{{ $kpi['nama_kandang'] }}</td>
                                <td class="px-6 py-4 font-bold text-blue-600">{{ number_format($kpi['total_produksi_butir'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-bold text-green-600">{{ number_format($kpi['total_produksi_kg'], 2, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                        {{ number_format($kpi['rata_rata_hdp'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-cyan-100 text-cyan-800 rounded text-xs font-medium">
                                        {{ number_format($kpi['rata_rata_hhp'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded text-xs font-medium">
                                        {{ number_format($kpi['rata_rata_mortality'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">{{ $kpi['total_ayam_mati'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada data KPI untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">Tanggal</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Kandang</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Pekerja</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jumlah Butir</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jumlah KG</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium">{{ $p->tanggal_produksi->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $p->kandang->nama_kandang }}</td>
                            <td class="px-6 py-4">{{ $p->user->name }}</td>
                            <td class="px-6 py-4 font-bold">{{ number_format($p->jumlah_butir, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-bold">{{ number_format($p->jumlah_kg, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data produksi untuk periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Export Buttons -->
        <div class="flex gap-4">
            <a href="{{ route('laporan.exportProduksiPdf', request()->query()) }}" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                Export PDF
            </a>
            <a href="{{ route('laporan.exportProduksiExcel', request()->query()) }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium">
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

        // Chart Utama - Multi-metric
        const dataUtama = {!! json_encode($chartDataUtama) !!};
        new Chart(document.getElementById('chartUtama'), {
            type: 'line',
            data: {
                labels: dataUtama.labels,
                datasets: dataUtama.datasets
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
                            text: 'Produksi (Butir)',
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Persentase (%)',
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    },
                    y2: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        offset: true,
                        title: {
                            display: true,
                            text: 'Ayam Mati',
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

        // Charts per Kandang
        const perKandangData = {!! json_encode($perKandangCharts) !!};
        
        @foreach($kandangs as $kandang)
            new Chart(document.getElementById('chart-kandang-{{ $kandang->id }}'), {
                type: 'line',
                data: {
                    labels: perKandangData[{{ $kandang->id }}].labels,
                    datasets: perKandangData[{{ $kandang->id }}].datasets
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
                                text: 'Produksi (Butir)',
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Persentase (%)',
                            },
                            grid: {
                                drawOnChartArea: false,
                            }
                        },
                        y2: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            offset: true,
                            title: {
                                display: true,
                                text: 'Ayam Mati',
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
        @endforeach

        // Tab switching function
        function switchTab(kandangId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600'));
            
            // Show selected tab
            document.getElementById('tab-' + kandangId).style.display = 'block';
            event.target.classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
        }
    </script>
</x-app-layout>
