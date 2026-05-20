# Penjelasan Entity-Relationship Diagram (ERD) - Versi Paragraf
## Relasi Antar Tabel Database Hans Jaya Poultry

---

## 1. Tabel users (Pengguna Sistem)

Tabel users memiliki relasi dengan beberapa tabel lain dalam sistem:

a. **Terhubung ke tabel kandangs melalui kolom kandang_id** — Menunjukkan bahwa satu user (pekerja) dapat ditugaskan ke satu kandang tertentu. Ketika kandang dihapus, kandang_id user otomatis menjadi NULL (relasi many-to-one dengan nullOnDelete). Ini memungkinkan sistem melacak user mana yang bertanggung jawab di kandang mana, terutama untuk workers yang menginput data produksi.

b. **Terhubung ke tabel kandangs melalui kolom pic_id** — Menunjukkan bahwa satu user dapat menjadi supervisor (Person in Charge) untuk banyak kandang (relasi one-to-many). Ketika user dihapus, pic_id di kandang menjadi NULL (relasi dengan SET NULL). Ini menciptakan accountability chain yang jelas untuk setiap unit operasional.

c. **Terhubung ke tabel produksi_telur melalui kolom user_id** — Berarti satu user (pekerja) dapat melakukan pencatatan banyak record produksi (relasi one-to-many dengan cascadeOnDelete). Setiap pencatatan produksi harus diinput oleh satu user tertentu untuk tracking siapa yang mencatat data tersebut.

d. **Terhubung ke tabel penjualan melalui kolom user_id** — Satu user dapat membuat banyak transaksi penjualan (relasi one-to-many dengan cascadeOnDelete). Ketika user dihapus, semua penjualan yang dibuat user tersebut juga otomatis terhapus untuk menjaga referential integrity.

e. **Terhubung ke tabel harga_telur melalui kolom user_id** — Satu user (pemilik) dapat menetapkan harga untuk banyak jenis atau kategori telur (relasi one-to-many dengan cascadeOnDelete). User yang menghapus harga juga menghapus semua price records yang dibuat oleh user tersebut.

---

## 2. Tabel kandangs (Unit Operasional - Kandang)

Tabel kandangs memiliki banyak keterkaitan dengan tabel lain:

a. **Terhubung ke tabel users melalui kolom pic_id** — Setiap kandang memiliki satu supervior (Person in Charge) yang merupakan user tertentu. Ketika user tersebut dihapus, pic_id kandang menjadi NULL untuk mempertahankan record kandang (relasi dengan SET NULL). Ini memungkinkan tracking siapa yang bertanggung jawab mengelola setiap kandang operasional.

b. **Terhubung ke tabel produksi_telur melalui kolom kandang_id** — Satu kandang dapat memiliki banyak record produksi harian (relasi one-to-many dengan cascadeOnDelete). Setiap pencatatan produksi harus mencatat dari kandang mana, sehingga menciptakan audit trail yang jelas tentang produksi per lokasi fisik. Ketika kandang dihapus, semua produksi kandang tersebut otomatis terhapus.

c. **Terhubung secara logis ke tabel stok_telur** — Meskipun tidak ada foreign key eksplisit, setiap kandang secara konseptual memiliki satu current inventory record. Tabel stok_telur menyimpan agregat stock berdasarkan produksi dan penjualan dari kandang tersebut. Namun karena desain tabel stok_telur yang tidak memiliki kandang_id FK, ini adalah relasi logis bukan fisik di database.

---

## 3. Tabel produksi_telur (Pencatatan Produksi Harian)

Tabel produksi_telur menghubungkan dua entitas penting dalam sistem:

a. **Terhubung ke tabel kandangs melalui kolom kandang_id** — Menunjukkan bahwa setiap record produksi adalah dari satu kandang tertentu (relasi many-to-one dengan cascadeOnDelete). Satu kandang dapat memiliki banyak pencatatan produksi harian, menciptakan audit trail historis lengkap untuk setiap unit operasional. Ketika kandang dihapus, semua pencatatan produksi kandang tersebut juga dihapus.

b. **Terhubung ke tabel users melalui kolom user_id** — Setiap pencatatan produksi harus diinput oleh satu user (pekerja) tertentu (relasi many-to-one dengan cascadeOnDelete). Ini menciptakan trail tentang siapa dan kapan data produksi dicatat, penting untuk accountability dan validasi data. Ketika user dihapus, semua produksi yang diinput user tersebut juga terhapus.

c. **Mempengaruhi tabel stok_telur secara kalkulatif** — Data produksi digunakan untuk menghitung current inventory. Setiap kali ada pencatatan produksi baru, StockService otomatis menambahkan jumlah tersebut ke stok_telur. Meskipun tidak ada foreign key, relasi ini adalah yang terpenting karena semua stock calculation bergantung pada data produksi.

d. **Bersifat IMMUTABLE untuk audit trail** — Record produksi setelah dibuat tidak boleh diubah atau dihapus (kecuali via admin action khusus) untuk menjaga integritas audit. Ini memastikan bahwa historical production data tetap akurat dan tidak dapat dimanipulasi retrospektif, yang penting untuk compliance dan analisis trend.

---

## 4. Tabel harga_telur (Master Data Pricing)

Tabel harga_telur memiliki relasi khusus dalam sistem pricing:

a. **Terhubung ke tabel users melalui kolom user_id** — Satu user (pemilik) dapat menetapkan atau mengelola banyak harga untuk berbagai kategori (kandang, grosir, konsumen) (relasi one-to-many dengan cascadeOnDelete). Ketika pemilik dihapus, semua price records yang dibuat pemilik tersebut juga terhapus untuk menjaga konsistensi pricing history.

b. **Terhubung ke tabel detail_penjualan melalui kolom harga_telur_id** — Satu harga entry dapat digunakan di banyak transaksi penjualan (relasi one-to-many dengan NO ACTION pada delete). Harga tidak bisa dihapus jika sudah ada pencatatan penjualan yang mereferensi harga tersebut, menjaga integritas historical pricing data. Ketika transaksi penjualan dibuat, harga saat itu di-snapshot ke detail_penjualan (bukan lookup real-time).

c. **Memiliki lifecycle management dengan versioning temporal** — Sistem memungkinkan multiple versions dari harga yang sama dengan status aktif/hangus. Harga lama tidak dihapus tetapi di-mark sebagai "hangus" (inactive) ketika harga baru dibuat. Ini memastikan bahwa historical pricing tetap tersimpan untuk audit, analisis trend, dan validasi transaksi historis.

---

## 5. Tabel penjualan (Sales Header - Master Transaksi)

Tabel penjualan adalah central hub untuk semua transaksi penjualan:

a. **Terhubung ke tabel users melalui kolom user_id** — Satu user dapat membuat banyak transaksi penjualan (relasi one-to-many dengan cascadeOnDelete). Setiap penjualan harus mencatat siapa yang membuat transaksi tersebut, menciptakan audit trail tentang user yang membuat transaksi. Ketika user dihapus, semua penjualan yang dibuat user tersebut juga otomatis terhapus.

b. **Terhubung ke tabel detail_penjualan melalui kolom id (as penjualan_id)** — Satu transaksi penjualan (header) dapat terdiri dari banyak line items (relasi one-to-many dengan cascadeOnDelete). Master-Detail pattern ini memungkinkan fleksibilitas dimana satu penjualan dapat menjual berbagai jenis telur dengan harga berbeda dalam satu transaksi. Ketika penjualan (header) dihapus, semua detail items juga otomatis terhapus.

c. **Berfungsi sebagai aggregation point untuk total harga** — Kolom total_harga di penjualan dihitung dari sum subtotal di detail_penjualan. Tidak ada relasi foreign key untuk agregasi, tetapi relasi logis yang dikelola through aplikasi business logic dan service layer.

---

## 6. Tabel detail_penjualan (Sales Line Items - Detail Penjualan)

Tabel detail_penjualan menghubungkan berbagai entitas dalam konteks transaksi:

a. **Terhubung ke tabel penjualan melalui kolom penjualan_id** — Banyak line items dapat milik satu transaksi penjualan (relasi many-to-one dengan cascadeOnDelete). Setiap item dalam penjualan harus mencatat tangal, waktu, jumlah, dan harga yang tepat pada saat transaksi terjadi. Ketika penjualan (header) dihapus, semua detail items juga terhapus otomatis.

b. **Terhubung ke tabel harga_telur melalui kolom harga_telur_id** — Setiap item penjualan mereferensi satu harga entry dari tabel harga_telur (relasi many-to-one dengan NO ACTION). Namun referensi ini bukan untuk lookup harga dinamis, tetapi hanya untuk traceability. Harga aktual disimpan di kolom harga_satuan sebagai snapshot dari harga pada saat transaksi.

c. **Mengimplementasikan Price Snapshot Pattern** — Meskipun detail_penjualan mereferensi harga_telur, tiga kolom harga disimpan sebagai snapshot pada saat transaksi: `harga_satuan` (harga utama), `harga_per_butir_saat_jual`, dan `harga_per_kg_saat_jual`. Ini memastikan bahwa jika harga master berubah, transaksi historis tetap menunjukkan semua varian harga yang berlaku saat itu. Tidak ada lookup real-time dari tabel harga. Pattern ini essential untuk compliance, auditing, dan financial accuracy.

d. **Mempengaruhi tabel stok_telur secara kalkulatif** — Setiap pencatatan penjualan (detail items) mengurangi dari stok yang tersedia. StockService otomatis mengurangi stok berdasarkan jumlah_butir atau jumlah_kg dari detail_penjualan. Meskipun tidak ada foreign key eksplisit ke stok_telur, relasi ini sangat penting karena inventory accuracy bergantung pada data penjualan yang akurat.

e. **Bersifat IMMUTABLE untuk audit trail dan compliance** — Record detail penjualan setelah dibuat tidak boleh diubah atau dihapus untuk menjaga integritas transaksi. Kombinasi dari immutability + price snapshot + timestamps menciptakan audit trail yang ketat dan compliant untuk needs regulasi dan financial reporting.

---

## 7. Tabel stok_telur (Inventory - Calculated Field)

Tabel stok_telur memiliki peran unik sebagai calculated field dalam sistem:

a. **Tidak memiliki foreign key eksplisit** — Tabel stok_telur adalah standalone table yang tidak memiliki kandang_id atau referensi APapun ke tabel lain. Ini adalah design decision yang berarti stok_telur menyimpan agregat inventory global, bukan per-kandang inventory. Relasi dengan tabel lain adalah relasi logis yang diimplementasikan melalui service layer, bukan melalui database constraints.

b. **Dipengaruhi oleh tabel produksi_telur secara kalkulatif (one-to-many logis)** — Setiap pencatatan produksi di tabel produksi_telur menambah stok_telur. Formula perhitungan: stok_butir = Σ(produksi_telur.jumlah_butir) - Σ(detail_penjualan.jumlah_butir). Relasi ini dikelola melalui StockService yang dijalankan setiap kali ada pencatatan produksi baru atau status produksi berubah.

c. **Dipengaruhi oleh tabel detail_penjualan secara kalkulatif (many-to-many logis)** — Setiap pencatatan penjualan (detail items) mengurangi dari stok yang tersedia. Ketika detail penjualan dibuat, StockService otomatis mengurangi stok berdasarkan jumlah yang dijual. Jika penjualan dihapus (cascade delete), stok otomatis recalculated dan ditambah kembali.

d. **Implements real-time calculation pattern** — Stok_telur bukan static snapshot tetapi dihitung ulang setiap kali ada transaksi. Tidak ada intermediate caching atau manual update. StockService menangani semua perhitungan, menjamin bahwa stock selalu akurat dan mencerminkan current state dari produksi dan penjualan. Pattern ini mencegah stock inconsistency yang fatal untuk inventory management.

e. **Timestamps khusus - hanya updated_at, tanpa created_at** — Tabel ini hanya memiliki updated_at untuk tracking kapan stock terakhir diupdate. Tidak ada created_at karena stok bukan event yang diciptakan, tetapi field yang terus berubah. Design ini menunjukkan bahwa stok_telur adalah continuously-updated calculated field, bukan traditional transaction record.

---

## 8. Tabel pengaturan (Configuration - System Settings)

Tabel pengaturan memiliki peran isolated sebagai configuration store:

a. **Tidak memiliki foreign key** — Tabel pengaturan adalah completely isolated entity yang tidak memiliki relasi dengan tabel lain. Ini adalah standalone configuration store yang menyimpan key-value pairs untuk system-wide settings dan parameters. Design ini memungkinkan konfigurasi diubah tanpa affecting relational integrity atau memerlukan schema changes.

b. **Berfungsi sebagai centralized configuration management** — Semua system-wide settings seperti konversi_butir_per_kg, tax_rate, margin_target, dsb disimpan di tabel ini. Ketika value perlu diubah, admin hanya perlu update value kolom ini tanpa perlu redeploy application atau migrate database. Ini memberikan flexibility dan agility untuk business rule changes.

c. **Diimplementasikan sebagai key-value store dengan type hint** — Kolom kunci adalah unique key identifier, nilai adalah string representation, dan tipe_data memberikan hint tentang bagaimana parse value tersebut (string/integer/decimal/boolean). Pattern ini memungkinkan extension dengan config baru tanpa schema migration.

d. **Timestamps simplified - hanya updated_at** — Tabel ini hanya memiliki updated_at tanpa created_at, menunjukkan bahwa konfigurasi adalah continuously-updated settings, bukan event records. Tidak ada audit trail created_at diperlukan karena config history tidak ditrack (hanya current value).

---

## 📊 Ringkasan Pola Relasi Antar Tabel

**Pola One-to-Many yang Dominan:**
Mayoritas relasi dalam sistem adalah one-to-many, mencerminkan bahwa:
- Satu user dapat membuat/input/manage banyak records (produksi, penjualan, harga)
- Satu kandang dapat memiliki banyak pencatatan produksi harian
- Satu penjualan (header) dapat memiliki banyak line items (detail)
- Satu harga dapat digunakan di banyak transaksi penjualan

**Pola Many-to-One yang Melacak Ownership:**
Sebaliknya dari perspektif child tables:
- Banyak produksi milik satu kandang
- Banyak detail penjualan milik satu penjualan
- Banyak penjualan dibuat oleh satu user

**Pola Calculated/Logical Relationships:**
Beberapa relasi bukan foreign key fisik tetapi logical relationships yang dikelola oleh service layer:
- stok_telur dipengaruhi oleh produksi_telur dan detail_penjualan secara kalkulatif
- kandidang memiliki relasi logis ke stok_telur meskipun tidak ada FK

**Pola Delete Behavior yang Variatif:**
Sistem menggunakan 4 tipe delete behavior yang berbeda:
- CASCADE: child records dihapus otomatis saat parent dihapus (produksi, penjualan, harga)
- SET NULL: FK menjadi NULL saat parent dihapus (users.kandang_id, kandangs.pic_id)
- NO ACTION: parent tidak bisa dihapus jika ada child (harga_telur tidak bisa dihapus jika di-snapshot)
- Logical delete: record tidak dihapus, hanya di-mark sebagai inactive (harga status='hangus')

**Pola Immutability untuk Audit:**
Tabel transaksional (produksi_telur, detail_penjualan) bersifat immutable after creation untuk menjaga integritas historical data dan compliance dengan requirement audit.

**Pola Price Snapshot untuk Historical Accuracy:**
Detail penjualan menyimpan price snapshot, bukan live reference ke tabel harga, memastikan bahwa harga historis tetap akurat bahkan jika master price berubah.

---

**Dokumen ini adalah bagian dari Sistem Manajemen Produksi dan Penjualan Telur Hans Jaya Poultry**  
*Format: Narrative/Paragraf Penjelasan Relasi*  
*Last Updated: April 21, 2026*
