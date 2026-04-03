@php
    $hargaId = $hargaTelur->id ?? 0;
    $jenisHarga = $hargaTelur->jenis_harga ?? 'Tidak Ada';
    $tanggalBerlaku = $hargaTelur->tanggal_berlaku?->format('d-m-Y') ?? 'Tidak Ada';
    $hargaPerKg = $hargaTelur->harga_per_kg ?? '';
    $hargaPerButir = $hargaTelur->harga_per_butir ?? '';
    $keterangan = $hargaTelur->keterangan ?? '';
@endphp

<!-- DEBUG INFO - REMOVE LATER -->
<!-- ID: {{ $hargaId }}, Jenis: {{ $jenisHarga }}, TglBerlaku: {{ $tanggalBerlaku }}, HargaPerKg: {{ $hargaPerKg }} -->

<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit Harga Telur</h1>
            <a href="{{ route('harga.index') }}" class="text-gray-600 hover:text-gray-800">← Kembali</a>
        </div>

        <form action="/harga/{{ $hargaId }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            @csrf @method('PATCH')

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="jenis_harga" class="block text-sm font-medium text-gray-700 mb-2">Jenis Harga</label>
                    <div class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-medium">
                        {{ $jenisHarga }}
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Jenis harga sudah paten, tidak bisa diubah</p>
                </div>

                <div>
                    <label for="tanggal_berlaku" class="block text-sm font-medium text-gray-700 mb-2">Berlaku Sejak</label>
                    <div class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-medium">
                        {{ $tanggalBerlaku }}
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Tanggal berlaku sudah paten, buat harga baru untuk mengubah</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="harga_per_kg" class="block text-sm font-medium text-gray-700 mb-2">Harga per KG</label>
                    <input type="number" name="harga_per_kg" id="harga_per_kg" value="{{ old('harga_per_kg', $hargaPerKg) }}" 
                        step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    @error('harga_per_kg')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="harga_per_butir" class="block text-sm font-medium text-gray-700 mb-2">Harga per Butir</label>
                    <input type="number" name="harga_per_butir" id="harga_per_butir" value="{{ old('harga_per_butir', $hargaPerButir) }}" 
                        step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @error('harga_per_butir')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('keterangan', $keterangan) }}</textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                    Update Harga
                </button>
                <a href="{{ route('harga.index') }}" class="bg-gray-400 text-white px-8 py-3 rounded-lg hover:bg-gray-500 transition font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
