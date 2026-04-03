<x-app-layout>
    <div class="space-y-6">
        @if(!$kandang)
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                <p class="text-red-600 font-medium">Anda belum diassign ke kandang manapun</p>
            </div>
        @else
            <!-- Header -->
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $dataKandang['nama'] }}</h1>
                    <p class="text-gray-600 mt-1">Pekerja: {{ auth()->user()->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">PIC Kandang</p>
                    <p class="text-lg font-bold text-gray-900">{{ $dataKandang['pic'] }}</p>
                </div>
            </div>

            <!-- Filter Periode -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <form method="GET" class="grid grid-cols-5 gap-4" id="dashboard-filter-form">
                    <div>
                        <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                        <select name="periode" id="periode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="7hari" {{ $periode == '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                            <option value="bulan" {{ $periode == 'bulan' ? 'selected' : '' }}>Bulan</option>
                            <option value="semua" {{ $periode == 'semua' ? 'selected' : '' }}>Semua Data</option>
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
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-6">
                    <p class="text-sm text-gray-600 font-medium">Jumlah Ayam (Base)</p>
                    <p class="text-3xl font-bold text-blue-700 mt-2">{{ $dataKandang['jumlah_ayam'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">ekor</p>
                </div>

                @php
                    $totalAyamMati = \App\Models\ProduksiTelur::where('kandang_id', auth()->user()->kandang->id)->sum('ayam_mati');
                    $ayamAktual = $dataKandang['jumlah_ayam'] - $totalAyamMati;
                @endphp
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-6">
                    <p class="text-sm text-gray-600 font-medium">Ayam Aktual</p>
                    <p class="text-3xl font-bold text-green-700 mt-2">{{ $ayamAktual }}</p>
                    <p class="text-xs text-gray-500 mt-1">ekor ({{ $totalAyamMati }} mati)</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-6">
                    <p class="text-sm text-gray-600 font-medium">Produksi Hari Ini</p>
                    <p class="text-3xl font-bold text-green-700 mt-2">{{ $produksiHariIni }}</p>
                    <p class="text-xs text-gray-500 mt-1">butir</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-6">
                    <p class="text-sm text-gray-600 font-medium">HDP Hari Ini %</p>
                    <p class="text-3xl font-bold text-blue-700 mt-2">{{ round($hdpHariIni ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Hen Day Production</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-6">
                    <p class="text-sm text-gray-600 font-medium">Rata-rata HDP ({{ ucfirst($periode) }})</p>
                    <p class="text-3xl font-bold text-purple-700 mt-2">{{ round($avgHDPPeriode ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">%</p>
                </div>
            </div>

            <!-- Chart Performa -->
            <div class="grid grid-cols-2 gap-6">
                <!-- Produksi Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Produksi ({{ ucfirst($periode) }})</h2>
                            <p class="text-sm text-gray-600 mt-1">Ayam Aktual: <span class="font-bold text-green-600">{{ $ayamAktual }} ekor</span></p>
                        </div>
                    </div>
                    <canvas id="produksiChart"></canvas>
                </div>

                <!-- HDP Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">HDP % ({{ ucfirst($periode) }})</h2>
                            <p class="text-sm text-gray-600 mt-1">Base Ayam: <span class="font-bold text-blue-600">{{ $dataKandang['jumlah_ayam'] }} ekor</span></p>
                        </div>
                    </div>
                    <canvas id="hdpChart"></canvas>
                </div>

                <!-- HHP Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">HHP % ({{ ucfirst($periode) }})</h2>
                            <p class="text-sm text-gray-600 mt-1">Berdasarkan: <span class="font-bold text-purple-600">Base {{ $dataKandang['jumlah_ayam'] }} ekor</span></p>
                        </div>
                    </div>
                    <canvas id="hhpChart"></canvas>
                </div>

                <!-- Mortality Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Mortality % ({{ ucfirst($periode) }})</h2>
                            <p class="text-sm text-gray-600 mt-1">Total Kematian: <span class="font-bold text-red-600">{{ $totalAyamMatiPeriode }} ekor</span></p>
                        </div>
                    </div>
                    <canvas id="mortalityChart"></canvas>
                </div>
            </div>

            <!-- Detail Catatan -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Detail Performa Harian</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-900">Tanggal</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-900">Produksi (Butir)</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-900">HDP %</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-900">HHP %</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-900">Mortality %</th>
                                <th class="px-6 py-3 text-left font-bold text-gray-900">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($perforamaPeriode as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($item->tgl)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="font-bold text-green-600">{{ $item->produksi }} butir</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="font-bold text-blue-600">{{ round($item->hdp ?? 0, 2) }}%</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="font-bold text-purple-600">{{ round($item->hhp ?? 0, 2) }}%</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($item->mortality > 0)
                                            <span class="font-bold text-red-600">{{ round($item->mortality ?? 0, 2) }}%</span>
                                        @else
                                            <span class="text-gray-500">0.00%</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        @if($item->catatan)
                                            <p class="text-sm">{{ $item->catatan }}</p>
                                        @else
                                            <span class="text-gray-400 italic">Tidak ada catatan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
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
                    'tgl' => \Carbon\Carbon::parse($p->tgl)->format('d M'),
                    'produksi' => $p->produksi,
                    'hdp' => round($p->hdp ?? 0, 2),
                    'hhp' => round($p->hhp ?? 0, 2),
                    'mortality' => round($p->mortality ?? 0, 2),
                ])->toArray()) !!};

                // Produksi Chart
                const produksiCtx = document.getElementById('produksiChart').getContext('2d');
                new Chart(produksiCtx, {
                    type: 'line',
                    data: {
                        labels: performaData.map(p => p.tgl),
                        datasets: [{
                            label: 'Produksi (Butir)',
                            data: performaData.map(p => p.produksi),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#10b981',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                ticks: { callback: v => v + ' butir' }
                            }
                        }
                    }
                });

                // HDP Chart
                const hdpCtx = document.getElementById('hdpChart').getContext('2d');
                new Chart(hdpCtx, {
                    type: 'line',
                    data: {
                        labels: performaData.map(p => p.tgl),
                        datasets: [{
                            label: 'HDP %',
                            data: performaData.map(p => p.hdp),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: v => v + '%' }
                            }
                        }
                    }
                });

                // HHP Chart
                const hhpCtx = document.getElementById('hhpChart').getContext('2d');
                new Chart(hhpCtx, {
                    type: 'line',
                    data: {
                        labels: performaData.map(p => p.tgl),
                        datasets: [{
                            label: 'HHP %',
                            data: performaData.map(p => p.hhp),
                            borderColor: '#a855f7',
                            backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#a855f7',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: v => v + '%' }
                            }
                        }
                    }
                });

                // Mortality Chart
                const mortalityCtx = document.getElementById('mortalityChart').getContext('2d');
                new Chart(mortalityCtx, {
                    type: 'line',
                    data: {
                        labels: performaData.map(p => p.tgl),
                        datasets: [{
                            label: 'Mortality %',
                            data: performaData.map(p => p.mortality),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#ef4444',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                max: 100,
                                ticks: { callback: v => v + '%' }
                            }
                        }
                    }
                });
            </script>
        @endif
    </div>
</x-app-layout>
