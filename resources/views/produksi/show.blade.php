<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Detail Produksi</h1>
                <a href="{{ route('produksi.index') }}" class="text-blue-600 hover:text-blue-800">← Kembali</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Panel -->
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600 uppercase">Kandang</p>
                        <p class="text-xl font-bold text-gray-900">{{ $produksi->kandang->nama_kandang }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600 uppercase">Tanggal Produksi</p>
                        <p class="text-xl font-bold text-gray-900">{{ $produksi->tanggal_produksi->format('d F Y') }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600 uppercase">Pekerja</p>
                        <p class="text-xl font-bold text-gray-900">{{ $produksi->user->name }}</p>
                    </div>
                </div>

                <!-- Produksi Info -->
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-xs text-gray-600 uppercase">Total Butir</p>
                        <p class="text-3xl font-bold text-blue-700">{{ number_format($produksi->jumlah_butir, 0, ',', '.') }}</p>
                    </div>

                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-xs text-gray-600 uppercase">Total Kilogram</p>
                        <p class="text-3xl font-bold text-green-700">{{ number_format($produksi->jumlah_kg, 2, ',', '.') }} kg</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600 uppercase">Satuan Input</p>
                        <p class="text-lg font-bold text-gray-900">{{ ucfirst($produksi->satuan_input) }}</p>
                    </div>
                </div>
            </div>

            <!-- Metrics Section -->
            <div class="mt-6 grid grid-cols-3 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-xs text-gray-600 uppercase">HDP %</p>
                    <p class="text-3xl font-bold text-green-700 mt-2">{{ number_format($produksi->hdp, 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Hen Day Production</p>
                </div>

                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-gray-600 uppercase">HHP %</p>
                    <p class="text-3xl font-bold text-blue-700 mt-2">{{ number_format($produksi->hhp, 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Hen House Production</p>
                </div>

                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <p class="text-xs text-gray-600 uppercase">Mortality %</p>
                    <p class="text-3xl font-bold text-red-700 mt-2">{{ number_format($produksi->mortality, 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Kematian Ayam</p>
                </div>
            </div>

            <!-- Ayam Info -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-xs text-gray-600 uppercase">Ayam Hidup</p>
                    <p class="text-2xl font-bold text-yellow-700 mt-1">{{ $produksi->ayam_hidup }} ekor</p>
                </div>

                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <p class="text-xs text-gray-600 uppercase">Ayam Mati</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $produksi->ayam_mati }} ekor</p>
                </div>
            </div>

            <!-- Keterangan -->
            @if($produksi->catatan)
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm font-medium text-gray-900">Catatan:</p>
                    <p class="text-gray-700 mt-2">{{ $produksi->catatan }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
