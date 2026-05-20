# Deskripsi Perancangan Halaman - Versi Implementasi
## Hans Jaya Poultry Management System

---

## **PUBLIC & AUTHENTICATION PAGES**

### Halaman Welcome (Landing Page)
Halaman ini dapat diakses oleh semua user sebelum maupun sesudah login ke dalam sistem. Halaman ini menampilkan informasi umum mengenai Hans Jaya Poultry dan sistem manajemen dengan headline "Kelola Peternakan Ayam Anda dengan Mudah", deskripsi singkat tentang sistem manajemen terintegrasi untuk monitoring produksi dan inventory, logo perusahaan (🐔), dan tombol aksi yang disesuaikan (Akses Dashboard untuk user login, atau Mulai Sekarang untuk user belum login), serta footer dengan informasi copyright.

### Halaman Login
Halaman ini dapat diakses oleh user sebelum melakukan login ke dalam sistem. Halaman ini menampilkan formulir login dengan field email/username dan password, checkbox remember me, tombol login, serta menampilkan akun demo untuk testing dengan credential pemilik, kandang1, kandang2, kandang3 untuk kemudahan trial system oleh user baru.

### Halaman Register
Halaman ini dapat diakses oleh user sebelum melakukan login ke dalam sistem untuk membuat akun baru. Halaman ini menampilkan formulir registrasi dengan field input nama lengkap, email, password, dan konfirmasi password, dengan link untuk user yang sudah punya akun untuk kembali ke halaman login.

### Halaman Forgot Password
Halaman ini dapat diakses oleh user yang lupa password mereka dan perlu melakukan reset password. Halaman ini menampilkan formulir dengan field email saja, dengan instruksi bahwa sistem akan mengirimkan link reset password ke email yang terdaftar untuk memungkinkan user memilih password baru.

### Halaman Reset Password
Halaman ini dapat diakses oleh user setelah mengklik link reset password dari email. Halaman ini menampilkan formulir dengan field email (pre-filled), password baru, dan konfirmasi password baru, dengan hidden token untuk validasi keamanan reset password.

### Halaman Confirm Password
Halaman ini dapat diakses oleh user yang sudah login ketika akan mengakses operasi sensitif. Halaman ini menampilkan instruksi keamanan dan formulir dengan field password saja untuk verifikasi identitas, dengan tombol confirm untuk melanjutkan ke operasi sensitif yang diminta.

### Halaman Verify Email
Halaman ini dapat diakses oleh user yang baru register dan harus memverifikasi email mereka sebelum menggunakan sistem. Halaman ini menampilkan instruksi untuk check email dan klik link verifikasi, tombol resend verification link jika user tidak menerima email, dan tombol logout untuk kembali ke login page.

---

## **DASHBOARD & OVERVIEW**

### Halaman Dashboard (Pemilik)
Halaman ini diakses oleh pemilik setelah berhasil login ke dalam sistem. Halaman ini menampilkan ringkasan lengkap status produksi dan penjualan telur dengan filter periode fleksibel, kartu-kartu informasi utama berisi metrik penting (total stok telur, jumlah ayam hidup, tingkat kematian, total produksi, total penjualan, jumlah kandang aktif, dan status sistem), serta grafik visual trend produksi dari semua kandang dalam periode yang dipilih dan tombol navigasi cepat menuju modul-modul utama lainnya.

### Halaman Dashboard (Pekerja)
Halaman ini diakses oleh pekerja setelah berhasil login ke dalam sistem. Halaman ini menampilkan data kandang yang ditugaskan kepada pekerja saja, berisi informasi kandang yang sedang dikelola (nama, supervisor, kapasitas), serta metrik produksi untuk kandang tersebut dalam periode yang dapat difilter (7 hari, bulanan, atau semua waktu), memberikan focus sesuai tanggung jawab pekerja tanpa menampilkan data kandang lain.

---

## **MANAJEMEN KANDANG (PEMILIK)**

### Halaman Data Kandang (Lihat)
Halaman ini diakses oleh pemilik untuk mengelola unit operasional kandang. Halaman ini menampilkan overview lengkap tentang kondisi setiap kandang dengan informasi kapasitas ayam, jumlah ayam aktual, tingkat kematian, total produksi, dan metrik produktivitas dalam periode tertentu, serta statistik ringkasan seperti total kandang, total kapasitas, total ayam hidup, dan rata-rata produktivitas, dengan tombol aksi untuk menambah, mengubah, atau menghapus kandang.

### Halaman Tambah Kandang & Edit Kandang
Halaman ini diakses oleh pemilik untuk menambah atau mengubah data kandang. Halaman ini menampilkan formulir dengan field input nama kandang, jumlah kapasitas ayam, penugasan supervisor (PIC), dan keterangan tambahan, dengan validasi input untuk memastikan data konsisten dan memungkinkan pemilik mengelola struktur operasional kandang sesuai kebutuhan bisnis.

---

## **MANAJEMEN HARGA TELUR (PEMILIK)**

### Halaman Manajemen Harga (Lihat)
Halaman ini diakses oleh pemilik untuk mengelola harga produk telur. Halaman ini menampilkan master pricing dengan daftar harga yang sedang berlaku dan riwayat harga lama, dilengkapi grafik visual trend harga historis dengan filter bulan untuk analisis pola pricing, serta tombol aksi untuk menambah harga baru, mengubah harga aktif, dan melihat kapan harga dimulai dan berakhir.

### Halaman Input Harga Baru & Edit Harga
Halaman ini diakses oleh pemilik untuk menambah atau mengubah data harga. Halaman ini menampilkan formulir dengan field input jenis harga (kandang/grosir/konsumen), tanggal berlaku, harga per kg, harga per butir, dan keterangan, dengan sistem yang mendukung versioning temporal untuk tracking price changes dan validasi untuk memastikan historical pricing data tetap valid untuk compliance dan financial reporting.

---

## **MANAJEMEN PENJUALAN (PEMILIK)**

### Halaman Data Penjualan (Lihat)
Halaman ini diakses oleh pemilik untuk melihat dan mengelola transaksi penjualan telur. Halaman ini menampilkan tabel penjualan dengan detail penting seperti tanggal jual, nama pembeli, user pencatat, jenis harga yang digunakan, jumlah telur terjual (butir dan kg), jumlah item dalam transaksi, dan nilai total setiap penjualan, serta filter periode, pagination, dan tombol aksi untuk melihat detail, mengubah transaksi, atau menghapus transaksi.

### Halaman Tambah Penjualan & Edit Penjualan
Halaman ini diakses oleh pemilik untuk membuat atau memodifikasi transaksi penjualan. Halaman ini menampilkan formulir dengan input header (tanggal, jam, nama pembeli) dan tabel line items dinamis yang menampilkan jenis harga, kuantitas, dan harga per unit, dengan menampilkan stok tersedia real-time dan validasi untuk memastikan penjualan tidak melebihi stok, serta auto-calculation untuk total harga.

### Halaman Detail Penjualan
Halaman ini diakses oleh pemilik untuk melihat detail transaksi penjualan tertentu. Halaman ini menampilkan informasi header lengkap (tanggal, jam, pembeli, user pencatat) dan breakdown itemized setiap penjualan dengan harga snapshot pada waktu transaksi (harga_satuan, harga_per_butir_saat_jual, harga_per_kg_saat_jual) untuk memastikan transparansi historis dan mendukung compliance audit.

---

## **INPUT & RIWAYAT PRODUKSI (PEKERJA)**

### Halaman Riwayat Produksi (Lihat)
Halaman ini diakses oleh pekerja untuk melihat riwayat produksi kandang yang ditugaskan. Halaman ini menampilkan audit trail lengkap tentang pencatatan produksi harian dengan informasi seperti tanggal, kandang sumber, jumlah telur (butir dan kg), metrik kesehatan ayam (HDP, HHP, mortality rate), dan user pekerja pencatat, serta filter periode, pagination, dan tombol untuk melihat detail setiap record produksi.

### Halaman Input Produksi Baru
Halaman ini diakses oleh pekerja untuk mencatat produksi harian kandang mereka. Halaman ini menampilkan formulir dengan field input tanggal produksi, jumlah telur dalam satuan butir atau kg (dengan auto-conversion), jumlah ayam yang mati, serta auto-calculation untuk metrik kesehatan (HDP, HHP, Mortality Rate), dengan kandang yang ditugaskan (read-only) dan jumlah ayam hidup saat ini untuk memastikan pekerja hanya mencatat data untuk kandang mereka.

### Halaman Detail Produksi
Halaman ini diakses oleh pekerja untuk melihat detail record produksi tertentu. Halaman ini menampilkan informasi lengkap produksi harian mencakup tanggal, kandang, jumlah telur (butir dan kg), metrik kesehatan (HDP, HHP, Mortality), jumlah ayam mati, keterangan/catatan kesehatan ayam, dan informasi pencatat dengan timestamp untuk keperluan audit dan tracking kesehatan flock historis.

---

## **LAPORAN (PEMILIK)**

### Halaman Laporan Produksi
Halaman ini diakses oleh pemilik untuk melihat laporan analisis data produksi. Halaman ini menampilkan data produksi dengan filter periode fleksibel (1 bulan, 3 bulan, 6 bulan, atau semua waktu) untuk melihat trend produksi historis per kandang, ringkasan produksi, total telur, rata-rata produktivitas (HDP/HHP), dan metrik kesehatan ayam dalam aggregate view, serta tombol export ke PDF/Excel.

### Halaman Laporan Penjualan
Halaman ini diakses oleh pemilik untuk melihat laporan analisis transaksi penjualan. Halaman ini menampilkan data penjualan dengan filter periode untuk tracking revenue, volume penjualan, dan customer patterns, ringkasan penjualan per periode, total rupiah revenue, volume telur terjual dalam satuan butir dan kg, serta breakdown by price category, dilengkapi tombol export ke PDF/Excel.

### Halaman Laporan KPI per Kandang
Halaman ini diakses oleh pemilik untuk melihat laporan Key Performance Indicators (KPI) setiap kandang. Halaman ini menampilkan metrik performa detail seperti HDP, HHP, Mortality Rate, total produksi, total ayam, serta target benchmarks untuk evaluasi performa unit per bulan/tahun, membantu pemilik dalam identifikasi kandang yang underperforming dan pengambilan keputusan untuk optimasi operasional.

### Halaman Export Laporan (PDF)
Halaman ini diakses oleh pemilik ketika melakukan export laporan ke PDF. Halaman ini menampilkan template export PDF untuk laporan produksi dan penjualan dalam format professional siap cetak atau email, mengintegrasikan logo, header informasi perusahaan, tanggal perioda laporan, dan data informatif dalam format tabel dan chart sesuai standar presentasi bisnis.

---

## **MANAJEMEN USER (PEMILIK)**

### Halaman Daftar User
Halaman ini diakses oleh pemilik untuk mengelola semua pengguna sistem. Halaman ini menampilkan daftar user (pemilik dan pekerja) dengan informasi lengkap seperti nama, username, email, role yang diberikan, dan kandang yang ditugaskan (untuk role pekerja), serta tombol aksi untuk menambah user baru, mengubah informasi user, menghapus user, atau mengubah role assignment.

### Halaman Tambah User & Edit User
Halaman ini diakses oleh pemilik untuk membuat atau memodifikasi akun pengguna. Halaman ini menampilkan formulir dengan field input nama lengkap, username, email, password (untuk create) atau password baru (untuk edit), pilihan role (pemilik/pekerja), dan penugasan kandang jika role adalah pekerja, dengan validasi uniqueness username/email dan force strong password untuk kontrol akses terhadap fitur sistem.

---

## **PENGATURAN SISTEM (PEMILIK)**

### Halaman Pengaturan List
Halaman ini diakses oleh pemilik untuk melihat konfigurasi sistem manajemen telur. Halaman ini menampilkan semua parameter global untuk sistem termasuk faktor konversi butir ke kilogram (conversion_butir_per_kg), tax rate, margin configurations, dan setting lain dalam key-value store, dengan informasi nilai setiap setting, tipe data, dan timestamp perubahan terakhir, serta tombol edit untuk memodifikasi parameter fundamental.

### Halaman Edit Pengaturan
Halaman ini diakses oleh pemilik untuk memodifikasi individual setting system. Halaman ini menampilkan formulir dengan input field yang disesuaikan berdasarkan tipe data setting (string/integer/decimal/boolean), dengan validasi input berdasarkan range atau format yang ditentukan, menyimpan history perubahan ke database, dan apply perubahan secara real-time ke seluruh sistem tanpa downtime untuk fleksibilitas business rule updates.

---

**Dokumentasi dibuat:** April 2026  
**Project:** Hans Jaya Poultry Management System  
**Format:** Deskripsi Perancangan Halaman - Implementasi (Lengkap)  
**Total Halaman:** 30 halaman user-facing (7 auth + 1 public + 22 authenticated)
