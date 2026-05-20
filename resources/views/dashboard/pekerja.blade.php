<x-app-layout>
    <div class="space-y-6">
        @if(!$kandang)
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                <p class="text-red-600 font-medium">Anda belum diassign ke kandang manapun</p>
            </div>
        @else
            <!-- Header -->
            <div class="flex justify-between items-end mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $dataKandang['nama'] }}</h1>
                    <p class="text-xs text-gray-600 mt-0.5">Pekerja: {{ auth()->user()->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-600">PIC Kandang</p>
                    <p class="text-lg font-bold text-gray-900">{{ $dataKandang['pic'] }}</p>
                </div>
            </div>

            <!-- Filter Periode -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <form method="GET" class="grid grid-cols-6 gap-3" id="dashboard-filter-form">
                    <div>
                        <label for="periode" class="block text-xs font-medium text-gray-700 mb-1">Periode</label>
                        <select name="periode" id="periode" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="7hari" {{ $periode == '7hari' ? 'selected' : '' }}>7 Hari</option>
                            <option value="bulan" {{ $periode == 'bulan' ? 'selected' : '' }}>Bulan</option>
                            <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua</option>
                        </select>
                    </div>

                    <div id="bulan-filter" style="{{ $periode != 'bulan' ? 'display:none' : '' }}">
                        <label for="bulan" class="block text-xs font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" id="bulan" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ $bulan == 1 ? 'selected' : '' }}>Jan</option>
                            <option value="2" {{ $bulan == 2 ? 'selected' : '' }}>Feb</option>
                            <option value="3" {{ $bulan == 3 ? 'selected' : '' }}>Mar</option>
                            <option value="4" {{ $bulan == 4 ? 'selected' : '' }}>Apr</option>
                            <option value="5" {{ $bulan == 5 ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{ $bulan == 6 ? 'selected' : '' }}>Jun</option>
                            <option value="7" {{ $bulan == 7 ? 'selected' : '' }}>Jul</option>
                            <option value="8" {{ $bulan == 8 ? 'selected' : '' }}>Agu</option>
                            <option value="9" {{ $bulan == 9 ? 'selected' : '' }}>Sep</option>
                            <option value="10" {{ $bulan == 10 ? 'selected' : '' }}>Okt</option>
                            <option value="11" {{ $bulan == 11 ? 'selected' : '' }}>Nov</option>
                            <option value="12" {{ $bulan == 12 ? 'selected' : '' }}>Des</option>
                        </select>
                    </div>

                    <div id="tahun-filter" style="{{ $periode == 'bulan' ? '' : 'display:none' }}">
                        <label for="tahun" class="block text-xs font-medium text-gray-700 mb-1">Tahun</label>
                        <input type="number" name="tahun" id="tahun" value="{{ $tahun }}" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-3 py-2 text-sm rounded-lg hover:bg-blue-700 transition font-medium">
                            Tampilkan
                        </button>
                    </div>
                </form>

                <script>
                    // Toggle bulan & tahun filter based on periode selection
                    document.getElementById('periode').addEventListener('change', function() {
                        const bulanFilter = document.getElementById('bulan-filter');
                        const tahunFilter = document.getElementById('tahun-filter');
                        
                        if (this.value === 'bulan') {
                            bulanFilter.style.display = 'block';
                            tahunFilter.style.display = 'block';
                        } else {
                            bulanFilter.style.display = 'none';
                            tahunFilter.style.display = 'none';
                        }
                    });
                </script>
            </div>

            <!-- Kandang Info -->
            <div class="grid grid-cols-5 gap-3">
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                    <p class="text-xs text-gray-600 font-medium">Jumlah Ayam (Base)</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $dataKandang['jumlah_ayam'] }}</p>
                    <p class="text-xs text-gray-500">ekor</p>
                </div>

                @php
                    $totalAyamMati = \App\Models\ProduksiTelur::where('kandang_id', auth()->user()->kandang->id)->sum('ayam_mati');
                    $ayamAktual = $dataKandang['jumlah_ayam'] - $totalAyamMati;
                @endphp
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                    <p class="text-xs text-gray-600 font-medium">Ayam Aktual</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $ayamAktual }}</p>
                    <p class="text-xs text-gray-500">{{ $totalAyamMati }} mati</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                    <p class="text-xs text-gray-600 font-medium">Produksi Hari Ini</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $produksiHariIni }}</p>
                    <p class="text-xs text-gray-500">butir</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                    <p class="text-xs text-gray-600 font-medium">HDP Hari Ini</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ round($hdpHariIni ?? 0, 2) }}%</p>
                    <p class="text-xs text-gray-500">HDP</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
                    <p class="text-xs text-gray-600 font-medium">Rata-rata HDP</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ round($avgHDPPeriode ?? 0, 2) }}%</p>
                    <p class="text-xs text-gray-500">({{ ucfirst($periode) }})</p>
                </div>
            </div>

            <!-- Chart Performa - Combined -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="mb-3">
                    <h2 class="text-sm font-bold text-gray-900">Performa Kandang ({{ ucfirst($periode) }})</h2>
                    <p class="text-xs text-gray-600 mt-0.5">Ayam Aktual: <span class="font-bold text-green-600">{{ $ayamAktual }}</span> | Base: <span class="font-bold">{{ $dataKandang['jumlah_ayam'] }}</span></p>
                </div>
                <div style="height: 300px;">
                    <canvas id="combinedChart"></canvas>
                </div>
            </div>

            <!-- Detail Catatan -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-sm font-bold text-gray-900">Detail Performa Harian</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left font-bold text-gray-900">Tanggal</th>
                                <th class="px-4 py-2 text-left font-bold text-gray-900">Produksi</th>
                                <th class="px-4 py-2 text-left font-bold text-gray-900">HDP %</th>
                                <th class="px-4 py-2 text-left font-bold text-gray-900">HHP %</th>
                                <th class="px-4 py-2 text-left font-bold text-gray-900">Mortality %</th>
                                <th class="px-4 py-2 text-left font-bold text-gray-900">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($perforamaPeriodeTable as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($item->tgl)->format('d M') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="font-bold text-green-600">{{ $item->produksi }}</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="font-bold text-blue-600">{{ round($item->hdp ?? 0, 1) }}%</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="font-bold text-purple-600">{{ round($item->hhp ?? 0, 1) }}%</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($item->mortality > 0)
                                            <span class="font-bold text-red-600">{{ round($item->mortality ?? 0, 1) }}%</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-600 max-w-xs truncate">
                                        @if($item->catatan)
                                            <span class="text-xs">{{ $item->catatan }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 text-xs">
                                        Belum ada data untuk periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                // Prepare data
                const performaData = {!! json_encode($perforamaPeriode->map(fn($p) => [
                    'tgl' => \Carbon\Carbon::parse($p->tgl)->format('d-m-Y'),
                    'produksi' => $p->produksi,
                    'hdp' => round($p->hdp ?? 0, 2),
                    'hhp' => round($p->hhp ?? 0, 2),
                    'mortality' => round($p->mortality ?? 0, 2),
                    'ayamMati' => $p->ayam_mati ?? 0,
                ])->toArray()) !!};

                // Combined Chart
                const combinedCtx = document.getElementById('combinedChart').getContext('2d');
                new Chart(combinedCtx, {
                    type: 'line',
                    data: {
                        labels: performaData.map(p => p.tgl),
                        datasets: [
                            {
                                label: 'Produksi (Butir)',
                                data: performaData.map(p => p.produksi),
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                yAxisID: 'y',
                                tension: 0.4,
                            },
                            {
                                label: 'HDP (%)',
                                data: performaData.map(p => p.hdp),
                                borderColor: '#3b82f6',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                fill: false,
                                yAxisID: 'y1',
                                tension: 0.4,
                                pointRadius: 3,
                            },
                            {
                                label: 'HHP (%)',
                                data: performaData.map(p => p.hhp),
                                borderColor: '#06b6d4',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                fill: false,
                                yAxisID: 'y1',
                                tension: 0.4,
                                pointRadius: 3,
                            },
                            {
                                label: 'Mortality (%)',
                                data: performaData.map(p => p.mortality),
                                borderColor: '#ef4444',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                fill: false,
                                yAxisID: 'y1',
                                tension: 0.4,
                                pointRadius: 3,
                            },
                            {
                                label: 'Ayam Mati',
                                data: performaData.map(p => p.ayamMati),
                                backgroundColor: '#fbbf24',
                                borderColor: '#f59e0b',
                                borderWidth: 1,
                                yAxisID: 'y2',
                                type: 'bar',
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
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 18,
                                    boxHeight: 8,
                                    font: { size: 11 },
                                    padding: 10,
                                    usePointStyle: false,
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
                                    font: { size: 11 }
                                },
                                ticks: {
                                    callback: v => v.toLocaleString('id-ID')
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Persentase (%)',
                                    font: { size: 11 }
                                },
                                min: 0,
                                max: 100,
                                grid: {
                                    drawOnChartArea: false,
                                },
                                ticks: {
                                    callback: v => v + '%'
                                }
                            },
                            y2: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                offset: true,
                                title: {
                                    display: true,
                                    text: 'Ayam Mati (Ekor)',
                                    font: { size: 11 }
                                },
                                grid: {
                                    drawOnChartArea: false,
                                }
                            }
                        }
                    }
                });
            </script>
        @endif
    </div>
</x-app-layout>
