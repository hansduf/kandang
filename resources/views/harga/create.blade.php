<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Input Harga Telur</h1>
            <a href="{{ route('harga.index') }}" class="text-gray-600 hover:text-gray-800">← Kembali</a>
        </div>

        <form action="{{ route('harga.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="jenis_harga" class="block text-sm font-medium text-gray-700 mb-2">Jenis Harga</label>
                    <select name="jenis_harga" id="jenis_harga" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="kandang" {{ old('jenis_harga') === 'kandang' ? 'selected' : '' }}>Kandang</option>
                        <option value="grosir" {{ old('jenis_harga') === 'grosir' ? 'selected' : '' }}>Grosir</option>
                        <option value="konsumen" {{ old('jenis_harga') === 'konsumen' ? 'selected' : '' }}>Konsumen</option>
                    </select>
                    @error('jenis_harga')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_berlaku" class="block text-sm font-medium text-gray-700 mb-2">Berlaku Sejak</label>
                    <input type="date" name="tanggal_berlaku" id="tanggal_berlaku" value="{{ old('tanggal_berlaku', now()->format('Y-m-d')) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    @error('tanggal_berlaku')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="harga_per_kg" class="block text-sm font-medium text-gray-700 mb-2">Harga per KG (Wajib)</label>
                    <input type="number" name="harga_per_kg" id="harga_per_kg" value="{{ old('harga_per_kg') }}" 
                        placeholder="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    @error('harga_per_kg')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="harga_per_butir" class="block text-sm font-medium text-gray-700 mb-2">Harga per Butir (Opsional)</label>
                    <input type="number" name="harga_per_butir" id="harga_per_butir" value="{{ old('harga_per_butir') }}" 
                        placeholder="Otomatis kalau kosong" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @error('harga_per_butir')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                    Simpan Harga
                </button>
                <a href="{{ route('harga.index') }}" class="bg-gray-400 text-white px-8 py-3 rounded-lg hover:bg-gray-500 transition font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
