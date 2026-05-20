# Penjelasan ERD - Versi Singkat Paragraf
## Relasi Antar Tabel Database Hans Jaya Poultry

---

## 1. Tabel users (Pengguna Sistem)

Tabel users terhubung ke beberapa tabel: **kandang_id** mereferensi kandangs untuk assignmen pekerja (nullOnDelete), **pic_id** untuk supervisor kandang (SET NULL), user juga membuat produksi_telur, penjualan, dan harga_telur dengan cascade delete. Role terbagi dua: pemilik (owner) atau pekerja (worker).

---

## 2. Tabel kandangs (Unit Operasional)

Setiap kandang memiliki supervisor via **pic_id** (SET NULL saat user dihapus) dan memiliki banyak pencatatan produksi via **kandang_id** (cascade delete). Kandang juga memiliki relasi logis ke stok_telur (tanpa FK fisik). Status: aktif/nonaktif.

---

## 3. Tabel produksi_telur (Pencatatan Produksi)

Setiap produksi milik satu kandang (**kandang_id**, cascade delete) dan diinput oleh satu user (**user_id**, cascade delete). Data produksi mempengaruhi stok_telur secara kalkulatif. **Sifat IMMUTABLE** — tidak bisa diubah/dihapus setelah dibuat untuk audit trail. Memiliki dual catatan: `catatan` (log sistem) dan `keterangan` (catatan kesehatan manual).

---

## 4. Tabel harga_telur (Master Pricing)

Harga ditetapkan oleh user (**user_id**, cascade delete) dan direferensi di detail_penjualan (**harga_telur_id**, NO ACTION — tidak bisa dihapus jika sudah dipakai). **Lifecycle management**: harga lama di-mark "hangus" (inactive), tidak dihapus. Mendukung multiple versions dengan temporal versioning.

---

## 5. Tabel penjualan (Sales Header)

Transaksi dibuat oleh user (**user_id**, cascade delete) dan terdiri dari banyak line items (**detail_penjualan** via penjualan_id, cascade delete). Menggunakan **master-detail pattern** dimana satu penjualan dapat memiliki items dengan harga berbeda.

---

## 6. Tabel detail_penjualan (Sales Line Items)

Setiap item milik satu transaksi (**penjualan_id**, cascade delete) dan referensi harga (**harga_telur_id**, NO ACTION). **Sifat IMMUTABLE** — tidak boleh diubah/dihapus. Menggunakan **price snapshot pattern** dengan 3 kolom harga snapshot (`harga_satuan`, `harga_per_butir_saat_jual`, `harga_per_kg_saat_jual`) untuk historical accuracy. Mempengaruhi stok_telur secara kalkulatif.

---

## 7. Tabel stok_telur (Inventory - Calculated)

**Standalone table tanpa FK** — menyimpan agregat stock global bukan per-kandang. Dipengaruhi produksi_telur (menambah stock) dan detail_penjualan (mengurangi stock) secara logis melalui formula: Σ(Production) - Σ(Sales). **Real-time calculation** via StockService, bukan stored value.

---

## 8. Tabel pengaturan (Configuration)

**Isolated entity** tanpa FK — key-value store untuk sistem (konversi_butir_per_kg, dll). Memungkinkan konfigurasi diubah tanpa redeploy. Hanya memiliki **updated_at** tanpa created_at.

---

## Delete Behavior

- **NULL ON DELETE**: users.kandang_id → kandangs (pekerja kehilangan assignmen)
- **SET NULL**: kandangs.pic_id ← users (supervisor dapat dihapus)
- **CASCADE**: users → produksi_telur, penjualan, harga_telur (semua ikut terhapus)
- **CASCADE**: penjualan → detail_penjualan (detail ikut terhapus)
- **NO ACTION**: harga_telur ← detail_penjualan (harga tidak bisa dihapus jika sudah dipakai)

---

## Pola Design Utama

1. **Immutability** — Produksi & penjualan tidak boleh diubah (audit compliance)
2. **Price Snapshot** — 3 kolom harga disimpan saat transaksi (historical accuracy)
3. **Dual Units** — Semua measured fields disimpan butir + kg
4. **Real-time Stock** — Calculated via service, tidak stored
5. **Logical Relationships** — Beberapa relasi tanpa FK fisik
6. **Cascade Delete** — Parent dihapus → child ikut terhapus
7. **Lifecycle Management** — Old records marked inactive, tidak dihapus

---

**Dokumen: Sistem Manajemen Produksi & Penjualan Telur Hans Jaya Poultry**  
*Format: Singkat Paragraf*  
*Last Updated: April 22, 2026*
