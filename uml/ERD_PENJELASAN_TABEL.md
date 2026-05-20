# Entity-Relationship Diagram (ERD)  
## Penjelasan Detail Setiap Tabel Database

---

## 1. **users** (Master Data - Pengguna)

**Fungsi Utama:** Menyimpan data pengguna sistem yang terdiri dari pekerja dan pemilik bisnis untuk mengelola akses dan audit trail.

**Primary Key:** `id` (identifier unik untuk setiap pengguna)

**Foreign Key:** `kandang_id` mereferensi ke kandangs (pengguna/pekerja ditugaskan ke kandang tertentu)

**Kolom Penting:**  
- `name`: Nama lengkap pengguna
- `email`: Email unik untuk login dan komunikasi
- `password`: Password terenkripsi menggunakan bcrypt
- `role`: Enum role - 'pemilik' (owner) atau 'pekerja' (worker)
- `kandang_id`: Referensi ke kandang yang ditugaskan (umumnya untuk pekerja)

**Relasi:**
- One-to-Many ke kandangs (satu user bisa mengelola/ditugaskan ke banyak kandang)
- One-to-Many ke produksi_telur (satu user bisa input banyak record produksi)
- One-to-Many ke penjualan (satu user bisa membuat banyak transaksi penjualan)
- One-to-Many ke harga_telur (satu user bisa set banyak harga)
- One-to-Many ke kandangs (pic_id) (satu user dapat menjadi supervisor untuk banyak kandang)
- One-to-Many ke kandangs (kandang_id) — jika kandang dihapus, kandang_id user menjadi NULL (nullOnDelete)

**Karakteristik Khusus:** Jika user dihapus, kandang_id yang mereference user menjadi NULL (tidak cascade complete). Menggunakan enum role ('pemilik' | 'pekerja') untuk pemisahan akses dan permission management. Timestamps mencatat setiap perubahan data pengguna untuk audit trail. Kolom tambahan: `email_verified_at`, `remember_token`, `username`.

---

## 2. **kandangs** (Master Data - Unit Operasional)

**Fungsi Utama:** Menyimpan data fisik kandang/coop tempat ayam dipelihara sebagai unit operasional utama dalam bisnis.

**Primary Key:** `id` (identifier unik setiap kandang)

**Foreign Key:** `pic_id` mereferensi ke users (siapa yang bertanggung jawab terhadap kandang ini)

**Kolom Penting:**
- `nama_kandang`: Identitas/nama kandang (contoh: "Kandang A1", "Kandang B2")
- `jumlah_ayam`: Baseline jumlah ayam dalam kandang, digunakan untuk kalkulasi HHP
- `status`: Status operasional (aktif/nonaktif)
- `keterangan`: Catatan/deskripsi kandang (lokasi, kapasitas, fasilitas)
- `pic_id`: Person in Charge yang bertanggung jawab mengelola kandang

**Relasi:**
- One-to-Many ke produksi_telur (satu kandang punya banyak record produksi harian)
- One-to-One logis ke stok_telur (meskipun tidak ada FK) - menyimpan current inventory
- Many-to-One dari users (pic_id) dengan SET NULL onDelete (jika supervisor dihapus, pic_id menjadi NULL)

**Karakteristik Khusus:** Central point untuk tracking semua aktivitas produksi per lokasi fisik. Kandang dapat diaktifkan/nonaktifkan tanpa menghapus data historis. Supervisor assignment melalui pic_id menggunakan `onDelete('set null')` - jika supervisor dihapus, pic_id menjadi NULL (accountability preserved). Timestamps auto-tracked untuk setiap perubahan.

---

## 3. **produksi_telur** (Transactional - Pencatatan Produksi)

**Fungsi Utama:** Mencatat data produksi telur harian per kandang beserta metrik kesehatan ayam untuk tracking performa dan compliance.

**Primary Key:** `id` (identifier unik setiap record produksi)

**Foreign Keys:**
- `kandang_id` → kandangs (produksi milik kandang mana)
- `user_id` → users (siapa yang input data ini)

**Kolom Penting:**
- `tanggal_produksi`: Tanggal pencatatan produksi
- `satuan_input`: Satuan awal input (butir atau kg)
- `jumlah_input`: Jumlah produksi dalam satuan input
- `jumlah_butir` & `jumlah_kg`: Hasil konversi ke kedua satuan (untuk konsistensi)
- `ayam_hidup`: Jumlah ayam yang masih hidup pada hari itu
- `ayam_mati`: Jumlah ayam yang mati pada hari itu
- `hdp` (Hen Day Production): Persentase produksi harian
- `hhp` (Hen House Production): Persentase kesehatan kandang
- `mortality`: Tingkat kematian ayam (%)
- `catatan`: Catatan sistem/log otomatis (e.g., "Input: 01 Jan 2026 16:30")
- `keterangan`: Catatan kesehatan/kondisi kandang manual (e.g., "Pemberian vitamin", "Vaksin ND")

**Relasi:**
- Many-to-One ke kandangs (banyak produksi belong to satu kandang)
- Many-to-One ke users (dicatat oleh satu user/pekerja)
- Impacts stok_telur (produksi ditambahkan ke perhitungan inventory)

**Karakteristik Khusus:** ⚠️ IMMUTABLE (tidak boleh diubah/dihapus setelah dibuat) untuk audit trail dan compliance. Menyimpan metrik kesehatan untuk analisis performa produksi. Menjadi dasar perhitungan stock yang akurat. Dual unit storage memastikan konsistensi. Dual catatan: `catatan` untuk log sistem, `keterangan` untuk info kesehatan kandang. Cascade delete jika kandang atau user dihapus.

---

## 4. **harga_telur** (Master Data - Pricing)

**Fungsi Utama:** Menyimpan daftar harga telur dengan versioning temporal dan lifecycle management untuk mendukung pricing strategy yang fleksibel.

**Primary Key:** `id` (identifier unik setiap price record)

**Foreign Key:** `user_id` → users (pemilik yang menetapkan harga)

**Kolom Penting:**
- `jenis_harga`: Kategori harga (kandang/grosir/konsumen) - untuk market differentiation
- `harga_per_kg`: Harga per kilogram
- `harga_per_butir`: Harga per butir (alternatif satuan, nullable)
- `tanggal_berlaku`: Tanggal efektif harga mulai berlaku
- `status`: Status harga (aktif/hangus - expired)
- `tanggal_akhir`: Tanggal akhir berlakunya harga (nullable, optional)
- `keterangan`: Catatan atau alasan perubahan harga

**Relasi:**
- One-to-Many ke detail_penjualan (satu harga bisa digunakan di banyak transaksi)
- Many-to-One ke users (price ditetapkan oleh satu pemilik)

**Karakteristik Khusus:** Lifecycle Management - harga lama otomatis di-mark sebagai "hangus" saat harga baru dibuat. Riwayat harga tetap tersimpan (tidak dihapus) untuk keperluan audit dan analisis historis. Mendukung multiple price types untuk segmentasi pasar berbeda. Temporal versioning memungkinkan scheduled price changes dan historical pricing queries.

---

## 5. **penjualan** (Transactional - Sales Header)

**Fungsi Utama:** Menyimpan header/master record setiap transaksi penjualan telur untuk tracking sales dan financial reporting.

**Primary Key:** `id` (identifier unik setiap penjualan)

**Foreign Key:** `user_id` → users (siapa yang membuat transaksi ini)

**Kolom Penting:**
- `tanggal_jual`: Tanggal transaksi penjualan
- `nama_pembeli`: Nama customer/pembeli
- `total_harga`: Total nilai transaksi (calculated dari detail items)
- `keterangan`: Catatan transaksi/metode pembayaran/notes

**Relasi:**
- One-to-Many ke detail_penjualan (satu penjualan contains banyak line items)
- Many-to-One ke users (dibuat oleh satu user)

**Karakteristik Khusus:** Master-Detail pattern - header penjualan bisa punya multiple items dengan harga berbeda. Cascade delete jika penjualan dihapus, semua detail otomatis terhapus. Total harga dihitung dari sum subtotal di detail_penjualan. Timestamps mencatat kapan transaksi dibuat dan diupdate untuk audit.

---

## 6. **detail_penjualan** (Transactional - Sales Line Items)

**Fungsi Utama:** Menyimpan detail item per transaksi penjualan dengan price snapshot untuk memastikan audit compliance dan akurasi historis.

**Primary Key:** `id` (identifier unik setiap line item)

**Foreign Keys:**
- `penjualan_id` → penjualan (item milik transaksi mana)
- `harga_telur_id` → harga_telur (reference ke price master)

**Kolom Penting:**
- `satuan_jual`: Satuan penjualan (butir/kg) - bisa berbeda per item
- `jumlah_jual`: Jumlah yang dijual dalam satuan
- `jumlah_butir` & `jumlah_kg`: Konversi ke kedua satuan (untuk konsistensi perhitungan)
- `harga_satuan`: SNAPSHOT harga utama saat transaksi terjadi (bukan lookup real-time)
- `harga_per_butir_saat_jual`: SNAPSHOT harga per butir pada saat transaksi
- `harga_per_kg_saat_jual`: SNAPSHOT harga per kg pada saat transaksi
- `subtotal`: Total item = jumlah × harga_satuan
- `jam_penjualan`: Waktu spesifik penjualan (untuk tracking detail)

**Relasi:**
- Many-to-One ke penjualan (banyak item dalam satu transaksi)
- Many-to-One ke harga_telur (reference, bukan untuk lookup price)
- Impacts stok_telur (sales removes stock)

**Karakteristik Khusus:** ⚠️ IMMUTABLE (tidak boleh diubah/dihapus) untuk compliance dan audit trail. Price Snapshot Pattern - menyimpan tiga varian harga (`harga_satuan`, `harga_per_butir_saat_jual`, `harga_per_kg_saat_jual`) pada saat transaksi, bukan ambil dari tabel harga saat query (prevents historical price tampering). Dual unit storage (butir dan kg) untuk akurasi perhitungan stock. Cascade delete jika penjualan dihapus. Kombinasi immutability + triple price snapshot + timestamp menciptakan audit trail yang ketat dan compliant.

---

## 7. **stok_telur** (Calculated Field - Inventory)

**Fungsi Utama:** Menyimpan current stock telur yang dihitung real-time untuk inventory management dan stock visibility.

**Foreign Key:** Tidak ada foreign key - tabel standalone yang hanya menyimpan aggregated stock values

**Kolom Penting:**
- `stok_butir`: Stok dalam satuan butir (tidak boleh negatif)
- `stok_kg`: Stok dalam satuan kilogram (tidak boleh negatif)
- `updated_at`: Waktu update terakhir stock

**Relasi:**
- ⚠️ **Logical relationship ke kandangs** (bukan FK) — Menyimpan current inventory (tanpa explicit FK reference)
- Calculated dari produksi_telur (production adds stock)
- Calculated dari detail_penjualan (sales removes stock)

**Karakteristik Khusus:** ⚠️ CALCULATED FIELD (bukan manual input/update). Formula: Σ(Production) - Σ(Sales). **CATATAN:** Tabel ini tidak memiliki kandang_id FK, sehingga hanya menyimpan agregat stock global, bukan per-kandang inventory. Diupdate oleh StockService setelah setiap transaksi. Tidak ada direct INSERT/UPDATE oleh user, hanya via service layer. ⏰ Hanya memiliki `updated_at`, tanpa `created_at`.

---

## 8. **pengaturan** (Configuration - System Settings)

**Fungsi Utama:** Menyimpan konfigurasi sistem dalam format key-value untuk flexibility dan centralized settings management.

**Primary Key:** `id` (identifier unik)

**Unique Constraint:** `kunci` (setiap config key harus unik)

**Kolom Penting:**
- `kunci`: Configuration key identifier (e.g., "konversi_butir_per_kg", "tax_rate")
- `nilai`: Configuration value (stored as string, tapi bisa multiple types)
- `tipe_data`: Tipe data value (string/integer/decimal/boolean)
- `keterangan`: Deskripsi/dokumentasi config ini untuk apa

**Relasi:** Tidak ada FK - isolated table

**Karakteristik Khusus:** Isolated Entity (tidak depend pada atau direferensi oleh tabel lain). Centralized configuration management (mudah change nilai tanpa deploy ulang) Contoh existing configs: konversi_butir_per_kg = "16" (conversion factor untuk normalizing satuan). Bisa extend dengan tax_rate, margin_target, dll tanpa perlu database migration.

---

## � Ringkasan Relasi Antar Tabel

**Matrix Relasi Utama:**
- users (1) → (N) kandangs (kandang_id) | User ditugaskan ke kandang (NULL ON DELETE)
- kandangs (N) ← (1) users (pic_id) | Kandang di-supervise oleh satu user (SET NULL)
- users (1) → (N) produksi_telur | User dapat input banyak record produksi
- kandangs (1) → (N) produksi_telur | Kandang punya banyak record produksi harian
- kandangs → stok_telur | Logical relationship (tanpa explicit FK)
- users (1) → (N) penjualan | User membuat banyak transaksi penjualan
- penjualan (1) → (N) detail_penjualan | Transaksi punya banyak line items
- harga_telur (1) → (N) detail_penjualan | Harga di-snapshot di banyak penjualan
- users (1) → (N) harga_telur | User menetapkan banyak harga

**Cascade Delete Behavior:**
- users → produksi_telur (CASCADE) — Jika user dihapus, pencatatannya terhapus
- users → penjualan (CASCADE) — Jika pemilik dihapus, transaksi terhapus
- users → harga_telur (CASCADE) — Jika pemilik dihapus, harga terhapus
- kandangs → produksi_telur (CASCADE) — Jika kandang dihapus, produksi terhapus
- penjualan → detail_penjualan (CASCADE) — Jika penjualan dihapus, detail terhapus
- harga_telur → detail_penjualan (NO ACTION) — Harga tidak bisa dihapus jika sudah di-snapshot
- users ← kandangs.kandang_id (NULL ON DELETE) — Jika kandang dihapus, users.kandang_id menjadi NULL
- kandangs ← users.pic_id (SET NULL) — Jika user dihapus, kandangs.pic_id menjadi NULL

---

## 🎯 Prinsip Desain Utama

**1. Normalization (3NF)** — Database design mengikuti normalisasi tingkat ketiga untuk menghilangkan redundansi data. Setiap tabel mempunyai primary key unik, tidak ada partial dependency dan transitive dependency antar tabel.

**2. Immutability & Audit Trail** — Tabel transaksional (produksi_telur, detail_penjualan) bersifat immutable. Record tidak boleh diubah atau dihapus setelah dibuat untuk menjaga integritas data historis dan compliance audit, serta mencegah manipulation retrospektif.

**3. Price Snapshot Pattern** — Harga dijadikan snapshot saat transaksi, bukan lookup real-time. detail_penjualan.harga_satuan menyimpan nilai saat transaksi untuk memastikan historical accuracy bahkan jika harga master berubah.

**4. Real-time Stock Calculation** — Inventory dihitung secara real-time dari formula: Σ(Production) - Σ(Sales). Tidak ada manual update atau intermediate caching. Diupdate oleh StockService setiap transaksi untuk mencegah stock inconsistency.

**5. Dual Unit Storage** — Semua measured fields disimpan dalam dua satuan (butir dan kg) untuk memastikan akurasi perhitungan, conversion, dan mendukung flexibility dalam sales unit selection.

**6. Cascade Delete** — Parent-child relationships menggunakan cascade delete untuk menjaga referential integrity. Exception: harga_telur tidak bisa dihapus jika sudah di-snapshot (NO ACTION) untuk data integrity.

**7. Lifecycle Management** — Master data seperti pricing memiliki lifecycle. Harga lama di-mark hangus (inactive), tidak dihapus. Riwayat tetap tersimpan untuk audit, analysis, dan historical reporting.

**8. Service Layer for Business Logic** — Complex calculations dioperasikan via service layer, bukan triggers. StockService menangani stock calculation, controllers memanggil services untuk business logic, centralized logic memudahkan testing dan maintenance.

---

**Dokumen ini adalah bagian dari Sistem Manajemen Produksi dan Penjualan Telur Hans Jaya Poultry**  
*Last Updated: April 16, 2026*
