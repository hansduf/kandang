# 📋 Dokumen Pengujian Sistem Poultry Management
**Proyek:** Hans Jaya Poultry  
**Tanggal:** April 2026  
**Versi:** 1.0

---

## Daftar Isi
1. [Pengujian User Management](#1-pengujian-user-management)
2. [Pengujian Kandang Management](#2-pengujian-kandang-management)
3. [Pengujian Produksi Telur](#3-pengujian-produksi-telur)
4. [Pengujian Penjualan](#4-pengujian-penjualan)
5. [Pengujian Permission & Role](#5-pengujian-permission--role)
6. [Pengujian Stock Calculation](#6-pengujian-stock-calculation)
7. [Pengujian Pricing Management](#7-pengujian-pricing-management)

---

## 1. PENGUJIAN USER MANAGEMENT

### 1.1 Login User

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 1.1.1 | Login dengan email dan password yang benar | 1. Buka aplikasi<br/>2. Klik login<br/>3. Masukkan email<br/>4. Masukkan password<br/>5. Klik tombol login | Email: owner@test.local<br/>Password: password123 | User berhasil login, redirect ke dashboard | | Valid |
| 1.1.2 | Login dengan email yang tidak terdaftar | 1. Buka halaman login<br/>2. Masukkan email tidak terdaftar<br/>3. Masukkan password<br/>4. Klik login | Email: tidak_ada@test.local<br/>Password: password123 | Tampil pesan error "Email tidak ditemukan" | | Valid |
| 1.1.3 | Login dengan password salah | 1. Buka halaman login<br/>2. Masukkan email terdaftar<br/>3. Masukkan password salah<br/>4. Klik login | Email: owner@test.local<br/>Password: salah123 | Tampil pesan error "Password tidak sesuai" | | Valid |
| 1.1.4 | Login dengan email kosong | 1. Buka halaman login<br/>2. Kosongkan field email<br/>3. Masukkan password<br/>4. Klik login | Email: (kosong)<br/>Password: password123 | Tampil validasi error "Email wajib diisi" | | Valid |
| 1.1.5 | Login dengan password kosong | 1. Buka halaman login<br/>2. Masukkan email<br/>3. Kosongkan password<br/>4. Klik login | Email: owner@test.local<br/>Password: (kosong) | Tampil validasi error "Password wajib diisi" | | Valid |

### 1.2 Logout User

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 1.2.1 | Logout setelah login berhasil | 1. Login terlebih dahulu<br/>2. Klik menu profile<br/>3. Klik tombol logout | Klik logout button | Redirect ke halaman login, session dihapus | | Valid |
| 1.2.2 | Logout dan cek session | 1. Logout<br/>2. Refresh page<br/>3. Cek apakah redirect ke login | Logout & refresh | User tidak bisa akses dashboard tanpa login | | Valid |

### 1.3 Registrasi User

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 1.3.1 | Registrasi user baru dengan data lengkap | 1. Klik daftar sekarang<br/>2. Isi form registrasi<br/>3. Isi email yang unik<br/>4. Isi password<br/>5. Klik daftar | Name: Pekerja Baru<br/>Email: pekerja@test.local<br/>Password: password123<br/>Password Confirm: password123<br/>Role: Pekerja | User berhasil terdaftar, redirect ke login atau dashboard | | Valid |
| 1.3.2 | Registrasi dengan email yang sudah terdaftar | 1. Klik daftar<br/>2. Isi form dengan email existing<br/>3. Klik daftar | Email: owner@test.local (sudah ada) | Tampil pesan error "Email sudah terdaftar" | | Valid |
| 1.3.3 | Registrasi dengan password tidak sesuai | 1. Klik daftar<br/>2. Isi form<br/>3. Password tidak sesuai dengan konfirmasi<br/>4. Klik daftar | Password: password123<br/>Konfirmasi: password456 | Tampil error "Password tidak sesuai" | | Valid |
| 1.3.4 | Registrasi dengan field kosong | 1. Klik daftar<br/>2. Kosongkan beberapa field<br/>3. Klik daftar | Kosongkan nama | Tampil validasi error "Nama wajib diisi" | | Valid |
| 1.3.5 | Registrasi dengan email format tidak valid | 1. Klik daftar<br/>2. Masukkan email tidak valid<br/>3. Klik daftar | Email: tidak_valid@@@test | Tampil error "Format email tidak valid" | | Valid |

---

## 2. PENGUJIAN KANDANG MANAGEMENT

### 2.1 Tambah Kandang

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 2.1.1 | Tambah kandang dengan data lengkap | 1. Login sebagai owner<br/>2. Masuk menu Kandang<br/>3. Klik tambah kandang<br/>4. Isi form<br/>5. Klik simpan | Nama: Kandang A<br/>Lokasi: Area 1<br/>Kapasitas: 1000<br/>PIC: Owner | Kandang berhasil ditambahkan, tampil di daftar | | Valid |
| 2.1.2 | Tambah kandang dengan nama kosong | 1. Buka form tambah<br/>2. Kosongkan nama<br/>3. Isi field lain<br/>4. Klik simpan | Nama: (kosong)<br/>Lokasi: Area 1<br/>Kapasitas: 1000 | Tampil error "Nama kandang wajib diisi" | | Valid |
| 2.1.3 | Tambah kandang dengan kapasitas tidak valid | 1. Buka form tambah<br/>2. Isi nama<br/>3. Masukkan kapasitas tidak valid<br/>4. Klik simpan | Kapasitas: abc (bukan angka) | Tampil error "Kapasitas harus berupa angka" | | Valid |
| 2.1.4 | Tambah kandang dengan duplikat lokasi dan nama | 1. Buka form tambah<br/>2. Masukkan nama & lokasi yang sudah ada<br/>3. Klik simpan | Nama: Kandang A<br/>Lokasi: Area 1 (sudah ada) | Tampil error "Kandang sudah ada di lokasi ini" | | Valid |

### 2.2 Edit Kandang

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 2.2.1 | Edit kandang dengan data valid | 1. Login sebagai owner<br/>2. Masuk menu Kandang<br/>3. Klik edit pada kandang<br/>4. Ubah kapasitas<br/>5. Klik simpan | Kapasitas baru: 1200 | Kandang berhasil diupdate | | Valid |
| 2.2.2 | Edit kandang dengan kapasitas 0 | 1. Buka form edit kandang<br/>2. Ubah kapasitas jadi 0<br/>3. Klik simpan | Kapasitas: 0 | Tampil error "Kapasitas minimal 1 ekor" | | Valid |

### 2.3 Hapus Kandang

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 2.3.1 | Hapus kandang tanpa produksi | 1. Login sebagai owner<br/>2. Pilih kandang tanpa produksi<br/>3. Klik hapus<br/>4. Konfirmasi | Klik tombol hapus | Kandang berhasil dihapus | | Valid |
| 2.3.2 | Hapus kandang yang memiliki produksi | 1. Login sebagai owner<br/>2. Pilih kandang dengan produksi<br/>3. Klik hapus | Klik tombol hapus | Tampil warning "Tidak bisa hapus kandang yang memiliki data produksi" | | Valid |
| 2.3.3 | Worker mencoba hapus kandang | 1. Login sebagai pekerja<br/>2. Buka halaman kandang<br/>3. Coba klik hapus | Klik hapus | Tampil error "Tidak ada akses untuk menghapus" | | Valid |

### 2.4 Lihat Daftar Kandang

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 2.4.1 | Lihat daftar kandang dengan paginasi | 1. Login<br/>2. Masuk menu Kandang<br/>3. Perhatikan paginasi<br/>4. Klik halaman 2 | Klik page 2 | Menampilkan kandang halaman 2 | | Valid |
| 2.4.2 | Cari kandang berdasarkan nama | 1. Masuk menu Kandang<br/>2. Isi search box<br/>3. Tekan enter | Cari: "Kandang A" | Hanya kandang dengan nama "Kandang A" yang tampil | | Valid |
| 2.4.3 | Worker bisa lihat semua kandang | 1. Login sebagai pekerja<br/>2. Masuk menu Kandang<br/>3. Lihat daftar | Lihat daftar | Worker bisa melihat semua kandang | | Valid |

---

## 3. PENGUJIAN PRODUKSI TELUR

### 3.1 Tambah Produksi Telur

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 3.1.1 | Tambah produksi dengan data lengkap (worker) | 1. Login sebagai pekerja<br/>2. Masuk menu Produksi<br/>3. Klik tambah<br/>4. Pilih kandang<br/>5. Isi jumlah telur<br/>6. Klik simpan | Kandang: Kandang A<br/>Tanggal: 2026-04-22<br/>Jumlah: 500 butir<br/>HDP: 95<br/>HHP: 92<br/>Mortalitas: 3 | Produksi berhasil tercatat, stok bertambah | | Valid |
| 3.1.2 | Tambah produksi dengan jumlah negatif | 1. Buka form tambah<br/>2. Masukkan jumlah negatif<br/>3. Klik simpan | Jumlah: -100 | Tampil error "Jumlah tidak boleh negatif" | | Valid |
| 3.1.3 | Tambah produksi tanpa memilih kandang | 1. Buka form tambah<br/>2. Kosongkan pilihan kandang<br/>3. Klik simpan | Kandang: (kosong) | Tampil error "Pilih kandang terlebih dahulu" | | Valid |
| 3.1.4 | Worker menambah produksi | 1. Login sebagai pekerja<br/>2. Masuk Produksi<br/>3. Tambah produksi | Data produksi valid | Produksi berhasil disimpan | | Valid |
| 3.1.5 | Worker mencoba ubah harga (tidak boleh) | 1. Worker buka form tambah<br/>2. Coba ubah field harga<br/>3. Cek apakah bisa diedit | Field harga | Field harga tidak bisa diedit worker | | Valid |

### 3.2 Edit Produksi Telur

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 3.2.1 | Edit produksi hari ini | 1. Login sebagai owner<br/>2. Masuk Produksi<br/>3. Pilih produksi hari ini<br/>4. Ubah jumlah<br/>5. Klik simpan | Jumlah baru: 600 | Produksi berhasil diupdate, stok otomatis terupdate | | Valid |
| 3.2.2 | Edit produksi hari kemarin (tidak boleh) | 1. Masuk Produksi<br/>2. Coba edit produksi kemarin<br/>3. Coba ubah | Edit kemarin | Tampil pesan "Tidak bisa mengedit produksi hari lalu" | | Valid |
| 3.2.3 | Edit HDP/HHP/mortalitas | 1. Buka form edit produksi<br/>2. Ubah nilai HDP HHP<br/>3. Klik simpan | HDP: 98<br/>HHP: 95 | Nilai berhasil diupdate | | Valid |

### 3.3 Hapus Produksi Telur

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 3.3.1 | Hapus produksi hari ini | 1. Login sebagai owner<br/>2. Pilih produksi hari ini<br/>3. Klik hapus<br/>4. Konfirmasi | Klik hapus | Produksi dihapus, stok berkurang | | Valid |
| 3.3.2 | Hapus produksi yang sudah dijual | 1. Pilih produksi yang punya penjualan<br/>2. Klik hapus | Hapus | Tampil warning "Tidak bisa hapus, sudah ada penjualan" | | Valid |
| 3.3.3 | Worker mencoba hapus produksi | 1. Login sebagai pekerja<br/>2. Coba klik hapus | Klik hapus | Tampil error "Tidak ada akses" | | Valid |

---

## 4. PENGUJIAN PENJUALAN

### 4.1 Buat Penjualan

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 4.1.1 | Buat penjualan dengan detail valid (owner) | 1. Login sebagai owner<br/>2. Masuk menu Penjualan<br/>3. Klik tambah<br/>4. Isi nama pembeli<br/>5. Tambah line item<br/>6. Isi jumlah & harga<br/>7. Klik simpan | Pembeli: Toko ABC<br/>Kandang: Kandang A<br/>Jumlah: 100 butir<br/>Harga: 500/butir<br/>Subtotal: 50000 | Penjualan berhasil dibuat, stok berkurang 100 butir | | Valid |
| 4.1.2 | Buat penjualan tanpa detail item | 1. Buka form penjualan<br/>2. Isi pembeli<br/>3. Tidak tambah item<br/>4. Klik simpan | Pembeli: Toko ABC<br/>Item: kosong | Tampil error "Minimal 1 item penjualan" | | Valid |
| 4.1.3 | Buat penjualan lebih dari stok | 1. Cek stok kandang<br/>2. Coba jual melebihi stok<br/>3. Klik simpan | Stok: 100, Jual: 150 | Tampil error "Stok tidak cukup" | | Valid |
| 4.1.4 | Buat penjualan dengan harga 0 | 1. Buka form penjualan<br/>2. Masukkan harga 0<br/>3. Klik simpan | Harga: 0 | Tampil error "Harga tidak boleh 0" | | Valid |
| 4.1.5 | Worker mencoba buat penjualan | 1. Login sebagai pekerja<br/>2. Coba akses menu penjualan | Akses penjualan | Worker tidak bisa akses atau readonly | | Valid |

### 4.2 Edit Penjualan

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 4.2.1 | Edit penjualan yang baru dibuat | 1. Login sebagai owner<br/>2. Pilih penjualan baru<br/>3. Klik edit<br/>4. Ubah jumlah<br/>5. Klik simpan | Jumlah baru: 80 butir | Penjualan diupdate, stok terupdate (tambah kembali 20) | | Valid |
| 4.2.2 | Edit penjualan tua (> 7 hari) | 1. Pilih penjualan lama<br/>2. Klik edit | Penjualan > 7 hari | Tampil warning "Tidak bisa edit penjualan lama" | | Valid |
| 4.2.3 | Edit harga di detail penjualan | 1. Buka edit penjualan<br/>2. Ubah harga di item<br/>3. Klik simpan | Harga baru: 600 | Harga item terupdate | | Valid |

### 4.3 Hapus Penjualan

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 4.3.1 | Hapus penjualan yang baru | 1. Login sebagai owner<br/>2. Pilih penjualan baru<br/>3. Klik hapus<br/>4. Konfirmasi | Klik hapus | Penjualan dihapus, stok kembali | | Valid |
| 4.3.2 | Hapus penjualan lama (> 7 hari) | 1. Pilih penjualan lama<br/>2. Klik hapus | Hapus | Tampil error "Tidak bisa hapus penjualan lama" | | Valid |
| 4.3.3 | Worker mencoba hapus penjualan | 1. Login sebagai pekerja<br/>2. Coba hapus | Hapus | Tampil error "Tidak ada akses" | | Valid |

### 4.4 Lihat Laporan Penjualan

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 4.4.1 | Lihat laporan penjualan per periode | 1. Login<br/>2. Masuk Laporan Penjualan<br/>3. Pilih tanggal dari - ke<br/>4. Klik lihat | Tanggal: 2026-04-01 - 2026-04-22 | Tampil daftar penjualan sesuai periode | | Valid |
| 4.4.2 | Export laporan ke PDF | 1. Buka laporan penjualan<br/>2. Klik export PDF | Klik export | File PDF berhasil didownload | | Valid |
| 4.4.3 | Export laporan ke Excel | 1. Buka laporan penjualan<br/>2. Klik export Excel | Klik export | File Excel berhasil didownload | | Valid |

---

## 5. PENGUJIAN PERMISSION & ROLE

### 5.1 Permission Owner (Pemilik)

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 5.1.1 | Owner bisa lihat semua kandang | 1. Login sebagai owner<br/>2. Buka menu Kandang | Lihat kandang | Owner melihat semua kandang | | Valid |
| 5.1.2 | Owner bisa edit harga telur | 1. Login sebagai owner<br/>2. Masuk menu Harga<br/>3. Klik edit | Edit harga | Owner bisa edit harga | | Valid |
| 5.1.3 | Owner bisa lihat laporan lengkap | 1. Login sebagai owner<br/>2. Buka laporan | Buka laporan | Owner lihat semua data | | Valid |
| 5.1.4 | Owner bisa manage user | 1. Login sebagai owner<br/>2. Masuk menu User<br/>3. Lihat daftar user | Lihat user | Owner bisa lihat & kelola user | | Valid |
| 5.1.5 | Owner bisa hapus penjualan baru | 1. Login sebagai owner<br/>2. Pilih penjualan baru<br/>3. Klik hapus | Hapus | Owner berhasil hapus | | Valid |

### 5.2 Permission Worker (Pekerja)

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 5.2.1 | Worker bisa lihat kandang (read-only) | 1. Login sebagai worker<br/>2. Buka menu Kandang | Lihat kandang | Worker lihat kandang tapi tidak bisa edit/hapus | | Valid |
| 5.2.2 | Worker tidak bisa edit kandang | 1. Login sebagai worker<br/>2. Coba klik edit kandang | Klik edit | Tidak ada tombol edit atau disabled | | Valid |
| 5.2.3 | Worker bisa tambah produksi | 1. Login sebagai worker<br/>2. Masuk Produksi<br/>3. Klik tambah | Klik tambah | Worker bisa tambah produksi | | Valid |
| 5.2.4 | Worker tidak bisa edit harga | 1. Login sebagai worker<br/>2. Coba akses menu Harga | Akses harga | Worker tidak lihat atau readonly | | Valid |
| 5.2.5 | Worker tidak bisa lihat laporan penjualan | 1. Login sebagai worker<br/>2. Coba buka laporan penjualan | Akses laporan | Worker tidak bisa akses atau error 403 | | Valid |
| 5.2.6 | Worker tidak bisa hapus data | 1. Login sebagai worker<br/>2. Coba klik tombol hapus | Klik hapus | Tidak ada tombol atau disabled | | Valid |

---

## 6. PENGUJIAN STOCK CALCULATION

### 6.1 Kalkulasi Stok

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 6.1.1 | Stok bertambah setelah produksi | 1. Login owner<br/>2. Lihat stok kandang A = 100<br/>3. Tambah produksi 300<br/>4. Lihat stok lagi | Stok awal: 100<br/>Produksi: 300 | Stok menjadi 400 | | Valid |
| 6.1.2 | Stok berkurang setelah penjualan | 1. Stok kandang A = 400<br/>2. Tambah penjualan 100<br/>3. Lihat stok lagi | Stok: 400<br/>Jual: 100 | Stok menjadi 300 | | Valid |
| 6.1.3 | Stok tidak boleh negatif | 1. Stok kandang A = 50<br/>2. Coba jual 100 | Stok: 50, Jual: 100 | Penjualan ditolak, stok tetap 50 | | Valid |
| 6.1.4 | Konversi butir ke kg otomatis | 1. Tambah produksi 160 butir<br/>2. Lihat konversi kg | 160 butir | Otomatis jadi 10 kg (160/16) | | Valid |
| 6.1.5 | Konversi kg ke butir otomatis | 1. Tampilkan 10 kg<br/>2. Lihat konversi butir | 10 kg | Otomatis jadi 160 butir (10*16) | | Valid |
| 6.1.6 | Laporan stok harian akurat | 1. Buka laporan stok<br/>2. Pilih tanggal tertentu | Tanggal: 2026-04-22 | Stok sesuai dengan perhitungan (buka + produksi - jual) | | Valid |
| 6.1.7 | Update stok tidak boleh double-entry | 1. Tambah produksi 100<br/>2. Refresh page<br/>3. Lihat stok | Tambah 100 | Stok naik 100 saja, tidak dihitung 2x | | Valid |

### 6.2 Laporan Stok

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 6.2.1 | Export laporan stok PDF | 1. Buka laporan stok<br/>2. Klik export PDF | Klik export | File PDF berhasil didownload | | Valid |
| 6.2.2 | Laporan stok per kandang | 1. Filter kandang A<br/>2. Lihat laporan | Kandang: A | Hanya stok kandang A yang tampil | | Valid |

---

## 7. PENGUJIAN PRICING MANAGEMENT

### 7.1 Tambah Harga

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 7.1.1 | Tambah harga baru dengan data valid | 1. Login sebagai owner<br/>2. Masuk menu Harga<br/>3. Klik tambah<br/>4. Masukkan harga per butir & kg<br/>5. Klik simpan | Harga/butir: 600<br/>Harga/kg: 9600<br/>Berlaku: 2026-04-22 | Harga berhasil ditambahkan | | Valid |
| 7.1.2 | Tambah harga dengan harga 0 | 1. Buka form tambah<br/>2. Masukkan harga 0<br/>3. Klik simpan | Harga: 0 | Tampil error "Harga tidak boleh 0" | | Valid |
| 7.1.3 | Tambah harga dengan tanggal lampau | 1. Buka form tambah<br/>2. Pilih tanggal kemarin<br/>3. Klik simpan | Tanggal: 2026-04-20 | Tampil error "Tanggal tidak boleh di masa lalu" | | Valid |
| 7.1.4 | Harga baru otomatis menjadi aktif | 1. Tambah harga baru<br/>2. Lihat status | Tambah harga | Harga baru status = "aktif" | | Valid |

### 7.2 Edit Harga

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 7.2.1 | Edit harga yang belum berlaku | 1. Login sebagai owner<br/>2. Pilih harga belum aktif<br/>3. Klik edit<br/>4. Ubah harga<br/>5. Klik simpan | Harga baru: 650 | Harga berhasil diubah | | Valid |
| 7.2.2 | Tidak bisa edit harga yang sudah berlaku | 1. Pilih harga aktif (sudah berlaku)<br/>2. Coba klik edit | Edit harga aktif | Tidak ada tombol edit atau disabled | | Valid |

### 7.3 Penghapusan Harga

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 7.3.1 | Hapus harga yang belum berlaku | 1. Pilih harga tidak aktif<br/>2. Klik hapus<br/>3. Konfirmasi | Hapus | Harga berhasil dihapus | | Valid |
| 7.3.2 | Tidak bisa hapus harga yang sedang digunakan | 1. Pilih harga aktif (sudah punya penjualan)<br/>2. Coba hapus | Hapus | Tampil warning "Tidak bisa hapus, sudah ada penjualan" | | Valid |
| 7.3.3 | Tidak boleh semua harga dihapus | 1. Ada 2 harga<br/>2. Hapus harga aktif pertama<br/>3. Coba hapus harga kedua | Hapus semua | Tampil error "Minimal harus ada 1 harga aktif" | | Valid |

### 7.4 Riwayat Harga

| No. | Deskripsi | Prosedur Pengujian | Masukkan | Keluaran yang Diharapkan | Hasil yang Didapatkan | Kesimpulan |
|-----|-----------|-------------------|----------|-------------------------|----------------------|-----------|
| 7.4.1 | Lihat riwayat harga lengkap | 1. Login sebagai owner<br/>2. Masuk menu Harga Riwayat<br/>3. Lihat daftar | Lihat riwayat | Tampil semua harga dari awal hingga sekarang | | Valid |
| 7.4.2 | Penjualan lama gunakan harga lama | 1. Ada penjualan tanggal lalu dengan harga lama<br/>2. Ubah harga baru<br/>3. Lihat penjualan lama | Lihat penjualan lama | Penjualan lama tetap gunakan harga lama | | Valid |
| 7.4.3 | Penjualan baru gunakan harga baru | 1. Ada harga baru aktif<br/>2. Buat penjualan baru<br/>3. Lihat harganya | Buat penjualan baru | Penjualan baru gunakan harga baru | | Valid |

---

## CATATAN PENGUJIAN

### Hasil Ringkasan
- Total Test Cases: **89**
- Test Cases Passed: **___**
- Test Cases Failed: **___**
- Test Cases Skipped: **___**
- Pass Rate: **____%**

### Issue & Bug yang Ditemukan
| No. | Deskripsi | Severity | Status |
|-----|-----------|----------|--------|
| 1 | | | |
| 2 | | | |

### Rekomendasi
- [ ] Semua test cases passed
- [ ] Fix bugs yang ditemukan
- [ ] Re-testing untuk bug yang sudah diperbaiki
- [ ] Ready for production

---

**Tanggal Pengujian:** ________________  
**Tester:** ________________________  
**Disetujui oleh:** ________________________  

---

*Dokumen ini dibuat dengan standar pengujian sistem berbasis black box testing.*  
*Format referensi: Level 1 API, Level 2 Database, Level 0 Permission Testing*
