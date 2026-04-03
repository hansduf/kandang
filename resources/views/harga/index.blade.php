<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Management Harga Telur</h1>
            <a href="{{ route('harga.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                + Input Harga Baru
            </a>
        </div>

        <!-- Grafik History Harga -->
        @if(count($chartData['labels']) > 0)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Track Record Harga</h2>
                <form method="GET" action="{{ route('harga.index') }}" class="flex items-center gap-2">
                    <label for="bulan" class="text-sm font-medium text-gray-700">Filter Bulan:</label>
                    <select name="bulan" id="bulan" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        @php
                            $dates = collect([]);
                            foreach ($chartData['labels'] as $label) {
                                $dates->push(\Carbon\Carbon::createFromFormat('d-m-Y', $label));
                            }
                            $uniqueMonths = $dates->groupBy(function ($date) {
                                return $date->format('Y-m');
                            })->keys();
                        @endphp
                        @foreach($uniqueMonths as $month)
                            @php
                                $monthCarbon = \Carbon\Carbon::createFromFormat('Y-m', $month);
                                $monthLabel = $monthCarbon->format('F Y');
                            @endphp
                            <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                                {{ $monthLabel }}
                            </option>
                        @endforeach
                    </select>
                    @if($selectedMonth)
                        <a href="{{ route('harga.index') }}" class="text-sm text-gray-500 hover:text-gray-700 ml-2">✕ Reset</a>
                    @endif
                </form>
            </div>
            <canvas id="hargaChart" height="80"></canvas>
        </div>
        @endif

        <!-- Harga Aktif -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-green-50 border-l-4 border-green-600 px-6 py-4">
                <h2 class="text-lg font-bold text-green-900">Harga Aktif (Berlaku Hari Ini)</h2>
            </div>
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">Jenis Harga</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Harga/KG</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Harga/Butir</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Berlaku Sejak</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Berakhir Pada</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Oleh</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($hargaAktif as $h)
                        <tr class="hover:bg-green-50 transition">
                            <td class="px-6 py-4 font-medium">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if($h->jenis_harga === 'kandang') bg-blue-100 text-blue-800
                                    @elseif($h->jenis_harga === 'grosir') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif
                                ">
                                    {{ ucfirst($h->jenis_harga) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-green-700">Rp {{ number_format($h->harga_per_kg, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $h->harga_per_butir ? 'Rp ' . number_format($h->harga_per_butir, 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-4">{{ $h->tanggal_berlaku->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $h->tanggal_akhir ? $h->tanggal_akhir->format('d-m-Y') : '∞ (Berlaku terus)' }}</td>
                            <td class="px-6 py-4">{{ $h->user->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('harga.edit', $h->id) }}" class="inline-block px-3 py-1 text-xs font-medium text-orange-600 bg-orange-50 rounded hover:bg-orange-100 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('harga.destroy', $h->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin hapus harga ini?')" class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Belum ada harga aktif hari ini. <a href="{{ route('harga.create') }}" class="text-green-600 font-medium">Input sekarang!</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Harga Hangus / Riwayat -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="bg-red-50 border-l-4 border-red-600 px-6 py-4">
                <h2 class="text-lg font-bold text-red-900">Riwayat Harga (Hangus)</h2>
            </div>
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">Jenis Harga</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Harga/KG</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Berlaku</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Hangus Pada</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($hargaHangus as $h)
                        <tr class="hover:bg-red-50 transition opacity-75">
                            <td class="px-6 py-4 font-medium">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if($h->jenis_harga === 'kandang') bg-blue-100 text-blue-800
                                    @elseif($h->jenis_harga === 'grosir') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif
                                ">
                                    {{ ucfirst($h->jenis_harga) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 line-through">Rp {{ number_format($h->harga_per_kg, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-xs">{{ $h->tanggal_berlaku->format('d-m-Y') }}</td>
                            <td class="px-6 py-4 text-xs font-medium text-red-600">{{ $h->tanggal_akhir ? $h->tanggal_akhir->format('d-m-Y') : '-' }}</td>
                            <td class="px-6 py-4">{{ $h->user->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada riwayat harga
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($hargaHangus->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $hargaHangus->links() }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <script>
        @if(count($chartData['labels']) > 0)
        const ctx = document.getElementById('hargaChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: {!! json_encode($chartData['datasets']) !!}
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Harga per KG (Rp)'
                        }
                    }
                }
            }
        });
        @endif
    </script>
</x-app-layout>
