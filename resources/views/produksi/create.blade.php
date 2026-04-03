<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Input Produksi Telur</h1>

            <form method="POST" action="{{ route('produksi.store') }}">
                @csrf

                <!-- Kandang Info (Display Only) -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-gray-600">Kandang:</p>
                    <p class="text-lg font-bold text-gray-900">{{ auth()->user()->kandang->nama_kandang }}</p>
                    @php
                        $totalAyamMati = \App\Models\ProduksiTelur::where('kandang_id', auth()->user()->kandang->id)->sum('ayam_mati');
                        $ayamHidupSaatIni = auth()->user()->kandang->jumlah_ayam - $totalAyamMati;
                    @endphp
                    <p class="text-xs text-gray-500 mt-2">
                        Ayam: <span class="font-bold">{{ $ayamHidupSaatIni }} ekor</span>
                        <span class="text-gray-400">({{ auth()->user()->kandang->jumlah_ayam }} base - {{ $totalAyamMati }} mati)</span>
                    </p>
                </div>

                <!-- Tanggal Produksi -->
                <div class="mb-6">
                    <label for="tanggal_produksi" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Produksi</label>
                    <input type="date" id="tanggal_produksi" name="tanggal_produksi" 
                           value="{{ old('tanggal_produksi', today()->toDateString()) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('tanggal_produksi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Satuan Input -->
                <div class="mb-6">
                    <label for="satuan_input" class="block text-sm font-medium text-gray-700 mb-2">Pilih Satuan Input</label>
                    <select id="satuan_input" name="satuan_input" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="updateSatuanDisplay()" required>
                        <option value="">Pilih Satuan</option>
                        <option value="butir" {{ old('satuan_input') === 'butir' ? 'selected' : '' }}>Butir</option>
                        <option value="kg" {{ old('satuan_input') === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                    </select>
                    @error('satuan_input')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Input -->
                <div class="mb-6">
                    <label for="jumlah_input" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah <span id="satuan-label">(satuan)</span>
                    </label>
                    <input type="number" id="jumlah_input" name="jumlah_input" 
                           value="{{ old('jumlah_input') }}"
                           step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           onchange="hitungKonversi()" placeholder="Masukkan jumlah"
                           required>
                    @error('jumlah_input')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konversi Info -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2">Hasil Konversi:</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Total Butir:</p>
                            <p class="text-lg font-bold text-gray-900" id="hasil-butir">0</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Total Kg:</p>
                            <p class="text-lg font-bold text-gray-900" id="hasil-kg">0</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">* Konversi: 1 kg = 16 butir (dari pengaturan sistem)</p>
                </div>

                <!-- Keterangan -->
                <div class="mb-6">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan (Opsional)</label>
                    <textarea id="keterangan" name="keterangan" rows="3"
                              placeholder="Contoh: Kondisi ayam bagus, cuaca cerah"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('keterangan') }}</textarea>
                </div>

                <!-- Ayam Mati -->
                <div class="mb-6">
                    <label for="ayam_mati" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Ayam Mati (Opsional)</label>
                    <input type="number" id="ayam_mati" name="ayam_mati" 
                           value="{{ old('ayam_mati', 0) }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="0"
                           onchange="hitungAyamHidup(); hitungMetrik();">
                    @error('ayam_mati')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ayam Hidup (Auto-calculated) -->
                <div class="mb-6">
                    <label for="ayam_hidup_display" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Ayam Hidup (Otomatis)</label>
                    <div class="flex gap-2">
                        <input type="number" id="ayam_hidup_display" name="ayam_hidup_display"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-100"
                               readonly>
                        <input type="hidden" id="ayam_hidup" name="ayam_hidup">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">* Otomatis dihitung dari: Jumlah Ayam Saat Ini (<span id="total-ayam">{{ $ayamHidupSaatIni }}</span> ekor) - Ayam Mati Hari Ini</p>
                </div>

                <!-- Metrics Display -->
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-gray-600 mb-4 font-semibold">Metrik Produksi:</p>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">HDP %</p>
                            <p class="text-2xl font-bold text-green-600" id="hasil-hdp">0.00</p>
                            <p class="text-xs text-gray-500">Hen Day Production</p>
                        </div>
                        <div>
                            <p class="text-gray-600">HHP %</p>
                            <p class="text-2xl font-bold text-blue-600" id="hasil-hhp">0.00</p>
                            <p class="text-xs text-gray-500">Hen House Production</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Mortality %</p>
                            <p class="text-2xl font-bold text-red-600" id="hasil-mortality">0.00</p>
                            <p class="text-xs text-gray-500">Kematian Ayam</p>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="mb-6">
                    <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea id="catatan" name="catatan" rows="2"
                              placeholder="Contoh: Pakan habis jam 10, ayam terlihat kurang aktif"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        Simpan Produksi
                    </button>
                    <a href="{{ route('produksi.index') }}" class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const KONVERSI_RATIO = 16; // 1 kg = 16 butir
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

        function hitungAyamHidup() {
            const ayamMati = parseInt(document.getElementById('ayam_mati').value) || 0;
            // Use current ayam count, not base count
            const ayamHidup = JUMLAH_AYAM_SAAT_INI - ayamMati;
            
            // Set display value
            document.getElementById('ayam_hidup_display').value = ayamHidup;
            
            // Set hidden input value
            document.getElementById('ayam_hidup').value = ayamHidup;
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
            hitungAyamHidup();
            hitungMetrik();
        });

        function hitungMetrik() {
            const jumlahButir = parseInt(document.getElementById('hasil-butir').textContent.replace(/\D/g, '')) || 0;
            const ayamHidup = parseInt(document.getElementById('ayam_hidup').value) || 0;
            const ayamMati = parseInt(document.getElementById('ayam_mati').value) || 0;
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
    </script>
</x-app-layout>
