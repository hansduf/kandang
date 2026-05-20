# ANALISIS SISTEM YANG KOMPREHENSIF
## Berdasarkan Code Review: Migrations, Models, Controllers & Routes

**Tanggal Analisis**: 8 April 2026  
**Status**: REAL IMPLEMENTATION (bukan hanya dokumentasi teori)

---

## 1. DATABASE SCHEMA AKTUAL

### A. STRUKTUR TABEL UTAMA

#### users
```
id, name, username, email, password, role (enum: pemilik/pekerja)
kandang_id (FK), created_at, updated_at
```
- **Role**: pemilik atau pekerja
- **Kandang_id**: Nullable, untuk assign pekerja ke kandang tertentu

#### kandangs
```
id, nama_kandang, jumlah_ayam, keterangan
status (enum: aktif/nonaktif), pic_id (FK to users), created_at, updated_at
```
- **pic_id**: Person In Charge (kepala kandang)
- **Status**: Untuk soft-delete logic

#### produksi_telur
```
id, kandang_id (FK), user_id (FK), tanggal_produksi, satuan_input
jumlah_input, jumlah_butir, jumlah_kg
ayam_mati, catatan, ayam_hidup, hdp, hhp, mortality
created_at, updated_at
```
- **Metrics yang ditrack**:
  - ayam_mati (died chickens)
  - ayam_hidup (living chickens)
  - hdp (Henday Production) = (telur / ayam hidup) × 100
  - hhp (?) = (telur / ayam awal) × 100
  - mortality = (ayam mati / ayam awal) × 100

#### harga_telur
```
id, jenis_harga (enum: kandang/grosir/konsumen)
harga_per_kg, harga_per_butir, tanggal_berlaku, tanggal_akhir
status (enum: aktif/hangus), user_id (FK), keterangan
updated_at
```
- **Historis**: Tidak pernah di-delete, hanya di-set status hangus
- **Multiple jenis**: kandang, grosir, konsumen
- **Scope**: aktif, aktifPadaTanggalJam untuk matching harga saat transaksi

#### penjualan
```
id, user_id (FK), tanggal_jual, jam_jual
nama_pembeli, total_harga, keterangan
created_at, updated_at
```
- **Multi-item**: Satu transaksi bisa banyak item

#### detail_penjualan
```
id, penjualan_id (FK), harga_telur_id (FK)
satuan_jual (butir/kg), jumlah_jual, jumlah_butir, jumlah_kg
harga_satuan, subtotal
harga_per_butir_saat_jual, harga_per_kg_saat_jual
tanggal_penjualan, jam_penjualan
```
- **Historical Price Snapshot**: harga_per_butir_saat_jual & harga_per_kg_saat_jual
- **Jam Penjualan**: Tersimpan per detail item

#### stok_telur
```
id, stok_butir, stok_kg, updated_at
```
- **Single record**: Agregasi stok keseluruhan (bukan per kandang!)
- **Real-time Update**: Diupdate saat ada produksi input atau penjualan

#### pengaturan
```
id, kunci (UNIQUE), nilai, tipe_data (string/integer/decimal/boolean)
keterangan, updated_at
```
- **Key-Value Store**: Untuk konfigurasi sistem
- **Contoh**: konversi_butir_per_kg, dll

---

## 2. FITUR YANG SUDAH IMPLEMENTED

### A. FITUR PEKERJA KANDANG

#### 1. Input Produksi Telur (CREATE)
- ✓ Input tanggal produksi
- ✓ Pilih satuan (butir/kg)
- ✓ Input jumlah
- ✓ **Auto-Konversi** ke satuan lain menggunakan ratio dari pengaturan
- ✓ Input ayam mati (optional)
- ✓ Input ayam hidup (required)
- ✓ Input catatan/keterangan (optional)
- ✓ **Auto-Calculate Metrics**:
  - HDP = (terilur / ayam hidup) × 100
  - HHP = (telur / ayam awal) × 100
  - Mortality = (ayam mati / ayam awal) × 100
- ✓ Data disimpan ke stok_telur secara real-time

#### 2. Lihat Riwayat Produksi (READ)
- ✓ Hanya kandang miliknya sendiri
- ✓ Paginated (10 per halaman)
- ✓ Sorted by tanggal_produksi DESC

#### 3. Dashboard Pekerja
- ✓ Ringkasan kandang yang dijaga
- ✓ Stok telur terkini
- ✓ Riwayat produksi 7 hari terakhir

#### 4. Profile
- ✓ Lihat profil
- ✓ Edit profil

---

### B. FITUR PEMILIK PETERNAKAN

#### 1. Kelola Kandang (CRUD)
- ✓ Tambah kandang baru
- ✓ Edit kandang (nama, jumlah ayam, keterangan, status)
- ✓ Hapus kandang (cascade delete produksi terkait)
- ✓ Lihat detail kandang
- ✓ Assign pic_id (Person In Charge)

#### 2. Kelola Produksi Telur (Read + Edit/Delete)
- ✓ Lihat riwayat produksi ALL kandang
- ✓ Edit riwayat produksi yang sudah input
- ✓ Hapus riwayat produksi (trigger stok recalculation)

#### 3. Kelola Harga Telur
- ✓ Tambah harga baru (tanpa hapus yang lama)
- ✓ Lihat riwayat harga (historis, never deleted)
- ✓ Set status (aktif/hangus) untuk price versioning
- ✓ Set tanggal_berlaku & tanggal_akhir
- ✓ Multiple jenis harga (kandang, grosir, konsumen)

#### 4. Input Penjualan Telur (CREATE)
- ✓ Multi-item per transaksi (satu penjualan banyak item)
- ✓ Input tanggal_jual & jam_jual
- ✓ Input nama_pembeli
- ✓ Untuk setiap item:
  - Input satuan_jual (butir/kg)
  - Input jumlah_jual (actual quantity)
  - Pilih harga dari master harga (hanya aktif)
  - **Auto-Konversi** ke satuan lain
  - **Auto-Calculate** subtotal
  - **Snapshot Harga**: Save harga_per_butir_saat_jual & harga_per_kg_saat_jual
- ✓ **Stock Validation**: Cek stok sebelum proses
- ✓ **Database Transaction**: Atomic operation
- ✓ **Auto-Update Stok**: Decrement stok_telur setelah penjualan

#### 5. Lihat Penjualan (READ)
- ✓ List semua penjualan
- ✓ Dengan detail items
- ✓ Dengan info harga saat jual
- ✓ Paginated (50 per halaman)

#### 6. Laporan Produksi
- ✓ View dalam bentuk tabel & grafik
- ✓ Filter berdasarkan periode (harian/mingguan/bulanan)
- ✓ Group by kandang
- ✓ Export ke PDF
- ✓ Export ke Excel

#### 7. Laporan Penjualan
- ✓ View dalam bentuk tabel & grafik
- ✓ Filter berdasarkan periode
- ✓ Lihat revenue & profit
- ✓ Detail items per transaksi
- ✓ Export ke PDF
- ✓ Export ke Excel

#### 8. Pengaturan Sistem
- ✓ Atur rasio konversi (konversi_butir_per_kg)
- ✓ Konfigurasi lainnya (via key-value pengaturan table)

#### 9. Kelola User
- ✓ Tambah user baru (assign role & kandang)
- ✓ Edit user
- ✓ Hapus user
- ✓ Manage roles & permissions (via Spatie\Permission)

#### 10. Dashboard Pemilik
- ✓ Ringkasan ALL kandang
- ✓ Total produksi hari ini
- ✓ Status stok telur
- ✓ Alert/notifikasi
- ✓ Grafik performa

---

## 3. FITUR YANG **BELUM DIJELASKAN** DI DOKUMENTASI

### A. Metrics Produksi
- **HDP (Henday Production)**: (Telur/Ayam Hidup) × 100 → Produksi per ayam yang hidup
- **HHP**: (Telur/Ayam Awal) × 100 → Produksi per ayam total
- **Mortality**: (Ayam Mati/Ayam Awal) × 100 → Persentase kematian

**TIDAK DIJELASKAN di dokumentasi!**

### B. Tracking Ayam
- Ayam mati per hari (ayam_mati)
- Ayam hidup saat produksi (ayam_hidup)
- Perhitungan ayam_hidup_saat_ini = jumlah_ayam - cumulative(ayam_mati)

**TIDAK DIJELASKAN di dokumentasi!**

### C. Jam Penjualan (jam_penjualan)
- Disimpan per detail item penjualan
- Bisa berbeda untuk setiap item dalam satu transaksi

**TIDAK DIJELASKAN di dokumentasi!**

### D. Price Type Variations
- 3 jenis harga simultan: kandang, grosir, konsumen
- Bisa berbeda di waktu yang sama

**TIDAK DIJELASKAN di dokumentasi!**

### E. Status Harga (aktif/hangus)
- Untuk deactivate harga lama
- isAktif() & isHangus() methods dengan logic tanggal

**TIDAK DIJELASKAN di dokumentasi!**

### F. Konversi Dinamis Per Kandang
- Tidak ada per-kandang conversion ratio
- Hanya satu global ratio di pengaturan table

**DOKUMENTASI KURANG JELAS!**

### G. Person In Charge (PIC) di Kandang
- pic_id field yang menglink ke user (kepala kandang)
- Structure untuk accountability

**NOT MENTIONED DI DOKUMENTASI!**

### H. API Endpoint untuk Stok
- `/api/stok` - untuk get stok real-time
- `/penjualan-harga-by-date` - untuk get harga by date

**TIDAK DOKUMENTASIKAN!**

---

## 4. BUSINESS LOGIC YANG COMPLEX

### A. Stock Management
```
Workflow:
1. Pekerja input produksi → stok_telur.stok_butir += jumlah_butir
2. Pemilik input penjualan → stok_telur.stok_butir -= jumlah_butir
3. Jika pemilik edit/delete produksi → stok_telur di-recalculate
4. Setiap operasi: stok_kg di-update berdasarkan ratio konversi
```

### B. Price Matching
```
Saat penjualan:
1. Cari harga aktif dari master harga
2. Gunakan harga_per_kg atau harga_per_butir sesuai satuan_jual
3. Snapshot harga ke detail_penjualan (for historical accuracy)
4. Jika ada harga baru esok hari, transaksi lama tetap pakai harga lama
```

### C. Multi-Item Transaction
```
Satu penjualan bisa:
- 10 butir dari harga kandang + 2 kg dari harga grosir
- Setiap item bisa satuan berbeda
- Setiap item price-nya independen
- Subtotal di-hitung per item, total_harga = sum(subtotal)
```

### D. Atomic Penjualan
```
Database transaction memastikan:
- SEMUA items berhasil atau SEMUA gagal
- Stock decrement HANYA jika seluruh transaksi sukses
- No partial sales
```

---

## 5. AUTHORIZATION & ACCESS CONTROL (Spatie\Permission)

**Routes**:
```php
// Authenticated only
/dashboard, /profile

// Pemilik only
/kandang, /harga, /penjualan, /laporan/*, /pengaturan, /users

// Pekerja only
/produksi (index, create, store, show)
```

**Data Scoping**:
- Pekerja: WHERE user_id = auth()->id() untuk produksi
- Pemilik: No WHERE clause, akses ALL data

---

## 6. SERVICES & HELPERS

### StockService
- Digunakan untuk menghitung stok dinamis
- Tidak ada manual update ke stok table (comment di code)
- Kalkulasi real-time dari produksi & penjualan

---

## 7. FITUR YANG MASIH MISSING/BELUM CLEAR

| No | Fitur | Status | Note |
|:--:|-------|--------|------|
| 1 | Can pekerja assign multiple kandang? | ? | Code hanya support satu kandang per user |
| 2 | Approval workflow untuk penjualan | ✗ | Pemilik langsung input, tidak ada approval |
| 3 | Supplier/Customer master | ✗ | Hanya nama_pembeli di table penjualan |
| 4 | Audit log/activity tracking | ✗ | Tidak ada tracking siapa ubah apa kapan |
| 5 | Backup/Recovery procedures | ? | Tidak dilihat di code |
| 6 | Notification system | ? | Tidak ada email/SMS notification |
| 7 | Multi-user pemilik (co-owner) | ? | Belum clear cara implementasinya |
| 8 | Performa dengan 10,000+ records | ? | Belum ada optimization untuk big data |
| 9 | Mobile responsiveness | ? | Need check views |
| 10 | Error handling & validation | ✓ | Ada, tapi perlu verify |

---

## 8. PERTANYAAN UNTUK CLARIFICATION DENGAN PEMILIK

1. **HDP, HHP, Mortality**: 
   - Apa tujuan track metrics ini?
   - Siapa yang perlu akses?
   - Ada target threshold atau alert?

2. **Ayam Mati Tracking**:
   - Ini mandatory reporting?
   - Ada analisis penyebab?
   - Ada dokumen medis/pemeriksaan?

3. **Price Types (kandang/grosir/konsumen)**:
   - Apakah semua 3 jenis selalu dipakai?
   - Atau bergantung produk/customer?
   - Logika switching antar tipe?

4. **Multi-Item Penjualan**:
   - Bisa campuran jenis harga dalam satu transaksi?
   - Contoh: 10 butir (kandang price) + 2kg (grosir price)?

5. **Per-Kandang Stock** vs **Global Stock**:
   - Apakah perlu tracking stok per kandang?
   - Atau cukup global saja?

6. **Person In Charge (PIC)**:
   - Fungsinya apa kebetulan?
   - PIC bisa assign multiple kandang?
   - Atau satu kandang = satu PIC saja?

7. **Laporan Custom**:
   - Apa saja yang mau exported (PDF/Excel)?
   - Format tertentu?
   - Frequency (harian/mingguan/monthly)?

8. **Pengguna/Role Tambahan**:
   - Ada Supervisor di atas Pekerja?
   - Ada Finance/Accounting role?
   - Ada Management/Director role?

---

## 9. REKOMENDASI DOKUMENTASI LENGKAP

Dokumentasi perlu mencakup:

1. **Entity Relationship Diagram (ERD)** - dengan semua 8 tabel & relationships
2. **Metrics Definition** - HDP, HHP, Mortality dengan formula & kegunaan
3. **Workflow Diagram** - per fitur utama
4. **Data Validation Rules** - per field, per role
5. **Business Rules** - stock management, price matching, etc
6. **API Endpoints** - lengkap dengan examples
7. **Permission Matrix** - detailed access control
8. **Integration Points** - dengan sistem lain (jika ada)
9. **Performance Considerations** - untuk data besar
10. **Disaster Recovery Plan** - backup & restore procedures

---

**STATUS DOKUMENTASI**:
- ✓ Basic features documented
- ⚠️ Advanced features partially documented
- ✗ Complex business logic NOT documented  
- ✗ Metrics & tracking features NOT documented
- ✗ API endpoints NOT documented
- ✗ Error scenarios NOT documented

