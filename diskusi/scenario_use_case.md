# SKENARIO USE CASE - SISTEM MANAJEMEN & MONITORING TELUR AYAM PETELUR

---

## 1. SKENARIO: LOGIN

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Login |
| **Actor** | Pekerja Kandang / Pemilik Peternakan |
| **Tujuan** | Masuk ke sistem persewaan buku |
| **Kondisi Awal** | Sistem menampilkan halaman login |
| **Kondisi Akhir** | Sistem menampilkan dashboard sesuai role pengguna |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pengguna membuka aplikasi | Sistem menampilkan halaman login |
| 2 | Pengguna memasukkan username | Sistem menerima input username |
| 3 | Pengguna memasukkan password | Sistem menerima input password |
| 4 | Pengguna click tombol "Login" | Sistem memvalidasi kredensial |
| 5 | | Sistem memverifikasi role pengguna (pemilik/pekerja) |
| 6 | | Sistem menampilkan dashboard sesuai role |

### Skenario Alternatif (Error Cases)

**A1. Username atau Password Salah:**
- Sistem menampilkan pesan error "Username/Password salah"
- Pengguna dapat retry login

**A2. Akun Terkunci:**
- Sistem mendeteksi multiple failed login attempts
- Sistem display pesan "Akun terkunci, hubungi administrator"

---

## 2. SKENARIO: INPUT PRODUKSI TELUR HARIAN

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Input Produksi Telur Harian |
| **Actor** | Pekerja Kandang |
| **Tujuan** | Mencatat hasil produksi telur harian kandang mereka |
| **Kondisi Awal** | Pekerja sudah login, berada di halaman input produksi |
| **Kondisi Akhir** | Data produksi tersimpan, stok bertambah otomatis |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pekerja klik menu "Input Produksi" | Sistem menampilkan form input produksi |
| 2 | Sistem pre-fill kandang_id pekerja | |
| 3 | Pekerja memilih tanggal produksi | Sistem menerima tanggal, validasi tidak melebihi hari ini |
| 4 | Pekerja memilih satuan (butir/kg) | Sistem menerima pilihan satuan |
| 5 | Pekerja input jumlah | Sistem menerima input angka |
| 6 | Pekerja input keterangan (optional) | Sistem menerima keterangan |
| 7 | Pekerja klik tombol "Simpan" | Sistem validasi input (tidak boleh kosong, positif) |
| 8 | | Sistem konversi satuan otomatis (butir ↔ kg) |
| 9 | | Sistem simpan ke tabel produksi_telur |
| 10 | | Sistem update stok_telur (tambah) |
| 11 | | Sistem menampilkan pesan sukses |
| 12 | | Sistem redirect ke halaman riwayat produksi |

### Skenario Alternatif (Error Cases)

**A1. Input Jumlah Negatif atau Nol:**
- Sistem menampilkan error "Jumlah harus lebih dari 0"
- Pekerja harus perbaiki dan resubmit

**A2. Tanggal Produksi Lebih Besar dari Hari Ini:**
- Sistem menampilkan error "Tanggal tidak boleh melebihi hari ini"

**A3. Pekerja Mencoba Input Kandang Lain:**
- Sistem hanya memperlihatkan kandang mereka sendiri (auto-fill)
- Tidak bisa ubah kandang_id

---

## 3. SKENARIO: LIHAT DASHBOARD

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Dashboard |
| **Actor** | Pekerja Kandang / Pemilik Peternakan |
| **Tujuan** | Melihat ringkasan data bisnis terkini |
| **Kondisi Awal** | Pengguna sudah login |
| **Kondisi Akhir** | Sistem menampilkan dashboard dengan kartu ringkasan & grafik |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pengguna klik menu "Dashboard" | Sistem query data dari database |
| 2 | | Sistem hitung stok_telur terkini |
| 3 | | Sistem hitung produksi hari ini (dari tabel produksi_telur) |
| 4 | | Sistem hitung penjualan bulan ini (dari tabel penjualan) |
| 5 | | Sistem hitung jumlah kandang aktif |
| 6 | | Sistem query produksi 7 hari terakhir untuk grafik |
| 7 | | Sistem menampilkan 4 kartu: Stok, Produksi hari ini, Penjualan bulan ini, Kandang aktif |
| 8 | | Sistem menampilkan grafik bar produksi 7 hari terakhir |

### Skenario Alternatif (Conditional)

**A1. Data Belum Ada:**
- Sistem menampilkan kartu dengan nilai 0
- Grafik kosong atau placeholder

**A2. Pekerja Kandang:**
- Dashboard hanya menampilkan ringkasan umum (tidak ada laporan detail)

**A2. Pemilik Peternakan:**
- Dashboard menampilkan ringkasan lengkap dengan semua metrik bisnis

---

## 4. SKENARIO: LIHAT RIWAYAT PRODUKSI

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Riwayat Produksi |
| **Actor** | Pekerja Kandang / Pemilik Peternakan |
| **Tujuan** | Melihat histori pencatatan produksi telur |
| **Kondisi Awal** | Pengguna sudah login |
| **Kondisi Akhir** | Sistem menampilkan tabel riwayat produksi |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pengguna klik menu "Lihat Riwayat Produksi" | Sistem menampilkan halaman riwayat produksi |
| 2 | | Sistem query tabel produksi_telur |
| 3 | | Sistem tampilkan tabel: No, Tanggal, Kandang, Satuan, Jumlah (Butir), Jumlah (Kg) |
| 4 | | Sistem urutkan berdasarkan tanggal DESC (terbaru duluan) |
| 5 | Pengguna bisa scroll atau klik pagination | Sistem menampilkan data per halaman (10/20 per hal) |

### Skenario Alternatif (Filter & Action)

**A1. Pekerja Kandang:**
- Hanya melihat riwayat produksi kandang mereka sendiri
- Tidak bisa lihat kandang lain

**A2. Pemilik Peternakan:**
- Bisa filter berdasarkan kandang
- Bisa filter berdasarkan range tanggal
- Bisa lihat riwayat semua kandang

**A3. Edit/Hapus Data:**
- Jika tabel punya kolom action (edit/delete)
- Pekerja tidak bisa edit data lain
- Pemilik bisa edit semua data

---

## 5. SKENARIO: LIHAT STOK TELUR

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Stok Telur |
| **Actor** | Pekerja Kandang / Pemilik Peternakan |
| **Tujuan** | Mengetahui jumlah stok telur saat ini |
| **Kondisi Awal** | Pengguna sudah login |
| **Kondisi Akhir** | Sistem menampilkan informasi stok dalam butir & kg |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pengguna klik menu "Lihat Stok" | Sistem query tabel stok_telur |
| 2 | | Sistem menampilkan kartu/panel stok |
| 3 | | Sistem tampilkan: Stok Butir & Stok KG dalam angka besar |
| 4 | | Sistem tampilkan last updated timestamp |
| 5 | Pengguna melihat informasi stok | Sistem siap untuk transaksi (produksi/penjualan) |

### Skenario Alternatif

**A1. Stok Habis:**
- Sistem menampilkan warning/alert "Stok telur habis"
- Rekomendasi untuk input produksi baru

**A2. Stok Menipis:**
- Sistem menampilkan warning jika stok dibawah threshold tertentu

---

## 6. SKENARIO: KELOLA DATA KANDANG

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Kelola Data Kandang |
| **Actor** | Pemilik Peternakan |
| **Tujuan** | Menambah, mengubah, atau menghapus data kandang |
| **Kondisi Awal** | Pemilik sudah login, berada di halaman daftar kandang |
| **Kondisi Akhir** | Data kandang tersimpan/terupdate/terhapus di sistem |

### Skenario Utama (Basic Flow) - TAMBAH KANDANG

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik tombol "Tambah Kandang" | Sistem menampilkan form input kandang kosong |
| 2 | Pemilik input nama kandang | Sistem menerima input |
| 3 | Pemilik input jumlah ayam | Sistem validasi input harus numeric |
| 4 | Pemilik input keterangan (optional) | Sistem menerima input |
| 5 | Pemilik klik "Simpan" | Sistem validasi nama_kandang tidak boleh duplikat |
| 6 | | Sistem insert data ke tabel kandang |
| 7 | | Sistem menampilkan pesan sukses |
| 8 | | Sistem redirect ke daftar kandang (update halaman) |

### Skenario Alternatif - EDIT KANDANG

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik tombol "Edit" pada kandang tertentu | Sistem menampilkan form dengan data terpopulasi |
| 2 | Pemilik ubah nama kandang | Sistem menerima input |
| 3 | Pemilik ubah jumlah ayam | Sistem menerima input |
| 4 | Pemilik ubah status (aktif/nonaktif) | Sistem menerima pilihan |
| 5 | Pemilik klik "Simpan" | Sistem validasi |
| 6 | | Sistem update tabel kandang |
| 7 | | Sistem menampilkan pesan sukses |

### Skenario Alternatif - HAPUS KANDANG

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik tombol "Hapus" pada kandang | Sistem menampilkan konfirmasi |
| 2 | Pemilik confirm "Ya, hapus" | Sistem cek relasi (apakah ada produksi/pekerja di kandang ini) |
| 3 | | Jika ada relasi, sistem tampilkan warning "Data terkait akan ikut terhapus" |
| 4 | | Sistem delete record dan relasi-nya (cascade) |
| 5 | | Sistem menampilkan pesan sukses |

---

## 7. SKENARIO: KELOLA MASTER HARGA TELUR

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Kelola Master Harga Telur |
| **Actor** | Pemilik Peternakan |
| **Tujuan** | Mengatur harga telur berdasarkan referensi pasar |
| **Kondisi Awal** | Pemilik sudah login, berada di halaman master harga |
| **Kondisi Akhir** | Harga tersimpan historis, siap digunakan untuk transaksi |

### Skenario Utama (Basic Flow) - TAMBAH HARGA BARU

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik tombol "Tambah Harga Telur" | Sistem menampilkan form input harga |
| 2 | Pemilik pilih jenis harga (kandang/grosir/konsumen) | Sistem menerima pilihan |
| 3 | Pemilik input harga per kg | Sistem menerima input numeric |
| 4 | Pemilik input harga per butir (optional) | Sistem menerima input |
| 5 | Sistem auto-fill tanggal_berlaku = hari ini | |
| 6 | Pemilik klik "Simpan" | Sistem validasi tidak duplikat (jenis + tanggal) |
| 7 | | Sistem insert ke tabel harga_telur |
| 8 | | Sistem menampilkan pesan sukses |
| 9 | | Sistem not update/delete harga lama (keep historis) |

### Skenario Alternatif - LIHAT RIWAYAT HARGA

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik lihat tabel riwayat harga | Sistem tampilkan semua harga (dari dulu sampai sekarang) |
| 2 | | Sistem urutkan berdasarkan tanggal DESC |
| 3 | | Kolom: Jenis Harga, Harga/kg, Harga/butir, Tanggal berlaku |

### Notes
- **Harga NEVER dihapus** → untuk referensi historis transaksi
- Saat input penjualan, sistem akan ambil harga_telur_id terbaru sesuai tanggal transaksi

---

## 8. SKENARIO: INPUT PENJUALAN TELUR

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Input Penjualan Telur |
| **Actor** | Pemilik Peternakan |
| **Tujuan** | Mencatat transaksi penjualan telur dengan multiple items |
| **Kondisi Awal** | Pemilik sudah login, berada di halaman input penjualan |
| **Kondisi Akhir** | Transaksi penjualan tersimpan, stok berkurang otomatis |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik "Input Penjualan" | Sistem menampilkan form penjualan |
| 2 | Pemilik input tanggal jual | Sistem validasi tidak melebihi hari ini |
| 3 | Pemilik input nama pembeli | Sistem menerima input |
| 4 | Pemilik click "Tambah Item" | Sistem menampilkan row item kosong |
| 5 | Pemilik pilih harga telur id | Sistem popup/dropdown harga terbaru |
| 6 | Pemilik pilih satuan jual (butir/kg) | Sistem menerima pilihan |
| 7 | Pemilik input jumlah jual | Sistem validasi vs stok tersedia |
| 8 | Sistem auto-hitung konversi & subtotal | |
| 9 | Pemilik bisa tambah item lagi (repeat step 4-8) | |
| 10 | Pemilik klik "Proses Penjualan" | Sistem mulai transaction |
| 11 | | Sistem create record penjualan |
| 12 | | Sistem create detail_penjualan per item |
| 13 | | Sistem hitung total_harga (sum semua subtotal) |
| 14 | | Sistem hitung total stok yang diambil |
| 15 | | Sistem validasi stok cukup |
| 16 | | Sistem update stok_telur (decrement) |
| 17 | | Sistem commit transaction |
| 18 | | Sistem tampilkan pesan sukses + cetak struk (optional) |

### Skenario Alternatif (Error Cases)

**A1. Stok Tidak Cukup:**
- Sistem deteksi saat pemrosesan
- Sistem rollback transaction
- Sistem tampilkan error "Stok tidak cukup untuk item [nama item]"
- Pemilik harus kurangi qty atau hapus item

**A2. Harga Tidak Ditemukan:**
- Sistem tampilkan error "Harga telur belum di-input hari ini"
- Pemilik harus input harga dulu di menu master harga

**A3. Jumlah Negatif:**
- Sistem validasi "Jumlah harus lebih dari 0"

---

## 9. SKENARIO: LIHAT LAPORAN PRODUKSI

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Laporan Produksi |
| **Actor** | Pemilik Peternakan |
| **Tujuan** | Melihat analisis produksi telur dalam bentuk tabel & grafik |
| **Kondisi Awal** | Pemilik sudah login, berada di halaman laporan produksi |
| **Kondisi Akhir** | Sistem menampilkan laporan dengan visualisasi data |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik menu "Laporan Produksi" | Sistem menampilkan halaman laporan produksi |
| 2 | Sistem auto-set filter bulan/tahun = bulan saat ini | |
| 3 | Pemilik bisa ubah filter bulan/tahun | Sistem menerima filter |
| 4 | Pemilik klik "Filter" atau auto-refresh | Sistem query produksi_telur sesuai filter |
| 5 | | Sistem hitung sum produksi per hari |
| 6 | | Sistem hitung sum produksi per kandang |
| 7 | | Sistem render grafik garis (produksi harian vs waktu) |
| 8 | | Sistem render grafik donut (produksi per kandang) |
| 9 | | Sistem tampilkan tabel detail: Tanggal, Kandang, Satuan, Jumlah (Butir), Jumlah (Kg) |
| 10 | Pemilik melihat laporan | Sistem siap untuk export |

### Skenario Alternatif (Export)

**A1. Export PDF:**
- Pemilik klik button "Export PDF"
- Sistem generate PDF dengan tabel & grafik
- Sistem download file laporan-produksi-2026-04.pdf

**A2. Export Excel:**
- Pemilik klik button "Export Excel"
- Sistem generate Excel dengan sheet tabel
- Sistem download file laporan-produksi-2026-04.xlsx

### Skenario Alternatif (Conditional)

**A1. Data Periode Kosong:**
- Sistem tampilkan pesan "Data belum ada untuk periode ini"
- Grafik kosong/placeholder

**A2. Filter Custom:**
- Pemilik bisa filter per kandang tertentu
- Laporan hanya tampilkan data kandang yang dipilih

---

## 10. SKENARIO: LIHAT LAPORAN PENJUALAN

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Laporan Penjualan |
| **Actor** | Pemilik Peternakan |
| **Tujuan** | Melihat histori & analisis transaksi penjualan telur |
| **Kondisi Awal** | Pemilik sudah login, berada di halaman laporan penjualan |
| **Kondisi Akhir** | Sistem menampilkan laporan penjualan detail |

### Skenario Utama (Basic Flow)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik menu "Laporan Penjualan" | Sistem menampilkan halaman laporan penjualan |
| 2 | Sistem auto-set filter bulan/tahun = bulan saat ini | |
| 3 | Pemilik bisa ubah filter bulan/tahun | Sistem menerima filter |
| 4 | Pemilik klik "Filter" atau auto-refresh | Sistem query penjualan & detail_penjualan |
| 5 | | Sistem hitung sum total_harga per bulan |
| 6 | | Sistem render grafik (penjualan per hari / per minggu) |
| 7 | | Sistem tampilkan tabel: No, Tanggal, Pembeli, Items, Total Harga |
| 8 | | Sistem bisa expand row untuk lihat detail items |
| 9 | Pemilik melihat laporan | Sistem siap untuk export |

### Skenario Alternatif (Expand/Detail)

| # | Actor | Sistem |
|---|-------|--------|
| 1 | Pemilik klik tombol "Detail" pada transaksi | Sistem menampilkan modal/halaman detail transaksi |
| 2 | | Sistem tampilkan: Tanggal, Pembeli, List items (qty, harga, subtotal) |
| 3 | | Sistem tampilkan total_harga besar |

### Skenario Alternatif (Export)

**A1. Export PDF:**
- Pemilik klik button "Export PDF"
- Sistem generate PDF dengan tabel detail
- Sistem download file

**A2. Export Excel:**
- Pemilik klik button "Export Excel"
- Sistem generate Excel

---

## RINGKASAN FLOW ALUR SISTEM

```
┌─────────────────────────────────────────────────────┐
│  PENGGUNA BUKA APLIKASI                             │
└─────────────┬───────────────────────────────────────┘
              │
              ▼
    ┌─────────────────────┐
    │  UC1: LOGIN         │
    │  Validasi username  │
    │  masuk ke dashboard │
    └────┬────────────────┘
         │
    ┌────┴─────────────────────────────────────┐
    │                                           │
    ▼                                           ▼
PC2: Dashboard              PC3: Dashboard
(Pekerja Kandang)          (Pemilik Peternakan)
├─ UC2: Lihat Dashboard    ├─ UC2: Lihat Dashboard
├─ UC3: Riwayat Produksi   ├─ UC6: Kelola Kandang
├─ UC4: Lihat Stok         ├─ UC7: Kelola Harga
└─ UC1: Input Produksi     ├─ UC8: Input Penjualan
                           ├─ UC9: Laporan Produksi
                           ├─ UC10: Laporan Penjualan
                           └─ Setting/Admin
                           
┌─────────────────────────────────────────┐
│     ALUR TRANSAKSI HARIAN               │
├─────────────────────────────────────────┤
│ 1. Pekerja input produksi (UC1)         │
│    → Konversi otomatis (butir ↔ kg)    │
│    → Stok naik                          │
│                                         │
│ 2. Pemilik input harga (UC6)            │
│    → Master harga tersimpan historis    │
│                                         │
│ 3. Pemilik input penjualan (UC8)        │
│    → Referensi harga terbaru            │
│    → Konversi otomatis                  │
│    → Stok turun                         │
│                                         │
│ 4. Dashboard & Laporan (UC2,UC9,UC10)   │
│    → Menampilkan data realtime          │
│    → Visualisasi grafik                 │
│    → Export PDF/Excel                   │
└─────────────────────────────────────────┘
```

---

## NOTES PENTING

✅ **Konversi Satuan (Otomatis)**
- Nilai konversi diambil dari tabel `pengaturan`
- Default: 1kg = 16 butir (bisa diubah oleh admin)
- Semua input bisa dari kedua satuan (butir/kg)

✅ **Stok Management**
- Single record di tabel `stok_telur` (1 saja)
- Auto-update dari setiap produksi & penjualan
- Real-time untuk validasi transaksi

✅ **Harga Historis**
- NEVER delete/update harga lama
- Transaksi referensi ke `harga_telur_id` spesifik
- Perubahan harga = record baru

✅ **Authorization**
- Pekerja: hanya input kandang sendiri, read-only laporan
- Pemilik: full CRUD semua master & transaksi

✅ **Data Validation**
- Semua input numeric validasi tidak negatif
- Tanggal tidak boleh melebihi hari ini
- Stok validasi sebelum decrement
