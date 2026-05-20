# Penjelasan ERD - Versi Ringkas
## Relasi Antar Tabel Database Hans Jaya Poultry

---

## 1. **users** (Pengguna Sistem)
- **kandang_id** → kandangs: User ditugaskan ke kandang (nullOnDelete)
- **pic_id** → kandangs: User sebagai supervisor kandang (SET NULL)
- **user_id** → produksi_telur: User input data produksi (CASCADE)
- **user_id** → penjualan: User buat transaksi penjualan (CASCADE)
- **user_id** → harga_telur: User set harga (CASCADE)

**Role:** pemilik (owner) atau pekerja (worker)

---

## 2. **kandangs** (Unit Operasional)
- **pic_id** ← users: Supervisor kandang (SET NULL jika user dihapus)
- **kandang_id** → produksi_telur: Banyak pencatatan produksi (CASCADE)
- **Logis ke stok_telur:** Inventory kandang (tanpa FK fisik)

**Status:** aktif / nonaktif

---

## 3. **produksi_telur** (Pencatatan Produksi)
- **kandang_id** ← kandangs: Produksi milik kandang (CASCADE)
- **user_id** ← users: Diinput oleh user (CASCADE)
- **→ stok_telur:** Kalkulatif - menambah stock

**Sifat:** IMMUTABLE (tidak bisa diubah/dihapus setelah dibuat)
**Kolom catatan:** 
- `catatan`: Log sistem otomatis
- `keterangan`: Catatan kesehatan manual

---

## 4. **harga_telur** (Master Pricing)
- **user_id** ← users: Harga ditetapkan user (CASCADE)
- **harga_telur_id** → detail_penjualan: Direferensi penjualan (NO ACTION - tidak bisa dihapus jika sudah dipakai)

**Lifecycle:** aktif → hangus (mark inactive, tidak dihapus)
**Versioning:** Multiple prices per kategori dengan temporal management

---

## 5. **penjualan** (Sales Header)
- **user_id** ← users: Transaksi dibuat user (CASCADE)
- **id** → detail_penjualan: Header punya banyak line items (CASCADE)

**Master-Detail pattern:** Header + Detail items dengan harga berbeda

---

## 6. **detail_penjualan** (Sales Line Items)
- **penjualan_id** ← penjualan: Item milik transaksi (CASCADE)
- **harga_telur_id** ← harga_telur: Reference harga (NO ACTION)
- **→ stok_telur:** Kalkulatif - mengurangi stock

**Sifat:** IMMUTABLE (tidak bisa diubah/dihapus)
**Price Snapshot:** Menyimpan 3 kolom harga snapshot:
- `harga_satuan` (harga utama)
- `harga_per_butir_saat_jual`
- `harga_per_kg_saat_jual`

---

## 7. **stok_telur** (Inventory - Calculated)
- **Tanpa FK:** Standalone table (global stock aggregate)
- **← produksi_telur:** Logis - production adds stock
- **← detail_penjualan:** Logis - sales deduct stock
- **Formula:** Σ(Production) - Σ(Sales)

**Real-time calculation via StockService**

---

## 8. **pengaturan** (Configuration)
- **Isolated entity:** Key-value store (konversi_butir_per_kg, dll)
- **Tanpa FK:** Independent configuration management
- **Timestamps:** Hanya updated_at (no created_at)

---

## 📊 Delete Behavior Summary

| Relasi | Behavior | Keterangan |
|--------|----------|-----------|
| users → kandangs.kandang_id | NULL ON DELETE | Pekerja kehilangan assignmen |
| kandangs ← users.pic_id | SET NULL | Supervisor dapat dihapus |
| users → produksi_telur | CASCADE | Data produksi ikut terhapus |
| users → penjualan | CASCADE | Transaksi ikut terhapus |
| penjualan → detail_penjualan | CASCADE | Detail ikut terhapus |
| harga_telur → detail_penjualan | NO ACTION | Harga tidak bisa dihapus jika sudah dipakai |

---

## 🎯 Pola Design Utama

1. **Immutability:** Produksi & Penjualan tidak boleh diubah (audit compliance)
2. **Price Snapshot:** 3 kolom harga di detail_penjualan (historical accuracy)
3. **Dual Units:** Semua measured fields disimpan butir + kg
4. **Real-time Stock:** Calculated via service, bukan stored value
5. **Logical Relationships:** Beberapa relasi tanpa FK fisik (stok_telur)
6. **Cascade Delete:** Parent dihapus → child ikut terhapus
7. **Lifecycle Management:** Old records marked inactive, tidak dihapus

---

**Dokumen: Sistem Manajemen Produksi & Penjualan Telur Hans Jaya Poultry**  
*Format: Ringkas + Tabel Referensi*  
*Last Updated: April 22, 2026*
