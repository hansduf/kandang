<x-app-layout>
    <div class="w-full px-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Input Produksi Telur</h1>

            <form method="POST" action="{{ route('produksi.store') }}">
                @csrf

                <!-- Kandang Info (Display Only) - Compact -->
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Kandang: <span class="font-bold text-gray-900">{{ auth()->user()->kandang->nama_kandang }}</span></p>
                        </div>
                        @php
                            $totalAyamMati = \App\Models\ProduksiTelur::where('kandang_id', auth()->user()->kandang->id)->sum('ayam_mati');
                            $ayamHidupSaatIni = auth()->user()->kandang->jumlah_ayam - $totalAyamMati;
                        @endphp
                        <p class="text-xs text-gray-600">Ayam: <span class="font-bold">{{ $ayamHidupSaatIni }}/{{ auth()->user()->kandang->jumlah_ayam }}</span> ekor</p>
                    </div>
                </div>

                <!-- Primary Input Fields - 2 Column Grid -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Tanggal Produksi -->
                    <div>
                        <label for="tanggal_produksi" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" id="tanggal_produksi" name="tanggal_produksi" 
                               value="{{ old('tanggal_produksi', today()->toDateString()) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                        @error('tanggal_produksi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Satuan Input -->
                    <div>
                        <label for="satuan_input" class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <select id="satuan_input" name="satuan_input" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="updateSatuanDisplay()" required>
                            <option value="">Pilih</option>
                            <option value="butir" {{ old('satuan_input') === 'butir' ? 'selected' : '' }}>Butir</option>
                            <option value="kg" {{ old('satuan_input') === 'kg' ? 'selected' : '' }}>Kg</option>
                        </select>
                        @error('satuan_input')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Jumlah and Ayam Mati - 2 Column Grid -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Jumlah Input -->
                    <div>
                        <label for="jumlah_input" class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah <span id="satuan-label" class="text-gray-500">(satuan)</span>
                        </label>
                        <input type="number" id="jumlah_input" name="jumlah_input" 
                               value="{{ old('jumlah_input') }}"
                               step="0.01"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               onchange="hitungKonversi()" placeholder="Masukkan jumlah"
                               required>
                        @error('jumlah_input')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ayam Mati -->
                    <div>
                        <label for="ayam_mati" class="block text-sm font-medium text-gray-700 mb-1">Ayam Mati</label>
                        <input type="number" id="ayam_mati" name="ayam_mati" 
                               value="{{ old('ayam_mati', 0) }}"
                               min="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0"
                               onchange="hitungMetrik();">
                        @error('ayam_mati')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Conversion & Metrics Row - 2 Column -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Konversi Info - Compact -->
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs text-gray-600 mb-2 font-semibold">Hasil Konversi:</p>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Butir:</span>
                                <span class="font-bold text-gray-900" id="hasil-butir">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Kg:</span>
                                <span class="font-bold text-gray-900" id="hasil-kg">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ayam Hidup:</span>
                                <span class="font-bold text-gray-900" id="ayam_hidup_display_compact">{{ $ayamHidupSaatIni }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">1 kg = 16 butir</p>
                    </div>

                    <!-- Metrics Display - Compact -->
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-xs text-gray-600 mb-2 font-semibold">Metrik Produksi:</p>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">HDP %</span>
                                <span class="text-lg font-bold text-green-600" id="hasil-hdp">0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">HHP %</span>
                                <span class="text-lg font-bold text-blue-600" id="hasil-hhp">0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mortality %</span>
                                <span class="text-lg font-bold text-red-600" id="hasil-mortality">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan & Catatan - 2 Column Grid -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="2"
                                  placeholder="Kondisi ayam, cuaca"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('keterangan') }}</textarea>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="2"
                                  placeholder="Pakan, aktivitas ayam"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-medium">
                        Simpan Produksi
                    </button>
                    <a href="{{ route('produksi.index') }}" class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-medium">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const KONVERSI_RATIO = {{ $konversi ?? 16 }}; // Dynamic dari pengaturan
        const JUMLAH_AYAM_AWAL = {{ auth()->user()->kandang->jumlah_ayam }}; // Base count
        const JUMLAH_AYAM_SAAT_INI = {{ $ayamHidupSaatIni }}; // Current count (accounting for deaths)

        function updateSatuanDisplay() {
            const satuan = document.getElementById('satuan_input').value;
            const label = document.getElementById('satuan-label');
            label.textContent = satuan === 'butir' ? '(butir)' : '(kg)';
            hitungKonversi();
        }

        function hitungKonversi() {
            const satuan = document.getElementById('satuan_input').value;
            const jumlah = parseFloat(document.getElementById('jumlah_input').value) || 0;
            
            let butir, kg;

            if (satuan === 'butir') {
                butir = Math.floor(jumlah);
                kg = (butir / KONVERSI_RATIO).toFixed(2);
            } else if (satuan === 'kg') {
                kg = jumlah;
                butir = Math.floor(kg * KONVERSI_RATIO);
            } else {
                butir = 0;
                kg = 0;
            }

            document.getElementById('hasil-butir').textContent = butir.toLocaleString('id-ID');
            document.getElementById('hasil-kg').textContent = kg;
            hitungMetrik();
        }

        function hitungMetrik() {
            const jumlahButir = parseInt(document.getElementById('hasil-butir').textContent.replace(/\D/g, '')) || 0;
            const ayamMati = parseInt(document.getElementById('ayam_mati').value) || 0;
            const ayamHidup = JUMLAH_AYAM_SAAT_INI - ayamMati;
            // Use base count for metric calculations
            const jumlahAyamAwal = JUMLAH_AYAM_AWAL;

            // HDP = (Jumlah telur / Jumlah ayam hidup) × 100
            const hdp = ayamHidup > 0 ? (jumlahButir / ayamHidup) * 100 : 0;

            // HHP = (Jumlah telur / Jumlah ayam awal) × 100
            const hhp = jumlahAyamAwal > 0 ? (jumlahButir / jumlahAyamAwal) * 100 : 0;

            // Mortality = (Jumlah ayam mati / Total ayam awal) × 100
            const mortality = jumlahAyamAwal > 0 ? (ayamMati / jumlahAyamAwal) * 100 : 0;

            document.getElementById('hasil-hdp').textContent = hdp.toFixed(2);
            document.getElementById('hasil-hhp').textContent = hhp.toFixed(2);
            document.getElementById('hasil-mortality').textContent = mortality.toFixed(2);
        }

        // Initial load
        window.addEventListener('load', function() {
            // Set tanggal ke hari ini
            const today = new Date().toISOString().split('T')[0];
            const tanggalInput = document.getElementById('tanggal_produksi');
            if (!tanggalInput.value || tanggalInput.value === '') {
                tanggalInput.value = today;
            }
            updateSatuanDisplay();
            hitungMetrik();
        });
    </script>
</x-app-layout>
