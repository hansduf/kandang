<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Laporan KPI per Kandang</h1>
                <p class="text-gray-600 mt-1">Performa setiap kandang untuk periode {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y') }}</p>
            </div>
            <a href="{{ route('laporan.produksi') }}" class="text-gray-600 hover:text-gray-800">← Kembali</a>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="flex gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="bulan" class="px-4 py-2 border border-gray-300 rounded-lg">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $bulan ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate($tahun, $m, 1)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" class="px-4 py-2 border border-gray-300 rounded-lg">
                        @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Tampilkan
                </button>
            </form>
        </div>

        <!-- Summary Card - Horizontal & Compact -->
        <div class="grid grid-cols-4 gap-3">
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                <p class="text-xs text-gray-600 font-medium">Total Kandang</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ count($kandangs) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                <p class="text-xs text-gray-600 font-medium">Total Produksi (Butir)</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($kandangs->sum('total_produksi_butir'), 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                <p class="text-xs text-gray-600 font-medium">Total Produksi (KG)</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($kandangs->sum('total_produksi_kg'), 2, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                <p class="text-xs text-gray-600 font-medium">Avg Mortality</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($kandangs->avg('rata_rata_mortality'), 2) }}%</p>
            </div>
        </div>

        <!-- Tabel KPI per Kandang -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Detail KPI Setiap Kandang</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold text-gray-900">Kandang</th>
                            <th class="px-6 py-3 text-left font-bold text-gray-900">PIC</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">Ayam (Ekor)</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">Hari Produksi</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">Produksi (Butir)</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">Produksi (KG)</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">HDP %</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">HHP %</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">Mortality %</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-900">Ayam Mati</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kandangs as $kandang)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $kandang['nama_kandang'] }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $kandang['pic'] }}</td>
                                <td class="px-6 py-4 text-center text-gray-700">{{ number_format($kandang['jumlah_ayam']) }}</td>
                                <td class="px-6 py-4 text-center {{ $kandang['hari_pencatatan'] > 0 ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ $kandang['hari_pencatatan'] }} hari
                                </td>
                                <td class="px-6 py-4 text-center font-semibold">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg">{{ number_format($kandang['total_produksi_butir'], 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-center font-semibold">
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-lg">{{ number_format($kandang['total_produksi_kg'], 2, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-lg font-semibold {{ $kandang['rata_rata_hdp'] >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ number_format($kandang['rata_rata_hdp'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-lg font-semibold {{ $kandang['rata_rata_hhp'] >= 85 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ number_format($kandang['rata_rata_hhp'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-lg font-semibold {{ $kandang['rata_rata_mortality'] <= 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ number_format($kandang['rata_rata_mortality'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-gray-700 font-medium">{{ $kandang['total_ayam_mati'] }} ekor</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                    📭 Tidak ada data kandang aktif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chart per Kandang -->
        @foreach ($kandangs as $kandang)
            @if ($kandang['hari_pencatatan'] > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900"><i class="fas fa-chart-line text-blue-600"></i> {{ $kandang['nama_kandang'] }} - Trend Produksi & Metrik</h3>
                </div>
                
                <div class="p-6">
                    <div style="height: 350px;">
                        <canvas id="chart-kandang-{{ $kandang['id'] }}"></canvas>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        <!-- Legend -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-bold text-blue-900 mb-3"><i class="fas fa-list text-blue-900"></i> Penjelasan Metrik</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <p><strong>HDP (Hen Day Production) %:</strong> Persentase produksi telur per hari terhadap jumlah ayam</p>
                    <p class="mt-2"><strong>HHP (Hen Housed Production) %:</strong> Persentase produksi telur per hari terhadap rata-rata ayam</p>
                </div>
                <div>
                    <p><strong>Mortality %:</strong> Persentase kematian ayam dalam periode</p>
                    <p class="mt-2"><strong>Target Optimal:</strong> HDP ≥ 80%, HHP ≥ 85%, Mortality ≤ 5%</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        const chartDataPerKandang = {!! json_encode($chartDataPerKandang) !!};
        const kandangs = {!! json_encode($kandangs) !!};

        // Render chart untuk setiap kandang
        kandangs.forEach(kandang => {
            if (kandang.hari_pencatatan > 0) {
                const data = chartDataPerKandang[kandang.id];
                const ctx = document.getElementById(`chart-kandang-${kandang.id}`).getContext('2d');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Produksi (Butir)',
                                data: data.butir,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'HDP %',
                                data: data.hdp,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y1',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'HHP %',
                                data: data.hhp,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.05)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y1',
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            },
                            {
                                label: 'Mortality %',
                                data: data.mortality,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                yAxisID: 'y1',
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.dataset.label.includes('%')) {
                                            label += context.parsed.y.toFixed(2) + '%';
                                        } else {
                                            label += context.parsed.y;
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
                                title: {
                                    display: true,
                                    text: 'Produksi (Butir)',
                                    font: { weight: 'bold' }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Metrik %',
                                    font: { weight: 'bold' }
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
