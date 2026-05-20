# IDENTIFIKASI PENGGUNA SISTEM
## Sistem Manajemen & Monitoring Produksi Penjualan Telur Ayam Petelur

---

## 4.1 Deskripsi Pengguna

| No | Pengguna | Deskripsi |
|:--:|----------|-----------|
| 1 | **Pekerja Kandang** | Pekerja kandang merupakan pengguna yang bekerja langsung di lapangan untuk merawat ayam dan mencatat hasil produksi telur harian. Pengguna ini memiliki akses terbatas hanya pada fitur input produksi telur, melihat dashboard kandang miliknya, riwayat produksi, dan informasi stok telur. Pekerja kandang tidak memiliki akses untuk mengelola data master, melakukan transaksi penjualan, atau melihat laporan keuangan. |
| 2 | **Pemilik Peternakan** | Pemilik peternakan merupakan pengguna yang mengelola bisnis dan operasional peternakan secara keseluruhan. Pengguna ini memiliki akses penuh ke semua fitur dalam sistem, termasuk manajemen kandang, mengelola harga telur, mencatat transaksi penjualan, melihat laporan produksi dan penjualan, serta pengaturan sistem. Pemilik peternakan bertanggung jawab untuk pengambilan keputusan bisnis berdasarkan data yang telah dicatat. |

---

## 4.2 Analisis Pengguna Sistem

### A. PEKERJA KANDANG

#### Karakteristik:
- **Jumlah Pengguna**: 1 - N (tergantung jumlah kandang dan shift kerja)
- **Lokasi Kerja**: Di lapangan/lokasi kandang
- **Latar Belakang Pendidikan**: SMP - SMA
- **Kemampuan Teknis**: Dasar (basic)
- **Frekuensi Akses**: 1-2 kali per hari sesuai jadwal shift

#### Tanggungjawab Utama:
1. **Monitoring Harian**
   - Mengamati kondisi ayam dan kandang setiap hari
   - Mencatat observasi penting untuk laporan ke atasan
   
2. **Pencatatan Produksi Telur**
   - Mengumpulkan/menghitung telur yang diproduksi per hari
   - Input data produksi ke sistem (tanggal, satuan, dan jumlah)
   - Memilih satuan input (butir atau kilogram)
   
3. **Pelaporan**
   - Melaporkan kondisi dan masalah kandang ke Pemilik
   - Memberikan informasi real-time tentang produksi

#### Fitur yang Dapat Diakses:
- ✓ Login / Logout
- ✓ Dashboard kandang sendiri
- ✓ Input Produksi Telur Harian
- ✓ Lihat Riwayat Produksi (kandang miliknya saja)
- ✓ Lihat Stok Telur (informatif)

#### Fitur yang TIDAK Dapat Diakses:
- ✗ Kelola Kandang (tambah/edit/hapus)
- ✗ Kelola Harga Telur
- ✗ Input Penjualan
- ✗ Laporan Produksi & Penjualan
- ✗ Pengaturan Sistem
- ✗ Data pengguna lain

#### Data Scope:
- Hanya dapat melihat dan mengelola data kandang yang menjadi tanggung jawabnya
- Hanya dapat melihat riwayat produksi kandang sendiri
- Tidak dapat mengakses data kandang lain atau data penjualan

---

### B. PEMILIK PETERNAKAN

#### Karakteristik:
- **Jumlah Pengguna**: 1 (atau lebih jika ada co-owner)
- **Lokasi Kerja**: Kantor/rumah + lapangan (mobile)
- **Latar Belakang Pendidikan**: SMA - Sarjana
- **Kemampuan Teknis**: Intermediate - Advanced
- **Frekuensi Akses**: 1-3 kali per hari (fleksibel sesuai kebutuhan bisnis)

#### Tanggungjawab Utama:
1. **Perencanaan & Manajemen Bisnis**
   - Menentukan strategi produksi dan penjualan
   - Memantau kinerja setiap kandang
   - Melakukan analisis profitabilitas
   
2. **Pengelolaan Master Data**
   - Menambah, mengubah, dan menghapus data kandang
   - Menentukan dan mengubah harga telur berdasarkan pasar
   - Mengelola data pengguna dan hak akses pekerja
   
3. **Pencatatan Transaksi Penjualan**
   - Mencatat setiap transaksi penjualan telur
   - Menentukan jumlah, satuan, dan harga untuk setiap penjualan
   - Validasi ketersediaan stok sebelum transaksi
   
4. **Analisis & Pelaporan**
   - Melihat laporan produksi per kandang dan total
   - Melihat laporan penjualan dan revenue
   - Membuat keputusan bisnis berdasarkan data yang tersedia
   
5. **Monitoring Real-time**
   - Melihat stok telur terkini
   - Memonitor produksi dari berbagai kandang
   - Menerima alert jika ada masalah atau stok menipis

#### Fitur yang Dapat Diakses:
- ✓ Login / Logout
- ✓ Dashboard Lengkap (semua kandang)
- ✓ Lihat & Kelola Data Kandang (tambah/edit/hapus)
- ✓ Lihat Riwayat Produksi (semua kandang, bisa edit/hapus)
- ✓ Lihat Stok Telur (real-time, breakdown per kandang)
- ✓ Kelola Master Harga Telur (tambah, lihat riwayat)
- ✓ Input Penjualan Telur (dengan validasi stok otomatis)
- ✓ Lihat Laporan Produksi (grafik, tabel, export PDF/Excel)
- ✓ Lihat Laporan Penjualan (grafik, detail, export PDF/Excel)
- ✓ Pengaturan Sistem (konversi satuan, dll)

#### Data Scope:
- Dapat mengakses ALL data kandang, produksi, penjualan, dan harga
- Dapat melakukan operasi CRUD penuh pada master data
- Dapat melihat analisis dan laporan untuk seluruh peternakan
- Memiliki akses pengaturan sistem untuk konfigurasi umum

---

## 4.3 Matriks Akses Pengguna

| Fitur / Use Case | Pekerja Kandang | Pemilik Peternakan |
|---|:---:|:---:|
| **AUTHENTICATION** |  |  |
| Login / Logout | ✓ | ✓ |
| **DASHBOARD** |  |  |
| Lihat Dashboard | ✓* | ✓ |
| *Catatan: Pekerja hanya lihat kandang sendiri, Pemilik lihat semua | | |
| **PRODUKSI TELUR** |  |  |
| Input Produksi Harian | ✓ | ✗ |
| Lihat Riwayat Produksi | ✓* | ✓ |
| Edit Riwayat Produksi | ✗ | ✓ |
| Hapus Riwayat Produksi | ✗ | ✓ |
| **MANAJEMEN KANDANG** |  |  |
| Lihat Data Kandang | ✓* | ✓ |
| Tambah Kandang | ✗ | ✓ |
| Edit Kandang | ✗ | ✓ |
| Hapus Kandang | ✗ | ✓ |
| **MANAJEMEN HARGA** |  |  |
| Lihat Harga Telur | ✓** | ✓ |
| Tambah Harga Baru | ✗ | ✓ |
| Lihat Riwayat Harga | ✗ | ✓ |
| **STOK TELUR** |  |  |
| Lihat Stok Real-time | ✓* | ✓ |
| Breakdown per Kandang | ✓* | ✓ |
| **PENJUALAN** |  |  |
| Input Penjualan | ✗ | ✓ |
| Validasi Stok | (Otomatis) | ✓ |
| Print Nota Penjualan | ✗ | ✓ |
| **LAPORAN & ANALISIS** |  |  |
| Laporan Produksi | ✗ | ✓ |
| Laporan Penjualan | ✗ | ✓ |
| Grafik Produksi | ✗ | ✓ |
| Grafik Penjualan | ✗ | ✓ |
| Export PDF | ✗ | ✓ |
| Export Excel | ✗ | ✓ |
| **PENGATURAN SISTEM** |  |  |
| Atur Konversi Satuan | ✗ | ✓ |
| Kelola Pengguna | ✗ | ✓ |

**Keterangan:**
- ✓ = Akses penuh
- ✗ = Tidak ada akses
- \* = Akses terbatas (hanya data kandang/bagian sendiri)
- \*\* = Lihat harga saat input, namun tidak bisa kelola sendiri

---

## 4.4 Skenario Penggunaan Sistem

### Skenario 1: Pekerja Kandang Input Produksi Harian

**Waktu**: Setiap hari, pagi/siang/malam (sesuai shift kerja)

**Alur**:
1. Pekerja datang ke kandang dan mulai bekerja (pukul 07:00 atau sesuai jadwal)
2. Pekerja login ke sistem menggunakan username dan password
3. Sistem menampilkan dashboard kandang milik pekerja
4. Pekerja melihat informasi:
   - Nama kandang yang ditugasi
   - Stok telur terkini
   - Riwayat produksi 7 hari terakhir
5. Pekerja melakukan **Input Produksi Telur Harian**:
   - Pilih tanggal produksi
   - Pilih satuan (butir atau kilogram)
   - Input jumlah hasil produksi
   - Klik "Simpan"
6. Sistem melakukan:
   - Validasi input
   - Konversi otomatis ke satuan lain
   - Update stok telur secara real-time
   - Simpan record ke database
7. Sistem menampilkan pesan sukses dan ringkasan data yang tersimpan
8. Pekerja bisa melihat riwayat produksi untuk verifikasi
9. Pekerja logout dari sistem

**Output Sistem**:
- Data produksi tersimpan dan terintegrasi dengan stok
- Pemilik bisa melihat update produksi secara real-time

---

### Skenario 2: Pemilik Peternakan Memonitor & Menganalisis Bisnis

**Waktu**: Pagi (08:00), siang (13:00), sore (17:00)

**Alur Pagi (Morning Check)**:
1. Pemilik login ke sistem dari rumah/kantor
2. Sistem menampilkan dashboard lengkap dengan:
   - Total produksi semua kandang hari ini
   - Status stok telur
   - Alert/notifikasi penting
3. Pemilik melihat grafik performa harian
4. Jika ada masalah (produksi menurun/stok menipis), pemilik langsung hubungi pekerja

**Alur Siang (Sales Management)**:
1. Pemilik input penjualan telur jika ada pembeli
   - Pilih kandang/item yang dijual
   - Input jumlah (butir/kg)
   - Sistem auto-konversi dan validasi stok
   - Pilih harga (berdasarkan master harga)
   - Simpan transaksi
2. Jika diperlukan, pemilik update harga telur berdasarkan harga pasar terkini

**Alur Sore (Analisis & Laporan)**:No.	Kebutuhan	Aktor
1.	User harus login terlebih dahulu untuk bisa login	Admin 
2.	User bisa menampilkan, mengubah data profil 	Admin 
No.	Kebutuhan	Aktor
1.	User harus login terlebih dahulu untuk bisa login	Admin 
2.	User bisa menampilkan, mengubah data profil 	Admin 

1. Pemilik lihat Laporan Produksi:
   - Filter periode (harian/minggu/bulan)
   - Lihat tabel dan grafik produksi per kandang
   - Analisis trend dan performa
   - Export ke PDF untuk arsip
2. Pemilik lihat Laporan Penjualan:
   - Lihat detail transaksi penjualan
   - Analisis revenue dan profit per kandang
   - Export ke Excel untuk laporan finansial

**Output Sistem**:
- Dashboard yang membantu pengambilan keputusan
- Laporan yang tersedia untuk evaluasi bisnis
- Data yang akurat untuk perencanaan ke depan

---

## 4.5 Implementasi Teknis

### Database Schema - Users Table

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('pekerja', 'pemilik') NOT NULL,
    kandang_id BIGINT NULL,  -- Foreign key, untuk Pekerja
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (kandang_id) REFERENCES kandangs(id) ON DELETE SET NULL
);
```

### Authorization Middleware (Laravel)

```php
// Route group dengan middleware role
Route::group(['middleware' => ['auth', 'role:pekerja']], function () {
    // Routes for Pekerja
    Route::post('/produksi', 'ProduksiController@store');
    Route::get('/riwayat-produksi', 'ProduksiController@index');
    Route::get('/dashboard-pekerja', 'DashboardController@pekerja');
});

Route::group(['middleware' => ['auth', 'role:pemilik']], function () {
    // Routes for Pemilik
    Route::resource('kandang', 'KandangController');
    Route::resource('harga', 'HargaTelurController');
    Route::resource('penjualan', 'PenjualanController');
    Route::get('/laporan-produksi', 'LaporanController@produksi');
    Route::get('/laporan-penjualan', 'LaporanController@penjualan');
});
```

### Query Scope - Model Eloquent

```php
// Model Produksi
public function scopeForUser($query, $user)
{
    if ($user->role === 'pekerja') {
        return $query->where('kandang_id', $user->kandang_id);
    }
    // Pemilik dapat akses semua
    return $query;
}

// Usage
$produksi = ProduksiTelur::forUser(auth()->user())->get();
```

---

## 4.6 Pertanyaan untuk Diskusi Lebih Lanjut

1. **Multi-Kandang untuk Pekerja?**
   - Apakah satu Pekerja bisa ditugaskan ke multiple kandang?
   - Jika ya, bagaimana cara assign?

2. **Supervisor/Tim Lead?**
   - Apakah ada role ketiga (Supervisor) di atas pekerja?
   - Atau langsung lapor ke Pemilik?

3. **Approval Workflow?**
   - Apakah ada approval untuk transaksi penjualan?
   - Atau Pemilik langsung input dan sistem catat?

4. **Edit Data Produksi?**
   - Apakah Pekerja boleh edit produksi yang sudah input?
   - Atau hanya Pemilik yang bisa edit/hapus?

5. **Audit Log?**
   - Apakah perlu tracking siapa mengubah apa dan kapan?

6. **Sistem Approval Penjualan?**
   - Apakah penjualan bisa di-hold pending untuk approval?

7. **Multi-User pada Pemilik?**
   - Apakah bisa ada lebih dari satu Pemilik atau co-owner?

---

**Dokumen ini merupakan bagian dari spesifikasi sistem dan dapat dikonversi ke format PDF untuk keperluan dokumentasi resmi.**

