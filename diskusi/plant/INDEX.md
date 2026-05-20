# PlantUML Activity Diagrams
## Hans Jaya Poultry Farm Management System

**Format:** `.puml` files untuk direct preview di PlantUML Editor

---

## 📋 Daftar Activity Diagram

| No | File | Deskripsi Singkat |
|:--:|------|-----------|
| 1 | `01-login.puml` | User input email & password → Sistem validasi → Tampilkan dashboard sesuai role (Pemilik/Pekerja). Jika gagal ada opsi retry. |
| 2 | `02-lihat-dashboard.puml` | User klik Dashboard → Sistem load data default (bulan ini) & query stok, penjualan, produksi → Render dashboard. User bisa filter periode untuk update data. |
| 3 | `03-kelola-harga-telur.puml` | Pemilik buka halaman harga → Bisa Tambah harga baru (auto-hangus harga lama), Edit harga aktif, Tandai harga hangus, atau Lihat history harga lengkap. |
| 4 | `04-input-produksi-telur.puml` | Pekerja input produksi: tanggal, satuan, jumlah telur, ayam hidup/mati → Sistem auto-konversi satuan & auto-calculate HDP/HHP/Mortality → Simpan & update stok. |
| 5 | `05-input-penjualan-telur.puml` | Pemilik klik tambah penjualan → Input tanggal/jam/pembeli → Loop tambah item (pilih harga, satuan, qty) → Validasi stok → Jika cukup: atomic transaction, kurangi stok, simpan, invoice. Jika tidak: warning. |
| 6 | `06-lihat-laporan-penjualan.puml` | Pemilik buka laporan penjualan → Load default (bulan ini) dengan summary, breakdown per harga, grafik → Bisa filter periode/kandang → Export PDF/Excel. |
| 7 | `07-lihat-laporan-produksi.puml` | Pemilik buka laporan produksi → Load default dengan summary total/rata-rata, tabel per hari, grafik trend → Filter periode & kandang → Export PDF/Excel. |
| 8 | `08-kelola-kandang.puml` | Pemilik buka kelola kandang → Daftar kandang → Bisa Tambah kandang baru (input nama, ayam, PIC), Edit data kandang, atau Hapus dengan cascade delete. |
| 9 | `09-lihat-stok-real-time.puml` | User buka halaman stok → Sistem kalkulasi real-time (Opening + Produksi - Penjualan) → Convert ke satuan Butir & KG → Display dengan last updated time. Auto-update saat ada input produksi/penjualan baru. |
| 10 | `10-kelola-user.puml` | Pemilik buka kelola user → Daftar user → Bisa Tambah user (input nama, email, password, role, kandang jika pekerja), Edit user, atau Hapus user dengan permission terkait. |
| 11 | `11-pengaturan-sistem.puml` | Pemilik buka pengaturan sistem → Query semua setting → Display dengan nilai & keterangan → Pilih setting untuk diubah → Input nilai baru → Validasi → Update DB. |
| 12 | `12-alur-keseluruhan-sistem.puml` | User login → Dashboard sesuai role. Pemilik akses: Kandang/Harga/Penjualan/Laporan/User/Setting/Profil. Pekerja akses: Produksi/Dashboard/Profil. Loop hingga logout. |

---

## 🚀 Cara Preview

### Online (no installation needed):
1. Kunjungi [PlantUML Online Editor](https://www.plantuml.com/plantuml/uml/)
2. Copy-paste isi file `.puml` ke editor
3. Diagram akan render otomatis

### Offline (dengan installation):
1. Install PlantUML: [Download](https://plantuml.com/download)
2. Install Graphviz: [Download](https://graphviz.org/download/)
3. Render dengan command:
   ```bash
   plantuml 01-login.puml
   ```

### VS Code Extension:
1. Install extension: `PlantUML` atau `Markdown Preview PlantUML`
2. Open `.puml` file
3. Preview akan ditampilkan otomatis

---

## � Detail Activity Diagram

---

### **4.1 Activity Diagram - LOGIN**

**Deskripsi**

Activity diagram login menggambarkan alur proses autentikasi pengguna untuk memasuki sistem. Proses dimulai dari pengguna membuka aplikasi dan memasukkan email/username serta password, kemudian sistem melakukan validasi kredensial terhadap database. Jika kredensial valid, sistem akan mengecek role pengguna dan mengarahkan ke dashboard yang sesuai dengan role tersebut (Pemilik atau Pekerja), namun jika data tidak valid, sistem menampilkan pesan error "Email atau password tidak sesuai" dan memberikan opsi kepada pengguna untuk melakukan retry atau keluar dari aplikasi.

**File:** `01-login.puml`

---

### **4.2 Activity Diagram - LIHAT DASHBOARD**

**Deskripsi**

Activity diagram dashboard menggambarkan alur pengguna melihat ringkasan data sistem dalam halaman overview yang berfungsi sebagai control center. Ketika pengguna memilih menu dashboard, sistem akan me-load data default untuk periode bulan ini dan melakukan query terhadap stok telur real-time, summary penjualan, dan ringkasan produksi per kandang, kemudian me-render dashboard dengan visualisasi data tersebut. Pengguna juga dapat memilih filter periode yang berbeda (harian, 7 hari, bulanan, semua) untuk memperbarui data dashboard sesuai kebutuhan analisis mereka, dengan pemilik mendapat akses penuh dan pekerja mendapat dashboard terbatas hanya untuk kandang yang mereka tangani.

**File:** `02-lihat-dashboard.puml`

---

### **4.3 Activity Diagram - KELOLA HARGA TELUR**

**Deskripsi**

Activity diagram kelola harga telur menggambarkan operasi CRUD lengkap untuk master data harga telur dengan fitur auto-hangus otomatis yang memastikan integritas data historis. Pemilik dapat membuka halaman harga untuk melihat daftar harga yang aktif dan hangus, kemudian melakukan berbagai aksi seperti menambah harga baru (sistem otomatis akan menandai harga lama jenis yang sama sebagai hangus), mengedit harga yang masih aktif, menandai harga sebagai hangus dengan tanggal akhir untuk referensi audit, atau melihat history lengkap dari semua perubahan harga untuk keperluan analisis trend harga dan verifikasi data snapshot saat penjualan.

**File:** `03-kelola-harga-telur.puml`

---

### **4.4 Activity Diagram - INPUT PRODUKSI TELUR**

**Deskripsi**

Activity diagram input produksi menggambarkan alur pekerja menginput data produksi harian termasuk tanggal, satuan (butir atau kg), jumlah telur yang diproduksi, jumlah ayam hidup, dan jumlah ayam mati (opsional). Sistem akan secara otomatis melakukan konversi antar satuan, auto-calculate metrics penting seperti HDP (Hen Day Production), HHP (Hen Housed Production), dan Mortality Rate untuk evaluasi kesehatan kandang, kemudian menyimpan data produksi ke dalam database dan secara real-time memperbarui stok telur berdasarkan data produksi yang baru diinput, memastikan keakuratan stok setiap saat.

**File:** `04-input-produksi-telur.puml`

---

### **4.5 Activity Diagram - INPUT PENJUALAN TELUR**

**Deskripsi**

Activity diagram input penjualan menggambarkan alur transaksi penjualan yang kompleks dengan multiple items dan validasi stok untuk menjamin konsistensi data. Pemilik membuka form penjualan dan input tanggal jual, jam jual, serta nama pembeli, kemudian melakukan loop untuk menambahkan item dengan memilih harga dari master, satuan jual (butir/kg), dan quantity. Sistem melakukan validasi stok total untuk memastikan ketersediaan telur cukup sebelum memproses transaksi, jika stok cukup maka sistem menggunakan atomic database transaction untuk mengurangi stok, menyimpan penjualan dan semua detail items, dan menampilkan invoice, namun jika stok tidak cukup sistem menampilkan warning dan memberikan opsi untuk mengurangi quantity atau membatalkan penjualan.

**File:** `05-input-penjualan-telur.puml`

---

### **4.6 Activity Diagram - LIHAT LAPORAN PENJUALAN**

**Deskripsi**

Activity diagram laporan penjualan menggambarkan alur pemilik untuk melihat laporan penjualan komprehensif dengan berbagai opsi filter dan export untuk keperluan analisis dan reporting. Pemilik membuka halaman laporan penjualan dan sistem akan me-load data default untuk periode bulan ini dengan menampilkan summary total penjualan, breakdown penjualan per jenis harga (kandang/grosir/konsumen), grafik trend revenue, serta tabel detail. Pemilik dapat mengubah filter berdasarkan periode dan kandang tertentu untuk melihat data yang lebih spesifik, kemudian dapat memilih untuk mengexport laporan dalam format PDF atau Excel sesuai kebutuhan untuk keperluan presentasi, analisa mendalam, atau sharing data dengan stakeholder lainnya.

**File:** `06-lihat-laporan-penjualan.puml`

---

### **4.7 Activity Diagram - LIHAT LAPORAN PRODUKSI**

**Deskripsi**

Activity diagram laporan produksi menggambarkan alur pemilik untuk melihat laporan produksi komprehensif dengan detail harian/bulanan dan visualisasi trend untuk evaluasi performa. Pemilik membuka halaman laporan produksi dan sistem akan me-load data default dengan menampilkan summary total dan rata-rata produksi, tabel produksi per hari dengan metrik HDP/HHP/Mortality, serta grafik trend produksi, HDP trend, HHP trend, dan mortality trend. Pemilik dapat menerapkan filter berdasarkan periode (harian/bulanan/3-bulan/6-bulan/semua) dan kandang tertentu untuk melakukan analisis performa yang lebih fokus dan mendalam, kemudian dapat mengexport laporan dalam format PDF atau Excel untuk keperluan dokumentasi, presentasi ke manajemen, atau sharing data dengan stakeholder lainnya.

**File:** `07-lihat-laporan-produksi.puml`

---

### **4.8 Activity Diagram - KELOLA KANDANG**

**Deskripsi**

Activity diagram kelola kandang menggambarkan operasi CRUD lengkap untuk master data kandang sebagai unit manajemen utama produksi telur dalam sistem. Pemilik dapat membuka halaman kelola kandang untuk melihat daftar semua kandang yang ada, kemudian melakukan berbagai aksi seperti menambah kandang baru dengan input nama, jumlah ayam, keterangan, dan penetapan pekerja sebagai PIC (Person In Charge), mengedit data kandang yang sudah ada dengan melakukan validasi data terlebih dahulu untuk memastikan konsistensi, atau menghapus kandang dengan sistem cascade delete yang memastikan integritas data terjaga dengan baik beserta riwayat produksi yang tetap tersimpan untuk referensi historical.

**File:** `08-kelola-kandang.puml`

---

### **4.9 Activity Diagram - LIHAT STOK REAL-TIME**

**Deskripsi**

Activity diagram stok real-time menggambarkan alur kalkulasi stok telur secara real-time menggunakan formula akurat: Stok = Opening Stok + Total Produksi - Total Penjualan. Ketika pengguna membuka dashboard atau halaman stok, sistem melakukan query terhadap opening stok periode sebelumnya, total produksi periode ini, dan total penjualan periode ini, kemudian melakukan perhitungan dan konversi ke satuan butir dan kg sesuai dengan ratio yang telah dikonfigurasi dalam pengaturan sistem. Sistem juga melakukan monitoring terhadap setiap input produksi atau penjualan baru dan secara otomatis melakukan recalculate serta update display stok secara real-time tanpa memerlukan refresh manual, memastikan stok selalu akurat dan mencegah overselling atau pemesanan yang tidak valid.

**File:** `09-lihat-stok-real-time.puml`

---

### **4.10 Activity Diagram - KELOLA USER**

**Deskripsi**

Activity diagram kelola user menggambarkan operasi CRUD untuk manajemen pengguna sistem dengan role-based access control yang ketat dan aman. Pemilik dapat membuka halaman kelola user untuk melihat daftar semua user yang terdaftar, kemudian melakukan berbagai aksi seperti menambah user baru dengan input nama, email, password, dan selection role spesifik (jika pekerja maka harus dipilih kandang yang akan dijaga), mengedit data user yang sudah ada dengan melakukan validasi email unique dan update role/kandang jika ada perubahan, atau menghapus user beserta permission terkait dengan sistem cascade delete untuk menjaga konsistensi data dan keamanan akses sistem.

**File:** `10-kelola-user.puml`

---

### **4.11 Activity Diagram - PENGATURAN SISTEM**

**Deskripsi**

Activity diagram pengaturan sistem menggambarkan alur pemilik untuk mengkonfigurasi setting sistem sesuai dengan kebutuhan bisnis spesifik peternakan mereka. Pemilik membuka halaman pengaturan sistem dan sistem akan melakukan query terhadap semua setting yang tersedia, kemudian menampilkan daftar lengkap setting dengan nilai terkini dan keterangan deskriptif untuk setiap setting yang dapat dikonfigurasi. Pemilik dapat memilih setting mana yang ingin diubah dan input nilai baru (misalnya mengubah ratio konversi butir ke kg untuk menyesuaikan dengan tipe telur yang diproduksi), kemudian sistem melakukan validasi nilai sebelum melakukan update ke dalam database dan menampilkan pesan sukses atau error sesuai dengan hasil validasi, memastikan konfigurasi sistem selalu valid dan sesuai standar bisnis.

**File:** `11-pengaturan-sistem.puml`

---

### **4.12 Activity Diagram - ALUR KESELURUHAN SISTEM**

**Deskripsi**

Activity diagram alur keseluruhan sistem menggambarkan perjalanan lengkap pengguna dari login hingga logout dengan menekankan perbedaan fitur dan akses berdasarkan role yang berbeda. User pertama kali membuka aplikasi dan melakukan proses login dengan memasukkan kredensial, kemudian sistem menampilkan dashboard sesuai dengan role pengguna tersebut (Pemilik atau Pekerja). Jika role adalah Pemilik maka pengguna memiliki akses penuh ke semua fitur sistem meliputi manajemen kandang, harga, input penjualan, lihat laporan (produksi/penjualan/stok), kelola user, pengaturan sistem, dan edit profil, namun jika role adalah Pekerja maka pengguna hanya dapat mengakses fitur terbatas yaitu input produksi harian, lihat dashboard terbatas (kandang mereka saja), dan edit profil pribadi, dengan proses berakhir ketika pengguna melakukan logout dari sistem.

**File:** `12-alur-keseluruhan-sistem.puml`

---

```
diskusi/
├── ACTIVITY_DIAGRAM.md          (Dokumentasi lengkap)
└── plant/                        (Folder PlantUML files)
    ├── 01-login.puml
    ├── 02-lihat-dashboard.puml
    ├── 03-kelola-harga-telur.puml
    ├── 04-input-produksi-telur.puml
    ├── 05-input-penjualan-telur.puml
    ├── 06-lihat-laporan-penjualan.puml
    ├── 07-lihat-laporan-produksi.puml
    ├── 08-kelola-kandang.puml
    ├── 09-lihat-stok-real-time.puml
    ├── 10-kelola-user.puml
    ├── 11-pengaturan-sistem.puml
    ├── 12-alur-keseluruhan-sistem.puml
    └── INDEX.md                  (File ini)
```

---

**Last Updated:** 11 April 2026  
**Format:** PlantUML Activity Diagrams  
**Capstone Project:** Hans Jaya Poultry Farm Management System