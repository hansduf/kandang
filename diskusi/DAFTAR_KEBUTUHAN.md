# DAFTAR KEBUTUHAN SISTEM
## Sistem Manajemen & Monitoring Produksi Penjualan Telur Ayam Petelur

---

## 5.1 Kebutuhan Fungsional Sistem
k### A. AUTHENTICATION & AUTHORIZATION

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 1 | User harus login terlebih dahulu untuk dapat mengakses sistem | Pekerja Kandang, Pemilik Peternakan |
| 2 | Sistem menggunakan role-based access control (RBAC) dengan 2 role utama: Pekerja dan Pemilik | Pekerja Kandang, Pemilik Peternakan |
| 3 | User dapat logout dari sistem | Pekerja Kandang, Pemilik Peternakan |
| 4 | Sistem menampilkan menu dan dashboard sesuai role yang login | Pekerja Kandang, Pemilik Peternakan |

---

### B. PROFILE & AKUN

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 5 | User dapat menampilkan data profil pribadi | Pekerja Kandang, Pemilik Peternakan |
| 6 | User dapat mengubah data profil pribadi (nama, email, password) | Pekerja Kandang, Pemilik Peternakan |
| 7 | Pemilik peternakan dapat mengelola data pengguna sistem (tambah/edit/hapus) | Pemilik Peternakan |
| 8 | Sistem dapat mengassign pekerja kandang ke kandang tertentu saat pembuatan user | Pemilik Peternakan |

---

### C. DASHBOARD

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 9 | Pekerja kandang dapat melihat dashboard kandang yang menjadi tanggung jawabnya | Pekerja Kandang |
| 10 | Dashboard pekerja menampilkan informasi: nama kandang, stok telur, riwayat produksi 7 hari | Pekerja Kandang |
| 11 | Pemilik peternakan dapat melihat dashboard lengkap dengan ringkasan produksi SEMUA kandang | Pemilik Peternakan |
| 12 | Dashboard pemilik menampilkan: total produksi hari ini, status stok, grafik performa, notifikasi | Pemilik Peternakan |

---

### D. KANDANG MANAGEMENT (PEMILIK ONLY)

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 13 | Pemilik peternakan dapat menambah data kandang baru | Pemilik Peternakan |
| 14 | Pemilik peternakan dapat melihat daftar lengkap semua kandang (aktif dan nonaktif) | Pemilik Peternakan |
| 15 | Pemilik peternakan dapat melihat detail kandang termasuk: nama, jumlah ayam, status, PIC | Pemilik Peternakan |
| 16 | Pemilik peternakan dapat mengubah data kandang (nama, jumlah ayam, keterangan, status) | Pemilik Peternakan |
| 17 | Pemilik peternakan dapat mengassign Person In Charge (PIC) ke setiap kandang | Pemilik Peternakan |
| 18 | Pemilik peternakan dapat menghapus data kandang (dengan cascade delete ke produksi terkait) | Pemilik Peternakan |

---

### E. PRODUKSI TELUR MANAGEMENT

#### E1. Input Produksi (PEKERJA ONLY)

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 19 | Pekerja kandang dapat menginput data produksi telur harian dengan satuan butir atau kilogram | Pekerja Kandang |
| 20 | Input produksi harus mencakup: tanggal, satuan input, jumlah, ayam hidup, ayam mati, catatan | Pekerja Kandang |
| 21 | Sistem melakukan konversi otomatis antara satuan butir dan kilogram sesuai rasio di pengaturan | Pekerja Kandang |
| 22 | Sistem secara otomatis menambah stok telur saat produksi berhasil disimpan | Pekerja Kandang |
| 23 | Pekerja dapat melihat data kandang yang ditugaskan dan ayam hidup saat ini (ayam awal - ayam mati kumulatif) | Pekerja Kandang |

#### E2. Production Metrics

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 24 | Sistem secara otomatis menghitung HDP (Henday Production) = (telur/ayam hidup) × 100 | Pekerja Kandang |
| 25 | Sistem secara otomatis menghitung HHP = (telur/jumlah ayam awal) × 100 | Pekerja Kandang |
| 26 | Sistem secara otomatis menghitung Mortality = (ayam mati/jumlah ayam awal) × 100 | Pekerja Kandang |
| 27 | Metrics (HDP, HHP, Mortality) disimpan untuk setiap record produksi untuk analisis historis | Pekerja Kandang, Pemilik Peternakan |

#### E3. Riwayat Produksi (READ/EDIT/DELETE)

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 28 | Pekerja kandang dapat melihat riwayat produksi kandang miliknya (paginated, sorted DESC) | Pekerja Kandang |
| 29 | Pemilik peternakan dapat melihat riwayat produksi SEMUA kandang | Pemilik Peternakan |
| 30 | Pemilik peternakan dapat mengubah data produksi yang sudah diinput (jika ada kesalahan) | Pemilik Peternakan |
| 31 | Pemilik peternakan dapat menghapus data produksi (dengan trigger stok recalculation) | Pemilik Peternakan |

---

### F. STOK TELUR MANAGEMENT

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 32 | Pekerja kandang dan pemilik peternakan dapat melihat informasi stok telur real-time | Pekerja Kandang, Pemilik Peternakan |
| 33 | Stok telur disimpan dalam 2 satuan: butir (integer) dan kg (decimal) | Pekerja Kandang, Pemilik Peternakan |
| 34 | Sistem menggunakan single aggregated stok record (bukan per kandang) | Pemilik Peternakan |
| 35 | Stok diupdate secara real-time: tambah saat produksi input, kurang saat penjualan | Pemilik Peternakan |
| 36 | Stok di-recalculate otomatis jika ada edit/delete produksi atau penjualan | Pemilik Peternakan |

---

### G. HARGA TELUR MANAGEMENT

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 37 | Pemilik peternakan dapat menambah data harga telur baru berdasarkan harga pasar | Pemilik Peternakan |
| 38 | Sistem mendukung 3 jenis harga simultan: kandang, grosir, konsumen | Pemilik Peternakan |
| 39 | Setiap harga mencakup: harga_per_kg dan harga_per_butir | Pemilik Peternakan |
| 40 | Sistem menyimpan riwayat perubahan harga secara historis TANPA menghapus data lama | Pemilik Peternakan |
| 41 | Pemilik peternakan dapat melihat riwayat lengkap semua perubahan harga | Pemilik Peternakan |
| 42 | Harga memiliki status: aktif atau hangus untuk version control | Pemilik Peternakan |
| 43 | Harga memiliki tanggal_berlaku dan tanggal_akhir untuk time-based validation | Pemilik Peternakan |
| 44 | Sistem automatisly menampilkan hanya harga yang aktif saat ini saat pemilik membuat penjualan | Pemilik Peternakan |

---

### H. PENJUALAN TELUR

#### H1. Input Penjualan

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 45 | Pemilik peternakan dapat menginput transaksi penjualan telur | Pemilik Peternakan |
| 46 | Satu transaksi penjualan dapat memiliki MULTIPLE items (tidak hanya satu) | Pemilik Peternakan |
| 47 | Input penjualan harus mencakup: tanggal_jual, jam_jual, nama_pembeli | Pemilik Peternakan |
| 48 | Untuk setiap item dalam penjualan: pilih harga, satuan_jual, input jumlah | Pemilik Peternakan |
| 49 | Sistem melakukan konversi otomatis satuan untuk setiap item penjualan | Pemilik Peternakan |
| 50 | Sistem secara otomatis menghitung subtotal per item (jumlah × harga_satuan) | Pemilik Peternakan |
| 51 | Sistem secara otomatis menghitung total_harga transaksi (sum semua subtotal) | Pemilik Peternakan |

#### H2. Stock Validation & Processing

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 52 | Sistem secara otomatis melakukan validasi ketersediaan stok SEBELUM penjualan diproses | Pemilik Peternakan |
| 53 | Jika stok tidak cukup, sistem menampilkan warning dengan detail stok tersedia vs diminta | Pemilik Peternakan |
| 54 | Penjualan hanya dapat disimpan jika stok mencukupi (transaction rollback jika gagal) | Pemilik Peternakan |
| 55 | Sistem menggunakan database transaction untuk memastikan atomicity (all or nothing) | Pemilik Peternakan |
| 56 | Sistem secara otomatis mengurangi stok telur saat transaksi penjualan berhasil | Pemilik Peternakan |

#### H3. Price Snapshot (Historical Accuracy)

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 57 | Sistem menyimpan snapshot harga saat penjualan (harga_per_butir_saat_jual, harga_per_kg_saat_jual) | Pemilik Peternakan |
| 58 | Jika harga berubah esok hari, transaksi kemarin tetap menggunakan harga kemarin | Pemilik Peternakan |
| 59 | Jam penjualan disimpan per detail item (bisa berbeda untuk setiap item dalam transaksi) | Pemilik Peternakan |

#### H4. View Penjualan

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 60 | Pemilik peternakan dapat melihat list semua transaksi penjualan | Pemilik Peternakan |
| 61 | Detail penjualan menampilkan: tanggal, pembeli, items dengan detail, total harga | Pemilik Peternakan |
| 62 | List penjualan dipaginate (50 per halaman) dan sorted by tanggal DESC | Pemilik Peternakan |

---

### I. LAPORAN & ANALISIS

#### I1. Laporan Produksi

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 63 | Pemilik peternakan dapat melihat laporan produksi dalam bentuk tabel | Pemilik Peternakan |
| 64 | Pemilik peternakan dapat melihat laporan produksi dalam bentuk grafik (line/bar/pie) | Pemilik Peternakan |
| 65 | Laporan produksi dapat difilter berdasarkan periode: harian, mingguan, bulanan | Pemilik Peternakan |
| 66 | Laporan produksi dapat difilter per kandang | Pemilik Peternakan |
| 67 | Laporan produksi menampilkan: tanggal, kandang, jumlah (butir/kg), metrics (HDP/HHP/Mortality) | Pemilik Peternakan |
| 68 | Pemilik peternakan dapat mengekspor laporan produksi ke file PDF | Pemilik Peternakan |
| 69 | Pemilik peternakan dapat mengekspor laporan produksi ke file Excel | Pemilik Peternakan |

#### I2. Laporan Penjualan

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 70 | Pemilik peternakan dapat melihat laporan penjualan dalam bentuk tabel | Pemilik Peternakan |
| 71 | Pemilik peternakan dapat melihat laporan penjualan dalam bentuk grafik (line/bar/pie) | Pemilik Peternakan |
| 72 | Laporan penjualan dapat difilter berdasarkan periode waktu | Pemilik Peternakan |
| 73 | Laporan penjualan dapat difilter per jenis harga (kandang/grosir/konsumen) | Pemilik Peternakan |
| 74 | Laporan penjualan menampilkan: tanggal, pembeli, items, subtotal, total revenue | Pemilik Peternakan |
| 75 | Laporan penjualan menampilkan detail items (jumlah, satuan, harga saat jual) | Pemilik Peternakan |
| 76 | Laporan penjualan dapat melihat profit/margin per transaksi (jika applicable) | Pemilik Peternakan |
| 77 | Pemilik peternakan dapat mengekspor laporan penjualan ke file PDF | Pemilik Peternakan |
| 78 | Pemilik peternakan dapat mengekspor laporan penjualan ke file Excel | Pemilik Peternakan |

---

### J. PENGATURAN SISTEM

| No | Kebutuhan | Aktor |
|:--:|-----------|-------|
| 79 | Pemilik peternakan dapat mengatur rasio konversi satuan telur (butir ke kilogram) | Pemilik Peternakan |
| 80 | Konversi ratio disimpan dalam tabel pengaturan dengan key-value structure | Pemilik Peternakan |
| 81 | Konversi ratio digunakan untuk semua kalkulasi otomatis di sistem | Pemilik Peternakan |
| 82 | Pemilik peternakan dapat mengatur konfigurasi sistem lainnya (via pengaturan table) | Pemilik Peternakan |

---

## 5.2 Kebutuhan Non-Fungsional

### A. PERFORMANCE & SCALABILITY

| No | Kebutuhan | Prioritas |
|:--:|-----------|-----------|
| 83 | Sistem dapat menangani 100+ kandang tanpa penurunan performa | Medium |
| 84 | Laporan dapat diload dalam waktu < 5 detik untuk periode bulanan | Medium |
| 85 | Query produksi/penjualan menggunakan pagination untuk menghindari memory overload | High |
| 86 | API endpoints untuk stok real-time harus merespons dalam < 500ms | High |

### B. DATA INTEGRITY & CONSISTENCY

| No | Kebutuhan | Prioritas |
|:--:|-----------|-----------|
| 87 | Semua operasi inventory (produksi, penjualan, edit, delete) menggunakan database transactions | High |
| 88 | Stok tidak boleh pernah negatif | High |
| 89 | Harga historis tidak boleh dihapus untuk audit trail | High |
| 90 | Cascade delete untuk kandang → produksi terkait harus konsisten | High |

### C. SECURITY & AUTHORIZATION

| No | Kebutuhan | Prioritas |
|:--:|-----------|-----------|
| 91 | Password disimpan dengan hashing (bcrypt) | High |
| 92 | Session timeout otomatis untuk idle users | Medium |
| 93 | Role-based middleware untuk melindungi routes sesuai permission | High |
| 94 | Query scope otomatis untuk memastikan pekerja hanya akses kandang sendiri | High |

### D. DATA VALIDATION

| No | Kebutuhan | Prioritas |
|:--:|-----------|-----------|
| 95 | Input numerik harus valid dan dalam range yang sesuai | High |
| 96 | Tanggal input tidak boleh melebihi tanggal hari ini | Medium |
| 97 | Email harus unik per user | High |
| 98 | Validasi satuan input hanya butir atau kg | High |

### E. USER EXPERIENCE

| No | Kebutuhan | Prioritas |
|:--:|-----------|-----------|
| 99 | Interface responsive untuk desktop dan tablet | Medium |
| 100 | Error messages yang informatif dan user-friendly | Medium |
| 101 | Success messages untuk setiap operasi berhasil | Low |
| 102 | Form harus pre-fill data kandang/harga untuk mempercepat input | Low |

### F. MAINTAINABILITY & DOCUMENTATION

| No | Kebutuhan | Prioritas |
|:--:|-----------|-----------|
| 103 | Kode harus mengikuti Laravel best practices dan conventions | High |
| 104 | Model menggunakan relationship & scopes untuk query yang clean | High |
| 105 | Controller action fokus pada business logic, view logic di blade | Medium |
| 106 | Database schema harus di-dokumentasikan dengan ERD | Medium |

---

## 5.3 FITUR FUTURE (NOT IN CURRENT SCOPE)

| No | Fitur | Keterangan |
|:--:|-------|-----------|
| 1 | Approval workflow untuk penjualan | Pemilik approval sebelum stok decrement |
| 2 | Audit log/activity tracking | Track siapa mengubah apa dan kapan |
| 3 | SMS/Email notification | Alert untuk stok menipis atau penjualan |
| 4 | Supplier/Customer master | Database pembeli tetap dan supplier |
| 5 | Per-kandang stock tracking | Stok breakdown per kandang, bukan global |
| 6 | Multi-user pemilik (co-owner) | Share access untuk pemilik bersama |
| 7 | Supervisor/Manager role | Role tambahan di atas pekerja |
| 8 | Finance/Accounting reports | Laporan cost/profit dengan detail finansial |
| 9 | Predictive analytics | Forecast produksi berdasarkan historical data |
| 10 | Mobile app | Native atau progressive web app |

---

**TOTAL KEBUTUHAN FUNGSIONAL**: 82 kebutuhan  
**TOTAL KEBUTUHAN NON-FUNGSIONAL**: 20 kebutuhan  
**TOTAL KEBUTUHAN FUTURE**: 10 fitur  

**STATUS DOKUMENTASI**: Sesuai dengan implementasi aktual (code review dari migrations, models, controllers, routes)

