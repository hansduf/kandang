# Deskripsi Perancangan Halaman
## Hans Jaya Poultry Management System

---

## **PUBLIC & AUTHENTICATION**

### Halaman Welcome
Halaman ini dapat diakses oleh semua user. Menampilkan headline, deskripsi sistem manajemen, logo perusahaan, tombol aksi (dashboard untuk user login, atau login untuk user baru), dan footer copyright.

### Halaman Login
Halaman ini dapat diakses sebelum login. Menampilkan formulir dengan field email/username, password, checkbox remember me, tombol login, serta demo accounts untuk testing (pemilik, kandang1, kandang2, kandang3).

### Halaman Register
Halaman ini dapat diakses sebelum login untuk membuat akun baru. Menampilkan formulir dengan field nama, email, password, konfirmasi password, dan link kembali ke login.

### Halaman Forgot Password
Halaman ini untuk user yang lupa password. Menampilkan formulir dengan field email saja dan instruksi bahwa link reset password akan dikirim ke email.

### Halaman Reset Password
Halaman ini diakses dari link di email untuk reset password. Menampilkan formulir dengan field email (pre-filled), password baru, dan konfirmasi password dengan hidden token untuk keamanan.

### Halaman Confirm Password
Halaman ini ditampilkan saat user perlu verifikasi sebelum operasi sensitif. Menampilkan instruksi keamanan dan formulir dengan field password saja untuk verifikasi.

### Halaman Verify Email
Halaman ini ditampilkan saat user baru register dan harus verifikasi email. Menampilkan instruksi check email, tombol resend verification email, dan tombol logout.

---

## **DASHBOARD**

### Halaman Dashboard (Pemilik)
Halaman diakses pemilik setelah login. Menampilkan ringkasan status produksi-penjualan dengan filter periode, kartu metrik (stok telur, ayam hidup, kematian, produksi, penjualan, kandang aktif, status sistem), grafik trend produksi multi-kandang, dan tombol navigasi cepat ke modul utama.

### Halaman Dashboard (Pekerja)
Halaman diakses pekerja setelah login. Menampilkan data kandang yang ditugaskan saja dengan informasi kandang (nama, supervisor, kapasitas), metrik produksi kandang tersebut dengan filter periode (7 hari, bulanan, semua waktu).

---

## **KANDANG MANAGEMENT (PEMILIK)**

### Halaman Lihat Kandang
Halaman untuk melihat semua kandang. Menampilkan statistik ringkasan (total kandang, total kapasitas, total ayam hidup, rata-rata produktivitas), overview setiap kandang dengan kartu yang berisi kapasitas, ayam aktual, kematian, produksi, HDP dalam periode tertentu, dan tombol aksi (edit, hapus, tambah).

### Halaman Tambah Kandang
Halaman untuk menambah kandang baru. Menampilkan formulir dengan field nama kandang, kapasitas ayam, penugasan supervisor (PIC), keterangan, serta tombol simpan dan batal.

### Halaman Edit Kandang
Halaman untuk mengubah data kandang tertentu. Menampilkan formulir pre-filled dengan data kandang sebelumnya (nama, kapasitas, PIC, keterangan), serta tombol perbarui dan batal.

---

## **HARGA MANAGEMENT (PEMILIK)**

### Halaman Lihat Harga
Halaman untuk mengelola harga produk telur. Menampilkan grafik trend harga historis dengan filter bulan, tabel harga aktif (jenis, harga/kg, harga/butir, berlaku sejak, berakhir pada, oleh, aksi), tabel riwayat harga hangus, dan tombol tambah harga baru.

### Halaman Input Harga Baru
Halaman untuk menambah harga baru. Menampilkan formulir dengan field jenis harga (kandang/grosir/konsumen), tanggal berlaku, harga per kg, harga per butir, keterangan, serta tombol simpan dan batal.

### Halaman Edit Harga
Halaman untuk mengubah data harga tertentu. Menampilkan formulir pre-filled dengan data harga sebelumnya (jenis harga read-only, tanggal, harga/kg, harga/butir, keterangan), serta tombol perbarui dan batal.

---

## **PENJUALAN MANAGEMENT (PEMILIK)**

### Halaman Lihat Penjualan
Halaman untuk melihat semua transaksi penjualan. Menampilkan tabel penjualan dengan kolom tanggal, pembeli, user pencatat, jenis harga, harga/kg, total butir, total kg, items, total harga, aksi (lihat, edit, hapus), filter periode, pagination (50 items/page).

### Halaman Tambah Penjualan
Halaman untuk membuat transaksi penjualan baru. Menampilkan formulir header (tanggal, jam, nama pembeli), display stok tersedia real-time, tabel line items dinamis (jenis harga, satuan, jumlah, harga, subtotal, hapus), tombol tambah item dan simpan.

### Halaman Edit Penjualan
Halaman untuk mengubah transaksi penjualan tertentu. Menampilkan formulir header pre-filled, stok display, tabel line items dengan nilai existing, tombol tambah item, perbarui, dan batal.

### Halaman Detail Penjualan
Halaman untuk melihat detail transaksi penjualan tertentu. Menampilkan informasi header lengkap (tanggal, jam, pembeli, user pencatat) dan breakdown itemized dengan harga snapshot (harga_satuan, harga_per_butir_saat_jual, harga_per_kg_saat_jual) untuk transparansi historis dan audit.

---

## **PRODUKSI ENTRY (PEKERJA)**

### Halaman Lihat Produksi
Halaman untuk melihat riwayat produksi kandang yang ditugaskan. Menampilkan tabel produksi dengan kolom tanggal, kandang, produksi butir, produksi kg, HDP, HHP, mortality, pekerja, aksi (lihat), filter periode, pagination (50 items/page).

### Halaman Input Produksi
Halaman untuk mencatat produksi harian. Menampilkan display kandang yang ditugaskan (read-only), formulir dengan field tanggal produksi, satuan (butir/kg), jumlah telur dengan auto-conversion, ayam mati dengan auto-calculation HDP/HHP/Mortality Rate, jumlah ayam hidup saat ini, tombol simpan dan batal.

### Halaman Detail Produksi
Halaman untuk melihat detail record produksi tertentu. Menampilkan informasi lengkap (tanggal, kandang, produksi butir/kg, HDP, HHP, mortality, ayam mati, keterangan kesehatan ayam, pencatat, timestamp) untuk audit dan tracking kesehatan historis.

---

## **LAPORAN (PEMILIK)**

### Halaman Laporan Produksi
Halaman untuk melihat analisis data produksi. Menampilkan tabel/grafik produksi dengan filter periode (1 bulan, 3 bulan, 6 bulan, semua waktu), ringkasan produksi, total telur, rata-rata produktivitas (HDP/HHP), metrik kesehatan ayam aggregate, tombol export PDF/Excel.

### Halaman Laporan Penjualan
Halaman untuk melihat analisis transaksi penjualan. Menampilkan tabel/grafik penjualan dengan filter periode, ringkasan penjualan per periode, total revenue, volume telur (butir/kg), breakdown by price category, tombol export PDF/Excel.

### Halaman Laporan KPI per Kandang
Halaman untuk melihat KPI setiap kandang. Menampilkan metrik performa detail (HDP, HHP, mortality, total produksi, total ayam, target benchmarks) per kandang per bulan/tahun untuk evaluasi performa unit dan identifikasi underperforming.

### Halaman Export Laporan (PDF)
Halaman untuk template export PDF laporan produksi dan penjualan. Menampilkan format professional siap cetak/email dengan logo, header informasi perusahaan, tanggal perioda, data informatif dalam format tabel dan chart sesuai standar bisnis.

---

## **USER MANAGEMENT (PEMILIK)**

### Halaman Lihat User
Halaman untuk mengelola semua pengguna sistem. Menampilkan tabel user dengan kolom nama, username, email, role, kandang (untuk pekerja), aksi (edit, hapus, tambah user baru), pagination.

### Halaman Tambah User
Halaman untuk membuat akun pengguna baru. Menampilkan formulir dengan field nama lengkap, username, email, password, role (pemilik/pekerja), penugasan kandang (jika role pekerja), tombol simpan dan batal.

### Halaman Edit User
Halaman untuk mengubah data pengguna tertentu. Menampilkan formulir pre-filled dengan data user sebelumnya (nama, username, email, role, kandang), field password baru (optional), tombol perbarui dan batal.

---

## **PENGATURAN SISTEM (PEMILIK)**

### Halaman Lihat Pengaturan
Halaman untuk melihat konfigurasi sistem. Menampilkan tabel semua setting global (conversion_butir_per_kg, tax_rate, margin_config, dll) dengan kolom kunci, nilai, tipe data, last updated, aksi (edit), tombol tambah setting baru.

### Halaman Edit Pengaturan
Halaman untuk memodifikasi setting system tertentu. Menampilkan formulir dengan input field disesuaikan tipe data (string/integer/decimal/boolean), dengan validasi range/format, tombol simpan dan batal untuk apply real-time tanpa restart sistem.

---

**Dokumentasi dibuat:** April 2026  
**Project:** Hans Jaya Poultry Management System  
**Format:** Deskripsi Singkat Per Halaman  
**Total Halaman:** 33 halaman user-facing (dengan separation: create/edit/list terpisah)
