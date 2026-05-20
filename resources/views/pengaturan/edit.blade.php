<x-app-layout>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex justify-between items-start mb-2">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Pengaturan</h1>
                <p class="text-xs text-gray-600 mt-0.5">{{ ucwords(str_replace('_', ' ', $pengaturan->kunci)) }}</p>
            </div>
            <a href="{{ route('pengaturan.index') }}" class="text-gray-600 hover:text-gray-800 font-medium text-xs">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="font-semibold text-red-800 text-xs mb-2"><i class="fas fa-circle-exclamation text-red-600 mr-2"></i>Terjadi Kesalahan</p>
                <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Card -->
        <form action="{{ route('pengaturan.update', $pengaturan) }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 space-y-4">
            @csrf @method('PATCH')

            <!-- Kunci (Read Only) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Kunci Pengaturan</label>
                <div class="bg-gray-50 border border-gray-300 rounded-lg p-2 text-gray-700 font-mono text-xs">
                    {{ $pengaturan->kunci }}
                </div>
                <p class="text-xs text-gray-500 mt-1">Identifier unik, tidak dapat diubah</p>
            </div>

            <!-- Keterangan & Tipe Data -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan</label>
                    <p class="text-xs text-gray-600 p-2 bg-gray-50 rounded-lg line-clamp-2">{{ $pengaturan->keterangan }}</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Data</label>
                    <span class="inline-block px-2.5 py-1 rounded text-xs font-medium
                        @switch($pengaturan->tipe_data)
                            @case('string') bg-blue-100 text-blue-800 @break
                            @case('integer') bg-green-100 text-green-800 @break
                            @case('float') bg-purple-100 text-purple-800 @break
                            @case('boolean') bg-orange-100 text-orange-800 @break
                            @default bg-gray-100 text-gray-800
                        @endswitch
                    ">
                        @switch($pengaturan->tipe_data)
                            @case('string') Teks @break
                            @case('integer') Angka @break
                            @case('float') Desimal @break
                            @case('boolean') Bool @break
                            @default {{ $pengaturan->tipe_data }}
                        @endswitch
                    </span>
                </div>
            </div>

            <!-- Nilai Saat Ini -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5">
                <p class="text-xs text-blue-700 font-bold mb-0.5">NILAI SAAT INI:</p>
                <p class="text-sm font-mono font-bold text-blue-900">{{ $pengaturan->nilai }}</p>
            </div>

            <!-- Nilai Input -->
            <div>
                <label for="nilai" class="block text-xs font-bold text-gray-700 mb-1.5">Nilai Baru</label>
                @if($pengaturan->tipe_data === 'boolean')
                    <select name="nilai" id="nilai" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">-- Pilih --</option>
                        <option value="1" {{ old('nilai', $pengaturan->nilai) == 1 ? 'selected' : '' }}>Ya (Aktif)</option>
                        <option value="0" {{ old('nilai', $pengaturan->nilai) == 0 ? 'selected' : '' }}>Tidak (Nonaktif)</option>
                    </select>
                @elseif($pengaturan->tipe_data === 'integer')
                    <input type="number" name="nilai" id="nilai" value="{{ old('nilai', $pengaturan->nilai) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        required min="1" placeholder="Angka positif...">
                @elseif($pengaturan->tipe_data === 'float')
                    <input type="number" name="nilai" id="nilai" value="{{ old('nilai', $pengaturan->nilai) }}" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        required min="0.01" placeholder="Contoh: 16.5">
                @else
                    <input type="text" name="nilai" id="nilai" value="{{ old('nilai', $pengaturan->nilai) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        required placeholder="Masukkan nilai...">
                @endif
                @error('nilai')
                    <span class="text-red-600 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-3 border-t border-gray-200">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition font-bold text-sm">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
                <a href="{{ route('pengaturan.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition font-bold text-center text-sm">
                    <i class="fas fa-times mr-1"></i>Batal
                </a>
            </div>
        </form>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <p class="text-xs font-bold text-blue-900 mb-2"><i class="fas fa-info-circle mr-1"></i>Panduan</p>
            <ul class="text-xs text-blue-800 space-y-1 list-disc list-inside">
                <li>Nilai harus sesuai tipe data</li>
                <li>Perubahan berlaku instan ke seluruh sistem</li>
            </ul>
        </div>
    </div>
</x-app-layout>
