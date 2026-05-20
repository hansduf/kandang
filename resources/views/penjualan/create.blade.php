<x-app-layout>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Input Penjualan</h1>
            <a href="{{ route('penjualan.index') }}" class="text-gray-600 hover:text-gray-800">← Kembali</a>
        </div>

        <form action="{{ route('penjualan.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Header Info -->
            <div class="grid grid-cols-3 gap-4 bg-white rounded-xl shadow-sm p-6">
                <div>
                    <label for="tanggal_jual" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Penjualan</label>
                    <input type="date" id="tanggal_jual" name="tanggal_jual" value="{{ old('tanggal_jual', now()->toDateString()) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    @error('tanggal_jual')
                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="jam_jual" class="block text-sm font-medium text-gray-700 mb-2">Jam Penjualan (Opsional)</label>
                    <input type="time" id="jam_jual" name="jam_jual" value="{{ old('jam_jual', now()->format('H:i')) }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Untuk track harga yang tepat jika ada perubahan dalam sehari</p>
                </div>
                <div>
                    <label for="nama_pembeli" class="block text-sm font-medium text-gray-700 mb-2">Nama Pembeli (Opsional)</label>
                    <input type="text" id="nama_pembeli" name="nama_pembeli" value="{{ old('nama_pembeli') }}" 
                        placeholder="Jika kosong: Umum"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Stok Display -->
            <div class="grid grid-cols-3 gap-4 bg-white rounded-xl shadow-sm p-6">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-gray-600 font-medium">Stok Tersedia (Butir)</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1" id="stok-butir">0</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-sm text-gray-600 font-medium">Stok Tersedia (KG)</p>
                    <p class="text-2xl font-bold text-green-700 mt-1" id="stok-kg">0.00</p>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <p class="text-sm text-gray-600 font-medium">Status</p>
                    <p class="text-lg font-bold text-purple-700 mt-1" id="stok-status"><i class="fas fa-check-circle text-green-600"></i> Cukup</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Detail Penjualan</h2>
                    <button type="button" onclick="tambahItem()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        + Tambah Item
                    </button>
                </div>

                @error('items')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 text-red-600 text-sm">
                        {{ $message }}
                    </div>
                @enderror

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="itemsTable">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-900">Jenis Harga</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-900">Satuan</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-900">Jumlah Jual</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-900">
                                    <span class="butir-header">Perkiraan KG</span>
                                    <span class="kg-header" style="display:none;">Perkiraan Butir</span>
                                </th>
                                <th class="px-4 py-3 text-right font-bold text-gray-900">Harga/Unit</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-900">Subtotal</th>
                                <th class="px-4 py-3 text-center font-bold text-gray-900">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Items akan ditambah di sini -->
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex justify-end">
                        <div class="w-64">
                            <div class="flex justify-between mb-3 pb-3 border-b-2 border-gray-200">
                                <span class="font-medium text-gray-700">Total Harga:</span>
                                <span class="text-2xl font-bold text-green-600">Rp <span id="totalHarga">0</span></span>
                            </div>
                            <input type="hidden" name="total_harga" id="totalHargaInput" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keterangan -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex gap-4">
                <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-medium">
                    Simpan Penjualan
                </button>
                <a href="{{ route('penjualan.index') }}" class="bg-gray-400 text-white px-8 py-3 rounded-lg hover:bg-gray-500 transition font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const initialHargaList = {!! json_encode($hargaTelur->pluck('harga_per_kg', 'id')->toArray()) !!};
        let hargaList = Object.assign({}, initialHargaList);
        
        let allHarga = {!! json_encode($hargaTelur->map(function($h) { return ['id' => $h->id, 'jenis_harga' => $h->jenis_harga, 'harga_per_kg' => $h->harga_per_kg, 'harga_per_butir' => $h->harga_per_butir]; })->toArray()) !!};
        let itemCount = 0;

        // Event listener untuk perubahan tanggal
        document.getElementById('tanggal_jual').addEventListener('change', function() {
            const tanggal = this.value;
            if (tanggal) {
                loadHargaByDate(tanggal);
                loadStok(tanggal);  // Reload stok untuk bulan dari tanggal yang dipilih
            }
        });

        // Fetch harga berdasarkan tanggal
        function loadHargaByDate(tanggal) {
            fetch(`/penjualan-harga-by-date?tanggal=${tanggal}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        // Clear semua items lama
                        document.getElementById('itemsBody').innerHTML = '';
                        
                        // Reset itemCount
                        itemCount = 0;
                        
                        // Update global hargaList dengan data terbaru
                        hargaList = {};
                        data.data.forEach(h => {
                            hargaList[h.id] = h.harga_per_kg;
                        });

                        // Update allHarga dengan data terbaru  
                        allHarga = data.data;
                        
                        // Tambah 1 item baru
                        tambahItem();
                        
                        // Reset total
                        hitungTotal();
                    } else {
                        alert('Harga tidak tersedia untuk tanggal ' + tanggal);
                    }
                })
                .catch(error => console.error('Error fetching harga:', error));
        }

        function tambahItem() {
            const tbody = document.getElementById('itemsBody');
            itemCount++;
            
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 hover:bg-gray-50 item-row';
            row.id = 'item-' + itemCount;
            
            let hargaOptions = '<option value="">-- Pilih Jenis Harga --</option>';
            allHarga.forEach(h => {
                hargaOptions += `<option value="${h.id}">Rp ${formatRupiah(h.harga_per_kg)}/kg - ${h.jenis_harga}</option>`;
            });
            
            row.innerHTML = `
                <td class="px-4 py-3">
                    <select name="items[${itemCount}][harga_telur_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm harga-select" onchange="updateSubtotal(${itemCount})" required>
                        ${hargaOptions}
                    </select>
                </td>
                <td class="px-4 py-3">
                    <select name="items[${itemCount}][satuan_jual]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm satuan-select" onchange="toggleBeratInput(${itemCount}); updateSubtotal(${itemCount})">
                        <option value="butir">Butir</option>
                        <option value="kg">KG</option>
                    </select>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][jumlah_jual]" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm jumlah-input" 
                        onchange="updateSubtotal(${itemCount})" min="0" placeholder="Jumlah (Butir)" required>
                </td>
                <td class="px-4 py-3">
                    <input type="number" name="items[${itemCount}][jumlah_butir]" step="any" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm jumlah-konversi-input" 
                        onchange="updateSubtotal(${itemCount})" min="0" placeholder="Perkiraan" required>
                    <small class="text-gray-500 text-xs butir-helper" style="display:none;">KG dari butir</small>
                    <small class="text-gray-500 text-xs kg-helper">Butir dari KG</small>
                </td>
                <td class="px-4 py-3 text-right">
                    <span class="font-medium harga-display">Rp 0</span>
                    <input type="hidden" name="items[${itemCount}][harga_satuan]" class="harga-satuan-input" value="0">
                </td>
                <td class="px-4 py-3 text-right">
                    <span class="font-bold text-green-600 subtotal-display">Rp 0</span>
                    <input type="hidden" name="items[${itemCount}][subtotal]" class="subtotal-input" value="0">
                </td>
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="hapusItem(${itemCount})" class="text-red-600 hover:text-red-800 font-medium">
                        Hapus
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
        }

        function toggleBeratInput(itemNo) {
            const row = document.getElementById('item-' + itemNo);
            const satuanSelect = row.querySelector('.satuan-select');
            const jumlahInput = row.querySelector('.jumlah-input');
            const konversiInput = row.querySelector('.jumlah-konversi-input');
            
            // Update header label
            if (satuanSelect.value === 'kg') {
                // Satuan KG: tampil "Perkiraan Butir"
                document.querySelector('.butir-header').style.display = 'none';
                document.querySelector('.kg-header').style.display = 'inline';
                
                // Update input field
                konversiInput.name = `items[${itemNo}][jumlah_butir]`;
                konversiInput.placeholder = 'Perkiraan butir';
                konversiInput.step = 'any';
                konversiInput.disabled = false;
                
                // Update helper
                row.querySelector('.butir-helper').style.display = 'none';
                row.querySelector('.kg-helper').style.display = 'inline';
                
                jumlahInput.placeholder = 'Berat KG (Misal: 1,024)';
                jumlahInput.step = '0.001';
            } else {
                // Satuan Butir: tampil "Perkiraan KG"
                document.querySelector('.butir-header').style.display = 'inline';
                document.querySelector('.kg-header').style.display = 'none';
                
                // Update input field
                konversiInput.name = `items[${itemNo}][jumlah_kg]`;
                konversiInput.placeholder = 'Perkiraan KG';
                konversiInput.step = 'any';
                konversiInput.disabled = false;
                
                // Update helper
                row.querySelector('.butir-helper').style.display = 'inline';
                row.querySelector('.kg-helper').style.display = 'none';
                
                jumlahInput.placeholder = 'Jumlah (Butir)';
                jumlahInput.step = '1';
            }
        }

        function updateSubtotal(itemNo) {
            const row = document.getElementById('item-' + itemNo);
            const hargaSelect = row.querySelector('.harga-select');
            const satuanSelect = row.querySelector('.satuan-select');
            const jmlInput = row.querySelector('.jumlah-input');
            const konversiInput = row.querySelector('.jumlah-konversi-input');
            
            if (!hargaSelect.value || !jmlInput.value) return;
            
            const hargaPerKg = hargaList[hargaSelect.value] || 0;
            const satuan = satuanSelect.value;
            const jumlah = parseFloat(jmlInput.value) || 0;
            const konversi = {{ $konversi ?? 16 }}; // Dynamic dari pengaturan
            
            let subtotal = 0;
            let hargaSatuan = 0;
            
            if (satuan === 'butir') {
                // Satuan BUTIR: estimate KG
                const perkiraaKg = (jumlah / konversi).toFixed(3);
                konversiInput.value = perkiraaKg;
                hargaSatuan = hargaPerKg;
                subtotal = (jumlah / konversi) * hargaPerKg;  // Convert to KG first
            } else {
                // Satuan KG: calculate & fill butir otomatis
                let perkiraaButir = Math.round(jumlah * konversi);
                konversiInput.value = perkiraaButir;
                hargaSatuan = hargaPerKg;
                subtotal = jumlah * hargaPerKg;
            }
            
            row.querySelector('.harga-satuan-input').value = hargaSatuan;
            row.querySelector('.harga-display').textContent = 'Rp ' + formatRupiah(hargaSatuan);
            row.querySelector('.subtotal-display').textContent = 'Rp ' + formatRupiah(subtotal);
            row.querySelector('.subtotal-input').value = subtotal;
            
            hitungTotal();
        }

        function hapusItem(itemNo) {
            const row = document.getElementById('item-' + itemNo);
            row.remove();
            hitungTotal();
        }

        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal-input').forEach(el => {
                total += parseFloat(el.value) || 0;
            });
            
            document.getElementById('totalHarga').textContent = formatRupiah(total);
            document.getElementById('totalHargaInput').value = total;
        }

        function formatRupiah(num) {
            return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Fetch dan tampilkan stok dengan carryover logic
        function loadStok(tanggal = null) {
            // Use provided date or get from form
            if (!tanggal) {
                tanggal = document.getElementById('tanggal_jual')?.value || new Date().toISOString().split('T')[0];
            }
            
            // Call API with date parameter
            fetch(`/api/stok?tanggal=${tanggal}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stokButir = data.stok_butir || 0;
                        const stokKg = parseFloat(data.stok_kg || 0);
                        document.getElementById('stok-butir').textContent = stokButir.toLocaleString('id-ID');
                        document.getElementById('stok-kg').textContent = stokKg.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 3 });
                        
                        // Update status
                        const statusEl = document.getElementById('stok-status');
                        if (stokButir > 0) {
                            statusEl.innerHTML = '<i class="fas fa-check-circle text-green-600"></i> Cukup';
                            statusEl.className = 'text-lg font-bold text-green-700 mt-1';
                        } else {
                            statusEl.innerHTML = '<i class="fas fa-circle-xmark text-red-600"></i> Kosong';
                            statusEl.className = 'text-lg font-bold text-red-700 mt-1';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading stok:', error);
                    document.getElementById('stok-butir').textContent = '0';
                    document.getElementById('stok-kg').textContent = '0.00';
                });
        }

        // Minimal 1 item
        window.addEventListener('load', function() {
            // Set tanggal ke hari ini jika kosong
            const today = new Date().toISOString().split('T')[0];
            const tanggalInput = document.getElementById('tanggal_jual');
            if (!tanggalInput.value || tanggalInput.value === '') {
                tanggalInput.value = today;
            }

            // Load stok awal
            loadStok();
            
            // Load harga for today, which will automatically add first item
            loadHargaByDate(today);
        });
    </script>
</x-app-layout>
