<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Edit: {{ ucwords(str_replace('_', ' ', $pengaturan->kunci)) }}</h1>
            <a href="{{ route('pengaturan.index') }}" class="text-gray-600 hover:text-gray-800">← Kembali</a>
        </div>

        <form action="{{ route('pengaturan.update', $pengaturan) }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kunci Pengaturan</label>
                <input type="text" value="{{ $pengaturan->kunci }}" disabled class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500">
            </div>

            <div>
                <label for="nilai" class="block text-sm font-medium text-gray-700 mb-2">Nilai</label>
                @if($pengaturan->tipe_data === 'boolean')
                    <select name="nilai" id="nilai" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="1" {{ $pengaturan->nilai == 1 ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ $pengaturan->nilai == 0 ? 'selected' : '' }}>Tidak</option>
                    </select>
                @elseif($pengaturan->tipe_data === 'integer')
                    <input type="number" name="nilai" id="nilai" value="{{ $pengaturan->nilai }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                @elseif($pengaturan->tipe_data === 'float')
                    <input type="number" name="nilai" id="nilai" value="{{ $pengaturan->nilai }}" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                @else
                    <input type="text" name="nilai" id="nilai" value="{{ $pengaturan->nilai }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                @endif
                @error('nilai')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Data</label>
                <input type="text" value="{{ $pengaturan->tipe_data }}" disabled class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <p class="px-4 py-2 text-gray-600">{{ $pengaturan->keterangan }}</p>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                    Simpan Perubahan
                </button>
                <a href="{{ route('pengaturan.index') }}" class="bg-gray-400 text-white px-8 py-3 rounded-lg hover:bg-gray-500 transition font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
