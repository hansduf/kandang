<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Data Penjualan</h1>
            <a href="{{ route('penjualan.create') }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                + Input Penjualan
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">No</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Tanggal</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Pembeli</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Pengguna</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jenis Harga</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Harga/KG</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Total Butir</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Total KG</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Items</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-900">Total Harga</th>
                        <th class="px-6 py-4 font-bold text-gray-900 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($penjualan as $p)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">{{ ($penjualan->currentPage() - 1) * $penjualan->perPage() + $loop->iteration }}</td>
                            <td class="px-6 py-4 font-medium">{{ $p->tanggal_jual->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $p->nama_pembeli ?? 'Umum' }}</td>
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
                            <td class="px-6 py-4 text-center">{{ number_format($p->detail->sum('jumlah_kg'), 3, ',', '.') }} kg</td>
                            <td class="px-6 py-4">{{ $p->detail->count() }} item</td>
                            <td class="px-6 py-4 font-bold text-green-600 text-right">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-2 justify-center">
                                    <a href="{{ route('penjualan.show', $p) }}" 
                                        class="inline-block px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition">
                                        Lihat
                                    </a>
                                    <a href="{{ route('penjualan.edit', $p) }}" 
                                        class="inline-block px-3 py-1 text-xs font-medium text-orange-600 bg-orange-50 rounded hover:bg-orange-100 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('penjualan.destroy', $p) }}" method="POST" class="inline" 
                                        onsubmit="return confirm('Hapus penjualan ini? Stok akan dikembalikan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="inline-block px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded hover:bg-red-100 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data penjualan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $penjualan->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
</x-app-layout>
