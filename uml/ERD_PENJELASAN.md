# Penjelasan ERD Database Hans Jaya Poultry
## Relasi Antar Tabel

---

## 1. Tabel users (Pengguna Sistem)

Tabel users menyimpan data pengguna dengan dua role: pemilik (owner) dan pekerja (worker). Tabel ini terhubung ke kandangs melalui dua kolom berbeda: **kandang_id** untuk assignmen pekerja (nullOnDelete sehingga data pekerja tetap ada), dan **pic_id** untuk supervisor kandang (SET NULL jika user dihapus). User juga membuat semua data transaksional: produksi_telur, penjualan, dan harga_telur dengan cascade delete. Ini menciptakan audit trail lengkap tentang siapa yang melakukan setiap aksi dalam sistem.

---

## 2. Tabel kandangs (Unit Operasional)

Kandang mewakili lokasi fisik operasional tempat ayam dipelihara. Setiap kandang memiliki supervisor via **pic_id** yang mereferensi users (SET NULL saat user dihapus untuk mempertahankan data kandang). Kandang memiliki banyak pencatatan produksi harian via **kandang_id** dengan cascade delete, menciptakan audit trail produksi per lokasi. Kandang juga memiliki relasi logis ke stok_telur (tanpa FK fisik) karena inventory dihitung aggregat global, bukan per-kandang. Status kandang dapat aktif atau nonaktif untuk lifecycle management.

---

## 3. Tabel produksi_telur (Pencatatan Produksi Harian)

Produksi adalah record harian output telur dari setiap kandang. Setiap produksi mereferensi **kandang_id** (cascade delete) dan **user_id** (cascade delete) untuk tracking lokasi dan petugas input. Menyimpan dual unit (butir dan kg) untuk fleksibilitas dan akurasi perhitungan. Tabel ini juga menyimpan metrik kesehatan: ayam_hidup, ayam_mati, HDP, HHP, mortality rate. Memiliki dual catatan: `catatan` untuk log sistem otomatis dan `keterangan` untuk catatan kesehatan manual. **IMMUTABLE** — tidak boleh diubah/dihapus setelah dibuat untuk menjaga compliance audit. Setiap pencatatan otomatis mempengaruhi perhitungan stok_telur.

---

## 4. Tabel harga_telur (Master Pricing)

Harga telur dikelola dengan lifecycle management: status aktif → hangus (inactive). Setiap harga ditetapkan oleh user (**user_id**, cascade delete) dan mendukung tiga kategori: kandang, grosir, konsumen untuk segmentasi pasar. Harga **tidak boleh dihapus** jika sudah direferensi di detail_penjualan (NO ACTION constraint). Menyimpan harga per kg dan per butir untuk fleksibilitas. Sistem mendukung multiple versions dari harga sama dengan tanggal_berlaku dan tanggal_akhir untuk temporal versioning dan historical tracking. Riwayat harga tetap tersimpan (di-mark hangus) untuk audit dan analisis trend.

---

## 5. Tabel penjualan (Sales Master)

Penjualan adalah header master untuk setiap transaksi penjualan. Dibuat oleh user (**user_id**, cascade delete) dan mencatat tanggal, pembeli, total harga. Menggunakan **master-detail pattern** dimana satu penjualan dapat terdiri dari banyak line items dengan jenis/jumlah/harga berbeda via detail_penjualan (cascade delete). Kolom total_harga dihitung dari sum subtotal detail items. Ketika penjualan dihapus, semua detail items otomatis terhapus untuk menjaga referential integrity.

---

## 6. Tabel detail_penjualan (Sales Line Items)

Detail penjualan adalah line item dalam setiap transaksi. Setiap item mereferensi satu penjualan header (**penjualan_id**, cascade delete) dan satu harga entry (**harga_telur_id**, NO ACTION). Menyimpan satuan jual (butir/kg), jumlah jual, dan dual units (butir+kg) untuk konversi akurat. **IMMUTABLE** — tidak boleh diubah/dihapus. Menggunakan **price snapshot pattern**: menyimpan tiga kolom harga snapshot (`harga_satuan`, `harga_per_butir_saat_jual`, `harga_per_kg_saat_jual`) pada saat transaksi untuk historical accuracy. Jika harga master berubah kemudian, transaksi historis tetap akurat. Setiap detail item otomatis mengurangi stok_telur melalui StockService.

---

## 7. Tabel stok_telur (Inventory - Calculated)

Stok adalah field yang dihitung real-time dari formula: Σ(Production) - Σ(Sales). **Tanpa FK fisik** — tabel standalone yang menyimpan agregat stock global, bukan per-kandang. Dipengaruhi secara logis oleh produksi_telur (menambah) dan detail_penjualan (mengurangi) melalui StockService. Menyimpan dual units (stok_butir dan stok_kg). **Bukan stored value** tetapi dihitung setiap kali ada transaksi untuk real-time accuracy dan mencegah stock inconsistency. Hanya memiliki **updated_at** timestamp tanpa created_at karena stok adalah continuously-updated field, bukan event record.

---

## 8. Tabel pengaturan (System Configuration)

Pengaturan adalah isolated key-value store untuk konfigurasi sistem. **Tanpa FK** — tidak tergantung tabel lain. Menyimpan setting seperti konversi_butir_per_kg (default 16), tax_rate, margin_target, dll dengan type hint (string/integer/decimal/boolean) untuk parsing. Memungkinkan konfigurasi diubah tanpa redeploy aplikasi atau database migration. Hanya memiliki **updated_at** timestamp tanpa created_at karena config adalah continuously-updated settings. Ideal untuk business rule yang sering berubah.

---

## 🔑 Pola Design Kunci

**Immutability** — Produksi dan penjualan tidak boleh diubah/dihapus (audit compliance). **Price Snapshot** — Harga disimpan saat transaksi, bukan lookup real-time (historical accuracy). **Dual Units** — Semua measured fields disimpan butir dan kg. **Cascade Delete** — Parent dihapus → child otomatis terhapus (referential integrity). **NO ACTION** — Harga tidak bisa dihapus jika sudah dipakai (data protection). **SET NULL** — FK jadi NULL saat parent dihapus (data preservation). **Logical Relationships** — Beberapa relasi tanpa FK fisik dikelola service layer (stok_telur). **Lifecycle Management** — Old records marked inactive, tidak dihapus (historical tracking).

---

**Sistem Manajemen Produksi & Penjualan Telur Hans Jaya Poultry**  
*Last Updated: April 22, 2026*
