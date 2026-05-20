<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Detail Penjualan - {{ $penjualan->tanggal_jual->format('d-m-Y') }}</h1>
            <a href="{{ route('penjualan.index') }}" class="text-gray-600 hover:text-gray-800">← Kembali</a>
        </div>

        <!-- Header Info -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <p class="text-gray-500 text-sm mb-1">Tanggal Penjualan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $penjualan->tanggal_jual->format('d M Y') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <p class="text-gray-500 text-sm mb-1">Pembeli</p>
                <p class="text-2xl font-bold text-gray-900">{{ $penjualan->nama_pembeli ?? 'Umum' }}</p>
            </div>
            <div class="bg-blue-50 rounded-xl shadow-sm p-6 border border-blue-200">
                <p class="text-blue-600 text-sm mb-1 font-medium">Total Telur Keluar</p>
                <p class="text-2xl font-bold text-blue-900">{{ number_format($penjualan->detail->sum('jumlah_butir'), 0, ',', '.') }} butir</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm p-6 border border-green-200">
                <p class="text-green-600 text-sm mb-1 font-medium">Total Harga</p>
                <p class="text-2xl font-bold text-green-900">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900">Detail Item</h2>
            </div>
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-900">No</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jenis Harga</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Satuan</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jumlah Jual</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jumlah Butir</th>
                        <th class="px-6 py-4 font-bold text-gray-900">Jumlah KG</th>                        <th class="px-6 py-4 font-bold text-gray-900">Gram/Butir</th>                        <th class="px-6 py-4 text-right font-bold text-gray-900">Harga/Butir</th>
                        <th class="px-6 py-4 text-right font-bold text-gray-900">Harga/KG</th>
                        <th class="px-6 py-4 font-bold text-gray-900 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($penjualan->detail as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if($item->hargaTelur->jenis_harga === 'kandang') bg-blue-100 text-blue-800
                                    @elseif($item->hargaTelur->jenis_harga === 'grosir') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif
                                ">
                                    {{ ucfirst($item->hargaTelur->jenis_harga) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ ucfirst($item->satuan_jual) }}</td>
                            <td class="px-6 py-4 font-medium">{{ number_format($item->jumlah_jual, 2) }}</td>
                            <td class="px-6 py-4 font-medium">{{ number_format($item->jumlah_butir, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-medium">{{ number_format($item->jumlah_kg, 3, ',', '.') }} kg</td>
                            <td class="px-6 py-4 text-center text-gray-600">
                                {{ $item->jumlah_butir > 0 ? number_format(($item->jumlah_kg * 1000) / $item->jumlah_butir, 2) . ' g' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600">Rp {{ number_format($item->harga_per_butir_saat_jual, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">Rp {{ number_format($item->harga_per_kg_saat_jual, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Keterangan -->
        @if($penjualan->keterangan)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Keterangan</h3>
                <p class="text-gray-600">{{ $penjualan->keterangan }}</p>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="{{ route('penjualan.index') }}" class="bg-gray-400 text-white px-6 py-3 rounded-lg hover:bg-gray-500 transition font-medium">
                Kembali
            </a>
            <a href="{{ route('penjualan.edit', $penjualan) }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                <i class="fas fa-pen text-blue-600"></i> Edit Transaksi
            </a>
            <form action="{{ route('penjualan.destroy', $penjualan) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Yakin hapus transaksi ini?')" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-medium">
                    Hapus Transaksi
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
