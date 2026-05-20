<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Data Kandang</h1>
            <a href="{{ route('kandang.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                + Tambah Kandang
            </a>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="grid grid-cols-4 gap-4" id="laporan-filter-form">
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
                    <input type="number" name="tahun" id="tahun" value="{{ $tahun }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $totalKandang = $kandang->total();
                $totalAyam = \App\Models\Kandang::sum('jumlah_ayam');
                $totalProduksi = 0;
                foreach($kandangData as $data) {
                    $totalProduksi += $data['produksi_total'];
                }
            @endphp
            
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm p-6 border border-blue-200">
                <p class="text-blue-600 text-sm font-medium">Total Kandang</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">{{ $totalKandang }}</p>
                <p class="text-gray-600 text-xs mt-1">unit</p>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm p-6 border border-green-200">
                <p class="text-green-600 text-sm font-medium">Total Ayam</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($totalAyam) }}</p>
                <p class="text-gray-600 text-xs mt-1">ekor aktual</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm p-6 border border-purple-200">
                <p class="text-purple-600 text-sm font-medium">Produksi (30 Hari)</p>
                <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($totalProduksi, 0, ',', '.') }}</p>
                <p class="text-gray-600 text-xs mt-1">butir</p>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-sm p-6 border border-red-200">
                <p class="text-red-600 text-sm font-medium">Kematian Ayam (30 Hari)</p>
                <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($totalKematian) }}</p>
                <p class="text-gray-600 text-xs mt-1">ekor</p>
            </div>
        </div>

        <!-- Detail Kandang Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($kandang as $k)
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 {{ $loop->first ? 'border-l-blue-500' : 'border-l-green-500' }}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $k->nama_kandang }}</h3>
                            @if($k->pic)
                                <p class="text-sm text-gray-600 mt-1">PIC: <span class="font-medium">{{ $k->pic->name }}</span></p>
                            @endif
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $k->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($k->status) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-6 gap-3">
                        <div class="col-span-2 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-3 border border-blue-200">
                            <p class="text-blue-600 text-sm font-medium">Kapasitas Kandang</p>
                            <p class="text-2xl font-bold text-blue-900 mt-2">{{ number_format($k->jumlah_ayam) }}</p>
                            <p class="text-gray-600 text-xs mt-1">ekor (base)</p>
                        </div>
                        
                        <div class="col-span-2 bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-3 border border-green-200">
                            <p class="text-green-600 text-sm font-medium">Ayam Aktual</p>
                            <p class="text-2xl font-bold text-green-900 mt-2">{{ number_format($kandangData[$k->id]['ayam_aktual_sekarang']) }}</p>
                            <p class="text-gray-600 text-xs mt-1">ekor</p>
                        </div>

                        <div class="col-span-2 bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-3 border border-red-200">
                            <p class="text-red-600 text-sm font-medium">Kematian (Periode)</p>
                            <p class="text-2xl font-bold text-red-900 mt-2">{{ number_format($kandangData[$k->id]['total_ayam_mati']) }}</p>
                            <p class="text-gray-600 text-xs mt-1">ekor</p>
                        </div>

                        <div class="col-span-3 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-3 border border-purple-200">
                            <p class="text-purple-600 text-sm font-medium">Produksi (Periode)</p>
                            <p class="text-2xl font-bold text-purple-900 mt-2">{{ number_format($kandangData[$k->id]['produksi_total'], 0, ',', '.') }}</p>
                            <p class="text-gray-600 text-xs mt-1">butir</p>
                        </div>

                        <div class="col-span-3 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-3 border border-orange-200">
                            <p class="text-orange-600 text-sm font-medium">Avg HDP</p>
                            <p class="text-2xl font-bold text-orange-900 mt-2">{{ number_format($kandangData[$k->id]['rata_rata_hdp'], 1) }}%</p>
                            <p class="text-gray-600 text-xs mt-1">efisiensi</p>
                        </div>
                    </div>

                    @if($k->keterangan)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-600">
                                <span class="font-medium">Keterangan:</span> {{ $k->keterangan }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>



        <!-- Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Kematian Ayam Per Hari</h2>
            <p class="text-sm text-gray-600 mb-4">
                Menampilkan jumlah kematian ayam per kandang setiap hari selama periode terpilih.
            </p>
            <canvas id="produksiChart" height="80"></canvas>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">No</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Nama Kandang</th>
                        <th class="px-6 py-4 font-bold text-gray-900">PIC</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jumlah Ayam</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Produksi 30 Hari</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Avg HDP</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Mortalitas</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Status</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($kandang as $k)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">{{ ($kandang->currentPage() - 1) * $kandang->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <div class="font-semibold">{{ $k->nama_kandang }}</div>
                                @if($k->keterangan)
                                    <div class="text-xs text-gray-500 mt-1">{{ $k->keterangan }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($k->pic)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ $k->pic->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium">{{ number_format($k->jumlah_ayam) }} ekor</td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ number_format($kandangData[$k->id]['produksi_total'], 0, ',', '.') }} butir</div>
                                <div class="text-xs text-gray-500">{{ number_format($kandangData[$k->id]['produksi_kg'], 1) }} kg</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg font-bold
                                    {{ $kandangData[$k->id]['rata_rata_hdp'] >= 90 ? 'bg-green-100 text-green-800' : ($kandangData[$k->id]['rata_rata_hdp'] >= 80 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($kandangData[$k->id]['rata_rata_hdp'], 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ number_format($kandangData[$k->id]['rata_rata_mortality'], 2) }}%</div>
                                <div class="text-xs text-gray-500">{{ $kandangData[$k->id]['total_ayam_mati'] }} ekor</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                                    {{ $k->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($k->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                <a href="{{ route('kandang.edit', $k) }}" class="inline-block px-3 py-1 text-xs font-medium text-orange-600 bg-orange-50 rounded hover:bg-orange-100 transition">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('kandang.destroy', $k) }}" style="display:inline;" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data kandang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $kandang->links() }}
        </div>
    </div>

    <!-- Chart.js Dependency -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    
    <script>
        // Toggle month filter visibility
        const periodeSelect = document.getElementById('periode');
        const bulanFilter = document.getElementById('bulan-filter');
        const tahunFilter = document.getElementById('tahun-filter');

        periodeSelect.addEventListener('change', function() {
            if (this.value === 'bulan') {
                bulanFilter.style.display = 'block';
                tahunFilter.style.display = 'block';
            } else if (this.value === '3bulan' || this.value === '6bulan') {
                bulanFilter.style.display = 'none';
                tahunFilter.style.display = 'block';
            } else {
                bulanFilter.style.display = 'none';
                tahunFilter.style.display = 'none';
            }
        });

        // Auto-submit form when period changes
        periodeSelect.addEventListener('change', function() {
            document.getElementById('laporan-filter-form').submit();
        });

        // Kematian Ayam Line Chart
        const produksiCtx = document.getElementById('produksiChart').getContext('2d');
        
        const colors = [
            'rgb(59, 130, 246)',     // Blue
            'rgb(34, 197, 94)',      // Green
            'rgb(168, 85, 247)',     // Purple
            'rgb(249, 115, 22)',     // Orange
            'rgb(239, 68, 68)',      // Red
            'rgb(236, 72, 153)',     // Pink
            'rgb(6, 182, 212)',      // Cyan
            'rgb(14, 165, 233)',     // Sky
            'rgb(245, 158, 11)',     // Amber
            'rgb(139, 92, 246)',     // Violet
        ];
        
        // Function to get color with cycling
        function getChartColor(index) {
            return colors[index % colors.length];
        }

        const datasets = [
            @foreach($kandang as $index => $k)
                {
                    label: '{{ $k->nama_kandang }}',
                    data: {!! json_encode($kandangChartMati[$k->id] ?? []) !!},
                    borderColor: getChartColor({{ $index }}),
                    backgroundColor: getChartColor({{ $index }}).replace('rgb', 'rgba').replace(')', ', 0.1)'),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: getChartColor({{ $index }}),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
            @endforeach
        ];

        new Chart(produksiCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels->toArray()) !!},
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
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Kematian (ekor)',
                            font: {
                                weight: 'bold'
                            }
                        },
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(200, 200, 200, 0.1)',
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
