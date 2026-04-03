<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">
                {{ isset($kandang) ? 'Edit Kandang' : 'Tambah Kandang' }}
            </h1>

            <form method="POST" action="{{ isset($kandang) ? route('kandang.update', $kandang) : route('kandang.store') }}">
                @csrf
                @if(isset($kandang))
                    @method('PATCH')
                @endif

                <!-- Nama Kandang -->
                <div class="mb-6">
                    <label for="nama_kandang" class="block text-sm font-medium text-gray-700 mb-2">Nama Kandang</label>
                    <input type="text" id="nama_kandang" name="nama_kandang" 
                           value="{{ old('nama_kandang', $kandang->nama_kandang ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('nama_kandang')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Ayam -->
                <div class="mb-6">
                    <label for="jumlah_ayam" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Ayam</label>
                    <input type="number" id="jumlah_ayam" name="jumlah_ayam" 
                           value="{{ old('jumlah_ayam', $kandang->jumlah_ayam ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           min="0" required>
                    @error('jumlah_ayam')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="">Pilih Status</option>
                        <option value="aktif" {{ old('status', $kandang->status ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $kandang->status ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="mb-6">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('keterangan', $kandang->keterangan ?? '') }}</textarea>
                </div>

                <!-- PIC Kandang -->
                <div class="mb-6">
                    <label for="pic_id" class="block text-sm font-medium text-gray-700 mb-2">PIC Kandang</label>
                    <select id="pic_id" name="pic_id" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih PIC Kandang</option>
                        @foreach($pekerja as $p)
                            <option value="{{ $p->id }}" {{ old('pic_id', $kandang->pic_id ?? '') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('pic_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        {{ isset($kandang) ? 'Perbarui' : 'Simpan' }}
                    </button>
                    <a href="{{ route('kandang.index') }}" class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
