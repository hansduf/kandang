# Entity-Relationship Diagram (ERD) - Hans Jaya Poultry Database

## Overview

Entity-Relationship Diagram (ERD) ini menggambarkan struktur database dan hubungan antar tabel dalam sistem manajemen produksi dan penjualan telur Hans Jaya Poultry.

---

## 📊 Database Tables (8 Tabel Utama)

### 1. **users**
**Primary Key:** `id`  
**Foreign Key:** `kandang_id` → `kandangs.id`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| name | string | Nama pengguna |
| email | string | Email unik |
| password | string | Password terenkripsi |
| kandang_id | int (FK) | Referensi ke kandang yang ditugaskan |
| created_at | timestamp | Waktu pembuatan record |
| updated_at | timestamp | Waktu update terakhir |

---

### 2. **kandangs**
**Primary Key:** `id`  
**Foreign Key:** `pic_id` → `users.id` (Person in Charge)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| nama_kandang | string | Nama/identitas kandang |
| jumlah_ayam | int | Jumlah ayam dalam kandang |
| keterangan | text | Catatan tambahan |
| status | enum | Status: aktif atau nonaktif |
| pic_id | int (FK) | Penanggung jawab kandang |
| created_at | timestamp | Waktu pembuatan record |
| updated_at | timestamp | Waktu update terakhir |

---

### 3. **produksi_telur**
**Primary Key:** `id`  
**Foreign Keys:** 
- `kandang_id` → `kandangs.id` (cascadeOnDelete)
- `user_id` → `users.id` (cascadeOnDelete)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| kandang_id | int (FK) | Referensi ke kandang |
| user_id | int (FK) | Referensi ke worker yang input data |
| tanggal_produksi | date | Tanggal produksi |
| satuan_input | enum | Satuan: butir atau kg |
| jumlah_input | decimal | Jumlah input |
| jumlah_butir | int | Hasil konversi ke butir |
| jumlah_kg | decimal | Hasil konversi ke kg |
| HDP | decimal | Daily Hen Production rate |
| HHP | decimal | Daily Hen Health rate |
| mortality | decimal | Tingkat kematian (%) |
| keterangan | text | Catatan kesehatan/kondisi |
| created_at | timestamp | Waktu input |
| updated_at | timestamp | Waktu update |

**Karakteristik:** Immutable once created - untuk audit trail

---

### 4. **harga_telur**
**Primary Key:** `id`  
**Foreign Key:** `user_id` → `users.id` (cascadeOnDelete)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| jenis_harga | enum | Tipe harga: kandang, grosir, konsumen |
| harga_per_kg | decimal | Harga per kilogram |
| harga_per_butir | decimal | Harga per butir (opsional) |
| tanggal_berlaku | date | Tanggal efektif harga |
| user_id | int (FK) | Manager yang set harga |
| status | enum | Status: aktif atau hangus (expired) |
| keterangan | text | Catatan harga |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update |

**Karakteristik:** Lifecycle management - harga lama auto-expire saat baru dibuat

---

### 5. **penjualan**
**Primary Key:** `id`  
**Foreign Key:** `user_id` → `users.id` (cascadeOnDelete)

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| user_id | int (FK) | Referensi ke user yang buat penjualan |
| tanggal_jual | date | Tanggal transaksi |
| nama_pembeli | string | Nama pembeli |
| total_harga | decimal | Total nilai transaksi |
| keterangan | text | Catatan transaksi |
| created_at | timestamp | Waktu transaksi |
| updated_at | timestamp | Waktu update |

**Karakteristik:** Master record untuk penjualan

---

### 6. **detail_penjualan**
**Primary Key:** `id`  
**Foreign Keys:**
- `penjualan_id` → `penjualan.id` (cascadeOnDelete)
- `harga_telur_id` → `harga_telur.id`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| penjualan_id | int (FK) | Referensi ke transaksi penjualan |
| harga_telur_id | int (FK) | Referensi ke harga saat transaksi |
| satuan_jual | enum | Satuan: butir atau kg |
| jumlah_jual | decimal | Jumlah terjual |
| jumlah_butir | int | Konversi ke butir |
| jumlah_kg | decimal | Konversi ke kg |
| harga_satuan | decimal | Harga per satuan (snapshot) |
| subtotal | decimal | Total item (jumlah × harga) |
| jam_penjualan | time | Waktu penjualan spesifik |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update |

**Karakteristik:** 
- Immutable - tidak boleh di-update atau di-delete
- Menyimpan price snapshot untuk audit trail
- Ensures data integrity untuk pelaporan

---

### 7. **stok_telur**
**Primary Key:** `id`  
**Karakteristik:** Tidak ada FK - data calculated

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| stok_butir | int | Stok dalam satuan butir |
| stok_kg | decimal | Stok dalam satuan kg |
| updated_at | timestamp | Waktu update terakhir |

**Karakteristik:** 
- Dihitung secara real-time: `Σ(ProduksiTelur) - Σ(DetailPenjualan)`
- Bukan manual update
- Diupdate oleh service setelah setiap transaksi

---

### 8. **pengaturan**
**Primary Key:** `id`  
**Karakteristik:** Tidak ada FK - sistem konfigurasi

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | int (PK) | Identifier unik |
| kunci | string (unique) | Config key |
| nilai | string | Config value |
| tipe_data | enum | Tipe: string, integer, decimal, boolean |
| keterangan | text | Deskripsi config |
| updated_at | timestamp | Waktu update |

**Contoh Config:**
- `konversi_ratio` = "16" (16 butir = 1 kg)

---

## 🔗 Entity Relationships

### One-to-Many (1-to-N)

```
users (1) ──→ (N) kandangs            [User dapat mengelola banyak kandang]
     ├─→ (N) produksi_telur           [User dapat input banyak produksi]
     ├─→ (N) penjualan                [User dapat membuat banyak penjualan]
     └─→ (N) harga_telur              [User dapat mengatur banyak harga]

kandangs (1) ──→ (N) produksi_telur   [Kandang punya banyak record produksi]

penjualan (1) ──→ (N) detail_penjualan [Penjualan punya banyak item detail]

harga_telur (1) ──→ (N) detail_penjualan [Harga digunakan di banyak penjualan]
```

### One-to-One (1-to-1)

```
kandangs (1) ──→ (1) stok_telur      [Kandang has one current stock]
```

### Other Relationships

```
kandangs.pic_id → users.id           [PIC reference - who manages this coop]
```

---

## 📋 Cascade Behavior

| Relasi | OnDelete | Keterangan |
|--------|----------|-----------|
| produksi_telur → users | CASCADE | Jika user terhapus, semua produksi ia akan terhapus |
| produksi_telur → kandangs | CASCADE | Jika kandang terhapus, semua produksi habis |
| detail_penjualan → penjualan | CASCADE | Jika penjualan terhapus, semua detail juga terhapus |
| detail_penjualan → harga_telur | NO ACTION | Harga tidak bisa terhapus jika ada di detail |

---

## 🎯 Design Patterns

### ✓ **Immutable Records**
- `produksi_telur` dan `detail_penjualan` tidak boleh di-update/delete
- Memastikan audit trail yang akurat dan compliant
- Mencegah data manipulation retrospektif

### ✓ **Price Snapshot**
- `detail_penjualan` menyimpan `harga_satuan` (snapshot saat transaksi)
- Bukan referensi langsung ke `harga_telur`
- Memungkinkan historical pricing accuracy

### ✓ **Dynamic Stock**
- `stok_telur` tidak punya FK ke tabel manapun
- Dihitung real-time dari produksi dan penjualan
- Diupdate oleh service layer, bukan manual

### ✓ **Lifecycle Management**
- `harga_telur.status` tracks: aktif → hangus
- Harga lama auto-expire saat baru dibuat
- History tetap tersimpan untuk audit

---

## 🔄 Transaction Flow

```
1. PRODUCTION LOG
   User → Input ProduksiTelur (kandang_id, tanggal, jumlah, metrics)
        → Record saved to database

2. PRICING UPDATE
   Manager → Set HargaTelur (jenis, harga, tanggal_berlaku)
          → Old prices auto-expire

3. SALES TRANSACTION
   User → Create Penjualan (user_id, tanggal, nama_pembeli)
       → Add DetailPenjualan items (jumlah, harga_telur_id)
       → System captures price snapshot
       → Transaction committed

4. STOCK UPDATE (Post-transaction)
   StockService → Calculate StokTelur
               → Σ(ProduksiTelur) - Σ(DetailPenjualan)
               → Update stok_telur
```

---

## 📌 Key Constraints

| Constraint | Tabel | Kolom | Keterangan |
|-----------|-------|-------|-----------|
| PRIMARY KEY | semua | id | Unique identifier |
| UNIQUE | pengaturan | kunci | Setiap config key unik |
| FOREIGN KEY | users | kandang_id | Referensi valid ke kandang |
| FOREIGN KEY | kandangs | pic_id | Referensi valid ke user |
| FOREIGN KEY | produksi_telur | kandang_id, user_id | Referensi valid |
| FOREIGN KEY | harga_telur | user_id | Referensi valid ke user |
| FOREIGN KEY | penjualan | user_id | Referensi valid ke user |
| FOREIGN KEY | detail_penjualan | penjualan_id, harga_telur_id | Referensi valid |

---

## 🗄️ Data Integrity

✓ **Referential Integrity** — FK constraints mencegah orphaned records  
✓ **Cascade Delete** — Menghapus parent otomatis delete child (jika applicable)  
✓ **Unique Constraints** — Mencegah duplikasi data  
✓ **Immutable Records** — Produksi & Detail penjualan tidak bisa diubah  
✓ **Calculated Fields** — Stok dihitung otomatis, bukan manual input  

---

## 📂 Related Files

- **Migrations:** `database/migrations/`
- **Models:** `app/Models/`
- **Service Layer:** `app/Services/StockService.php`
- **Class Diagram:** `uml/class_diagram.*`
