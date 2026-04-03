<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Produksi</h1>
            @role('pekerja')
                <a href="{{ route('produksi.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    + Input Produksi
                </a>
            @endrole
        </div>

        <!-- Kandang Info (untuk pekerja) -->
        @role('pekerja')
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center gap-4">
                <span class="text-3xl">🏠</span>
                <div>
                    <p class="text-sm text-gray-600">Kandang yang Anda kelola:</p>
                    <p class="text-xl font-bold text-gray-900">{{ auth()->user()->kandang->nama_kandang ?? 'Belum ditentukan' }}</p>
                </div>
            </div>
        @endrole

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">No</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Tanggal</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Kandang</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Produksi (Butir)</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Produksi (Kg)</th>
                        <th class="px-6 py-4 font-bold text-gray-900">HDP %</th>
                        <th class="px-6 py-4 font-bold text-gray-900">HHP %</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Mortality %</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Pekerja</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($produksi as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">{{ ($produksi->currentPage() - 1) * $produksi->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4 font-medium">{{ $p->tanggal_produksi->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $p->kandang->nama_kandang }}</td>
                            <td class="px-6 py-4 font-bold text-blue-600">{{ number_format($p->jumlah_butir, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ number_format($p->jumlah_kg, 2, ',', '.') }} kg</td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-green-600">{{ number_format($p->hdp, 2) }}%</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-blue-600">{{ number_format($p->hhp, 2) }}%</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($p->mortality > 0)
                                    <span class="font-bold text-red-600">{{ number_format($p->mortality, 2) }}%</span>
                                @else
                                    <span class="text-gray-400">0.00%</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $p->user->name }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('produksi.show', $p) }}" class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data produksi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $produksi->links() }}
        </div>
    </div>
</x-app-layout>
