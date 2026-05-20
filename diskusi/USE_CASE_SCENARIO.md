# USE CASE SCENARIO
## Hans Jaya Poultry Farm Management System

**Tanggal Analisis:** 8 April 2026  
**Status:** LENGKAP - Semua use case berdasarkan implementation

---

## 1. LOGIN

Pada halaman login, pemilik dan pekerja dapat masuk ke dalam sistem agar bisa mengakses fitur manajemen peternakan sesuai dengan role mereka. Login ini merupakan langkah awal yang sangat penting untuk mengakses seluruh layanan dan fitur di sistem Hans Jaya Poultry Farm Management System. Sistem akan memvalidasi kredensial pengguna dan menampilkan dashboard yang sesuai dengan role masing-masing (pemilik atau pekerja). Untuk scenario use case login dapat dilihat pada tabel berikut.

### Skenario Login

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Login |
| **Actor** | Pemilik, Pekerja |
| **Tujuan** | Masuk ke sistem manajemen peternakan |
| **Kondisi Awal** | Sistem menampilkan halaman login |
| **Kondisi Akhir** | Sistem menampilkan dashboard sesuai role |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan halaman form login |
| 2 Mengisi email/username dan password | |
| 3 Menekan tombol login | |
| | 4 Validasi email dan password |
| | 5 Cek role pengguna (pemilik/pekerja) |
| | 6 Menampilkan dashboard sesuai role |

#### Skenario Alternatif (alternative flow): Jika email/password salah

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan pesan kesalahan "Email atau password tidak sesuai" |
| | 2 Menampilkan halaman form login |
| 3 Mengisi email dan password kembali | |
| 4 Menekan tombol login | |
| | 5 Validasi email dan password |
| | 6 Menampilkan dashboard sesuai role (jika benar) |

---

## 2. LIHAT DASHBOARD

Dashboard merupakan halaman utama yang menampilkan ringkasan lengkap semua data peternakan dalam satu tempat. Dashboard berfungsi sebagai control center untuk pemilik dan pekerja melihat status real-time stok telur, ringkasan penjualan, dan data produksi per kandang. Dengan dashboard yang informatif, pemilik dapat membuat keputusan bisnis lebih cepat dan pekerja dapat memantau performa kandang mereka. Setiap role memiliki dashboard yang berbeda sesuai dengan kebutuhan dan akses mereka. Untuk scenario use case dashboard dapat dilihat pada tabel berikut.

### Skenario Dashboard Pemilik

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Dashboard Pemilik |
| **Actor** | Pemilik |
| **Tujuan** | Melihat ringkasan semua data peternakan |
| **Kondisi Awal** | Pengguna sudah login sebagai pemilik |
| **Kondisi Akhir** | Sistem menampilkan dashboard pemilik dengan filter periode |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan dashboard dengan default periode (bulan ini) |
| | 2 Menampilkan stok telur terkini (butir & kg) |
| | 3 Menampilkan summary penjualan (total transaksi, total harga) |
| | 4 Menampilkan ringkasan produksi per kandang |
| 5 Pemilik memilih filter periode (hari, 7 hari, bulan, semua) | |
| | 6 Sistem update data dashboard sesuai periode |

#### Skenario Alternatif: Jika belum ada data

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan pesan "Belum ada data untuk periode ini" |
| | 2 Menampilkan form untuk mulai input data |

---

### Skenario Dashboard Pekerja

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Dashboard Pekerja |
| **Actor** | Pekerja |
| **Tujuan** | Melihat ringkasan kandang yang menjadi tanggung jawab |
| **Kondisi Awal** | Pengguna sudah login sebagai pekerja |
| **Kondisi Akhir** | Sistem menampilkan dashboard pekerja dengan data kandang mereka |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan dashboard dengan default periode (7 hari) |
| | 2 Menampilkan informasi kandang yang dijaga pekerja |
| | 3 Menampilkan stok telur terkini |
| | 4 Menampilkan riwayat produksi 7 hari terakhir |
| 5 Pekerja memilih filter periode | |
| | 6 Sistem update data dashboard sesuai periode |

---

## 3. KELOLA KANDANG

Kelola kandang adalah fitur untuk mengelola data lokasi produksi telur di peternakan. Pemilik dapat menambahkan kandang baru, mengubah informasi kandang, menetapkan pekerja sebagai Person In Charge (PIC), serta menghapus kandang jika tidak lagi digunakan. Data kandang seperti nama, jumlah ayam, dan status sangat penting sebagai fondasi untuk tracking produksi dan perhitungan metrik performa. Kandang juga dapat ditampilkan detailnya beserta statistik produksi untuk analisis performa jangka panjang. Untuk scenario use case kelola kandang dapat dilihat pada tabel berikut.

### Skenario Tambah Kandang

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Tambah Kandang |
| **Actor** | Pemilik |
| **Tujuan** | Menambahkan kandang baru ke sistem |
| **Kondisi Awal** | Pemilik berada di halaman daftar kandang |
| **Kondisi Akhir** | Kandang baru berhasil disimpan di sistem |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Tambah Kandang" | |
| | 2 Menampilkan form input kandang |
| 3 Mengisi nama kandang, jumlah ayam, keterangan | |
| 4 Memilih pekerja sebagai PIC (Person In Charge) | |
| 5 Klik tombol "Simpan" | |
| | 6 Validasi data (nama kandang tidak boleh kosong) |
| | 7 Menyimpan kandang ke database |
| | 8 Menampilkan pesan "Kandang berhasil ditambahkan" |
| | 9 Redirect ke halaman daftar kandang |

#### Skenario Alternatif: Data tidak valid

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan pesan error "Nama kandang harus diisi" |
| | 2 Menampilkan form dengan data yang sudah diisi sebelumnya |

---

### Skenario Edit Kandang

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Edit Kandang |
| **Actor** | Pemilik |
| **Tujuan** | Mengubah data kandang yang sudah ada |
| **Kondisi Awal** | Pemilik memilih kandang dari daftar |
| **Kondisi Akhir** | Data kandang berhasil diperbarui |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Edit" pada kandang tertentu | |
| | 2 Menampilkan form edit dengan data kandang terkini |
| 3 Mengubah data (nama, jumlah ayam, keterangan, status) | |
| 4 Klik tombol "Simpan" | |
| | 5 Validasi data |
| | 6 Update data kandang ke database |
| | 7 Menampilkan pesan "Kandang berhasil diperbarui" |

---

### Skenario Hapus Kandang

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Hapus Kandang |
| **Actor** | Pemilik |
| **Tujuan** | Menghapus kandang dari sistem |
| **Kondisi Awal** | Pemilik berada di halaman detail kandang |
| **Kondisi Akhir** | Kandang dan data terkaitnya terhapus |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Hapus" pada kandang | |
| | 2 Menampilkan konfirmasi "Apakah Anda yakin ingin menghapus?" |
| 3 Klik "Ya, Hapus" | |
| | 4 Menghapus kandang dan semua data produksi terkait (cascade delete) |
| | 5 Menampilkan pesan "Kandang berhasil dihapus" |
| | 6 Redirect ke halaman daftar kandang |

#### Skenario Alternatif: Kandang masih memiliki stok

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan warning "Kandang masih memiliki riwayat produksi" |
| 2 Pemilik bisa lanjut hapus atau batal | |

---

## 4. INPUT PRODUKSI TELUR

Input produksi telur adalah fitur utama untuk pekerja dalam mencatat hasil produksi harian dari setiap kandang. Pekerja akan menginput jumlah telur yang diproduksi, jumlah ayam yang mati, dan ayam yang masih hidup pada hari tersebut. Sistem secara otomatis akan menghitung metrik penting seperti HDP (Hen Day Production), HHP (Hen House Production), dan mortality rate untuk analisis kesehatan dan performa kandang. Data produksi ini juga secara real-time akan memperbarui stok telur yang tersedia untuk penjualan. Input produksi yang akurat adalah sangat penting untuk tracking bisnis yang transparan dan akurat. Untuk scenario use case input produksi dapat dilihat pada tabel berikut.

### Skenario Input Produksi

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Input Produksi Telur |
| **Actor** | Pekerja |
| **Tujuan** | Memasukkan data produksi harian ke sistem |
| **Kondisi Awal** | Pekerja berada di halaman input produksi |
| **Kondisi Akhir** | Produksi telur berhasil disimpan dan stok terupdate |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Tambah Produksi" | |
| | 2 Menampilkan form input produksi |
| 3 Memilih tanggal produksi | |
| 4 Memilih satuan (butir atau kg) | |
| 5 Mengisi jumlah produksi | |
| | 6 Auto-konversi ke satuan lain |
| 7 Mengisi jumlah ayam hidup | |
| 8 Mengisi jumlah ayam mati (optional) | |
| 9 Mengisi catatan/keterangan (optional) | |
| 10 Klik tombol "Simpan" | |
| | 11 Validasi data (jumlah ayam hidup minimal = jumlah ayam di kandang - ayam mati) |
| | 12 Auto-calculate HDP, HHP, Mortality |
| | 13 Menyimpan produksi ke database |
| | 14 Update stok_telur dengan jumlah terbaru |
| | 15 Menampilkan pesan "Produksi berhasil disimpan" |

#### Skenario Alternatif: Data tidak valid

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan error "Jumlah ayam hidup tidak sesuai" |
| | 2 Menampilkan form dengan data yang sudah diisi sebelumnya |

---

### Skenario Lihat Riwayat Produksi

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Riwayat Produksi |
| **Actor** | Pekerja |
| **Tujuan** | Melihat history produksi yang sudah diinput |
| **Kondisi Awal** | Pekerja berada di halaman produksi |
| **Kondisi Akhir** | Sistem menampilkan daftar produksi dengan detail |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan daftar riwayat produksi (terbaru dulu) |
| | 2 Menampilkan informasi: tanggal, jumlah, HDP, HHP, mortality |
| | 3 Menampilkan pagination (10 per halaman) |
| 4 Pekerja klik detail untuk lihat record tertentu | |
| | 5 Menampilkan detail produksi lengkap |
| 6 Pekerja bisa kembali ke daftar | |

---

## 5. KELOLA HARGA TELUR

Kelola harga telur adalah fitur untuk pemilik dalam mengelola pricing strategy dari ketiga jenis harga yang berbeda yaitu kandang, grosir, dan konsumen. Pemilik dapat menambahkan harga baru sesuai dengan strategi pricing dan kondisi pasar, serta melihat history lengkap dari semua harga yang pernah digunakan (aktif atau hangus). Sistem secara otomatis akan menandai harga lama sebagai "hangus" ketika harga baru ditambahkan, sehingga memastikan integritas data historis untuk keperluan laporan dan analisis. Harga yang tersimpan juga akan di-snapshot pada saat transaksi penjualan terjadi, memastikan harga yang akurat untuk setiap pembelian. Untuk scenario use case kelola harga dapat dilihat pada tabel berikut.

### Skenario Tambah Harga Telur

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Tambah Harga Telur |
| **Actor** | Pemilik |
| **Tujuan** | Menambahkan harga telur baru ke sistem |
| **Kondisi Awal** | Pemilik berada di halaman daftar harga |
| **Kondisi Akhir** | Harga baru berhasil disimpan dan harga lama otomatis hangus |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Tambah Harga" | |
| | 2 Menampilkan form input harga |
| 3 Memilih jenis harga (kandang, grosir, konsumen) | |
| 4 Mengisi harga per kg | |
| 5 Mengisi harga per butir (optional) | |
| | 6 Jika harga per butir kosong, auto-calculate dari harga per kg |
| 7 Memilih tanggal berlaku | |
| 8 Mengisi keterangan (optional) | |
| 9 Klik tombol "Simpan" | |
| | 10 Cari harga aktif jenis yang sama |
| | 11 Tandai harga lama sebagai "hangus" (set tanggal_akhir) |
| | 12 Menyimpan harga baru sebagai "aktif" |
| | 13 Menampilkan pesan "Harga berhasil ditambahkan" |

#### Skenario Alternatif: Harga per butir diisi manual

| Actor | Sistem |
|-------|--------|
| 5 Mengisi harga per kg dan harga per butir manual | |
| | 6 Validasi kedua harga konsisten (tidak otomatis overwrite) |
| | 7 Lanjut ke step 7 |

---

### Skenario Edit Harga Telur

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Edit Harga Telur |
| **Actor** | Pemilik |
| **Tujuan** | Mengubah harga telur yang masih dalam status aktif |
| **Kondisi Awal** | Pemilik memilih harga aktif yang akan diedit |
| **Kondisi Akhir** | Data harga berhasil diperbarui |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Edit" pada harga yang aktif | |
| | 2 Menampilkan form edit dengan data harga terkini |
| 3 Mengubah harga per kg dan/atau harga per butir | |
| 4 Mengubah tanggal berlaku (optional) | |
| 5 Klik tombol "Simpan" | |
| | 6 Validasi harga (tidak boleh kosong) |
| | 7 Update data harga ke database |
| | 8 Menampilkan pesan "Harga berhasil diperbarui" |

#### Skenario Alternatif: Harga per butir diubah manual

| Actor | Sistem |
|-------|--------|
| 3 Mengubah harga per kg dan harga per butir secara manual | |
| | 4 Validasi kedua harga konsisten |
| | 5 Jika tidak konsisten, tampilkan warning |
| 6 Pemilik confirm untuk lanjut atau batal | |

---

### Skenario Tandai Harga Hangus

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Tandai Harga Hangus |
| **Actor** | Pemilik |
| **Tujuan** | Menandai harga telur sebagai hangus (tidak aktif lagi) |
| **Kondisi Awal** | Pemilik memilih harga yang masih aktif |
| **Kondisi Akhir** | Status harga berubah menjadi hangus |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Tandai Hangus" pada harga aktif | |
| | 2 Menampilkan konfirmasi "Apakah Anda yakin ingin menandai harga ini hangus?" |
| 3 Klik "Ya, Tandai Hangus" | |
| | 4 Set status harga menjadi "hangus" |
| | 5 Set tanggal_akhir ke tanggal hari ini |
| | 6 Menyimpan perubahan ke database |
| | 7 Harga historical disimpan untuk referensi laporan |
| | 8 Menampilkan pesan "Harga berhasil ditandai hangus" |

#### Skenario Alternatif: Batal penandaan

| Actor | Sistem |
|-------|--------|
| 2 Pemilik klik "Batal" pada konfirmasi | |
| | 3 Tidak ada perubahan pada data harga |
| | 4 Kembali ke halaman daftar harga |

---

### Skenario Lihat History Harga

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat History Harga |
| **Actor** | Pemilik |
| **Tujuan** | Melihat semua harga yang pernah digunakan (aktif & hangus) |
| **Kondisi Awal** | Pemilik berada di halaman harga telur |
| **Kondisi Akhir** | Sistem menampilkan daftar history harga lengkap |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tab "History Harga" | |
| | 2 Menampilkan tabel semua harga (aktif & hangus) |
| | 3 Menampilkan kolom: jenis harga, harga per kg, tanggal berlaku, status |
| | 4 Menampilkan grafik history harga per jenis |
| 5 Pemilik bisa klik detail harga tertentu | |
| | 6 Menampilkan detail harga dengan waktu berlaku |

---

## 6. INPUT PENJUALAN TELUR

Input penjualan telur adalah fitur kritis untuk pemilik dalam mencatat setiap transaksi penjualan yang terjadi. Sistem mendukung penjualan multi-item dalam satu transaksi, artinya dalam satu penjualan dapat menjual telur dengan berbagai jenis harga dan satuan berbeda. Sistem akan melakukan validasi stok untuk memastikan ketersediaan telur cukup sebelum transaksi diproses, dan akan secara otomatis menghitung subtotal dan total harga. Semua transaksi penjualan dijamin aman dan konsisten dengan menggunakan atomic database transaction, sehingga tidak akan ada partial sales atau data yang corrupt. Stok telur juga akan secara otomatis berkurang sesuai dengan jumlah yang dijual. Untuk scenario use case input penjualan dapat dilihat pada tabel berikut.

### Skenario Input Penjualan Multi-Item

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Input Penjualan Telur |
| **Actor** | Pemilik |
| **Tujuan** | Memasukkan transaksi penjualan dengan multiple item |
| **Kondisi Awal** | Pemilik berada di halaman input penjualan |
| **Kondisi Akhir** | Penjualan berhasil disimpan dan stok terkurangi |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Tambah Penjualan" | |
| | 2 Menampilkan form input penjualan |
| 3 Mengisi tanggal jual | |
| 4 Mengisi jam jual | |
| 5 Mengisi nama pembeli | |
| 6 Klik "Tambah Item" | |
| | 7 Menampilkan form input item |
| 8 Memilih jenis harga dari master | |
| | 9 Menampilkan harga per kg dan per butir |
| 10 Memilih satuan jual (butir/kg) | |
| 11 Mengisi jumlah jual | |
| | 12 Auto-konversi ke satuan lain |
| | 13 Auto-calculate subtotal (qty × harga satuan) |
| | 14 Snapshot harga saat jual |
| 15 Klik "Simpan Item" | |
| | 16 Item ditambahkan ke daftar transaksi |
| 17 Bisa tambah item lagi atau selesai | |
| 18 Klik tombol "Proses Penjualan" | |
| | 19 Validasi stok (total butir/kg harus cukup) |
| | 20 Database transaction dimulai |
| | 21 Mengurangi stok_telur sesuai total penjualan |
| | 22 Menyimpan penjualan & semua detail items |
| | 23 Database transaction selesai (commit) |
| | 24 Menampilkan pesan "Penjualan berhasil disimpan" |
| | 25 Menampilkan invoice/summary penjualan |

#### Skenario Alternatif (A): Stok tidak cukup

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan warning "Stok tidak cukup untuk penjualan ini" |
| | 2 Menampilkan stok yang tersedia |
| 3 Pemilik bisa kurangi jumlah atau batal | |

#### Skenario Alternatif (B): Cancel sebelum proses

| Actor | Sistem |
|-------|--------|
| 18 Pemilik klik tombol "Batal" sebelum proses | |
| | 19 Menampilkan konfirmasi pembatalan |
| 20 Pemilik confirm pembatalan | |
| | 21 Item dan form dikosongkan |
| | 22 Kembali ke form input penjualan kosong |

---

### Skenario Edit Penjualan

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Edit Penjualan |
| **Actor** | Pemilik |
| **Tujuan** | Mengubah data penjualan yang sudah diinput |
| **Kondisi Awal** | Pemilik memilih penjualan yang akan diedit |
| **Kondisi Akhir** | Penjualan berhasil diupdate dan stok di-revalidasi |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Edit" pada penjualan | |
| | 2 Menampilkan form edit dengan data penjualan & semua items |
| 3 Mengubah data penjualan atau detail items | |
| 4 Klik tombol "Simpan" | |
| | 5 Validasi stok dengan data baru |
| | 6 Database transaction dimulai |
| | 7 Kembalikan stok lama |
| | 8 Kurangi stok dengan kuantitas baru |
| | 9 Update penjualan & items di database |
| | 10 Database transaction selesai (commit) |
| | 11 Menampilkan pesan "Penjualan berhasil diupdate" |

#### Skenario Alternatif: Stok tidak cukup

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan warning "Stok tidak cukup dengan perubahan baru" |
| 2 Pemilik bisa sesuaikan jumlah atau batal | |

---

### Skenario Lihat Daftar Penjualan

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Daftar Penjualan |
| **Actor** | Pemilik |
| **Tujuan** | Melihat semua transaksi penjualan |
| **Kondisi Awal** | Pemilik berada di halaman penjualan |
| **Kondisi Akhir** | Sistem menampilkan daftar penjualan dengan detail items |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan tabel daftar penjualan (paling baru dulu) |
| | 2 Menampilkan kolom: tanggal, nama pembeli, total harga, jumlah items |
| | 3 Menampilkan pagination (50 per halaman) |
| 4 Pemilik klik detail penjualan | |
| | 5 Menampilkan semua items dengan harga saat jual |
| | 6 Menampilkan total harga keseluruhan |
| 7 Pemilik bisa edit atau hapus dari halaman detail | |

---

## 7. LIHAT LAPORAN PRODUKSI

Lihat laporan produksi adalah fitur untuk pemilik dalam menganalisis data produksi telur dalam bentuk yang lebih visual dan terstruktur. Laporan produksi menampilkan summary dari total produksi, rata-rata HDP, HHP, dan mortality rate per periode waktu, serta visualisasi dalam bentuk tabel dan grafik untuk trend analysis. Pemilik dapat memfilter laporan berdasarkan periode (harian, bulanan, 3 bulan, 6 bulan, atau semua waktu) dan per kandang tertentu. Laporan juga dapat di-export ke format PDF atau Excel untuk keperluan presentasi atau storage dokumen. Data laporan produksi sangat berguna untuk decision making dan evaluasi performa peternakan. Untuk scenario use case lihat laporan produksi dapat dilihat pada tabel berikut.

### Skenario Lihat Laporan Produksi

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Laporan Produksi |
| **Actor** | Pemilik |
| **Tujuan** | Melihat summary & analisis data produksi |
| **Kondisi Awal** | Pemilik berada di halaman laporan produksi |
| **Kondisi Akhir** | Sistem menampilkan laporan dengan tabel & grafik |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan laporan produksi default (bulan ini) |
| | 2 Menampilkan filter: periode, kandang |
| | 3 Menampilkan summary: total butir, total kg, rata-rata HDP, HHP, mortality |
| || 4 Menampilkan tabel produksi per hari dengan metrik |
| | 5 Menampilkan grafik: produksi trend, HDP trend, HHP trend, mortality trend |
| 6 Pemilik memilih periode filter (bulan, 3 bulan, 6 bulan, semua) | |
| 7 Pemilik memilih kandang tertentu (atau semua kandang) | |
| | 8 Update laporan dengan filter baru |
| 9 Pemilik klik tombol "Export PDF" | |
| | 10 Generate PDF laporan |
| | 11 Download file PDF |
| 12 Atau pemilik klik tombol "Export Excel" | |
| | 13 Generate file Excel |
| | 14 Download file Excel |

#### Skenario Alternatif: Belum ada data

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan pesan "Belum ada data produksi untuk periode ini" |
| || 2 Menampilkan link untuk mulai input produksi |

---

## 8. LIHAT LAPORAN PENJUALAN

Lihat laporan penjualan adalah fitur untuk pemilik dalam menganalisis data penjualan dan revenue yang diperoleh. Laporan penjualan menampilkan summary total harga, total jumlah telur terjual, jumlah transaksi, serta breakdown per jenis harga (kandang, grosir, konsumen). Sistem juga menampilkan chart overlay antara produksi dan penjualan sehingga pemilik dapat melihat balance antara kapasitas produksi dengan tingkat penjualan. Pemilik dapat memfilter laporan berdasarkan periode tertentu dan export ke PDF atau Excel. Laporan penjualan ini sangat penting untuk tracking revenue, analisis trend penjualan, dan perencanaan strategi bisnis ke depan. Untuk scenario use case lihat laporan penjualan dapat dilihat pada tabel berikut.

### Skenario Lihat Laporan Penjualan

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Laporan Penjualan |
| **Actor** | Pemilik |
| **Tujuan** | Melihat summary & analisis data penjualan |
| **Kondisi Awal** | Pemilik berada di halaman laporan penjualan |
| **Kondisi Akhir** | Sistem menampilkan laporan dengan tabel & grafik |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan laporan penjualan default (bulan ini) |
| | 2 Menampilkan filter: periode |
| | 3 Menampilkan summary: total harga, total butir, total transaksi |
| | 4 Menampilkan tabel penjualan dengan breakdown jenis harga |
| | 5 Menampilkan grafik: penjualan trend, revenue per jenis harga |
| | 6 Menampilkan chart overlay: produksi vs penjualan |
| 7 Pemilik memilih periode filter | |
| | 8 Update laporan dengan filter baru |
| 9 Pemilik klik tombol "Export PDF" | |
| | 10 Generate dan download PDF laporan |
| 11 Atau pemilik klik tombol "Export Excel" | |
| | 12 Generate dan download Excel laporan |

---

## 9. LIHAT LAPORAN STOK

Lihat laporan stok adalah fitur untuk pemilik dalam memantau dan menganalisis balance stok telur dari waktu ke waktu. Laporan stok balance menampilkan formula perhitungan yang jelas: opening stok + total produksi - total penjualan = closing stok untuk setiap periode. Dengan laporan ini, pemilik dapat memverifikasi bahwa stok telur yang tersimpan di sistem sesuai dengan perhitungan yang akurat dan tidak ada data yang hilang atau corrupt. Laporan stok balance juga membantu dalam inventory management dan perencanaan kapasitas penyimpanan telur. Untuk scenario use case lihat laporan stok dapat dilihat pada tabel berikut.

### Skenario Lihat Laporan Stock Balance

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Laporan Stock Balance |
| **Actor** | Pemilik |
| **Tujuan** | Melihat balance stok (opening + produksi - penjualan = closing) |
| **Kondisi Awal** | Pemilik berada di halaman laporan stok |
| **Kondisi Akhir** | Sistem menampilkan laporan stock balance |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan laporan stock balance |
| | 2 Menampilkan kolom: periode, opening stok, produksi, penjualan, closing stok |
| | 3 Perhitungan: closing = opening + produksi - penjualan |
| 4 Pemilik memilih filter periode | |
| | 5 Update laporan dengan periode baru |

---

## 10. KELOLA USER

Kelola user adalah fitur untuk pemilik dalam mengelola akun pengguna dalam sistem. Pemilik dapat menambahkan user baru, mengubah data user (nama, email, role), dan menghapus user yang tidak lagi diperlukan. Setiap user akan di-assign dengan role spesifik (pemilik atau pekerja) yang menentukan fitur dan data apa saja yang dapat diakses mereka. Jika user adalah pekerja, pemilik juga dapat menentukan kandang mana saja yang akan dijaga oleh pekerja tersebut. Manajemen user yang baik memastikan bahwa setiap anggota tim memiliki akses yang tepat sesuai dengan tanggung jawab mereka. Untuk scenario use case kelola user dapat dilihat pada tabel berikut.

### Skenario Tambah User

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Tambah User |
| **Actor** | Pemilik |
| **Tujuan** | Menambahkan user baru (pemilik atau pekerja) |
| **Kondisi Awal** | Pemilik berada di halaman daftar user |
| **Kondisi Akhir** | User baru berhasil dibuat dengan role yang sesuai |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Tambah User" | |
| | 2 Menampilkan form input user |
| 3 Mengisi nama, email, password | |
| 4 Memilih role (pemilik atau pekerja) | |
| 5 Jika memilih pekerja, pilih kandang yang dijaga | |
| 6 Klik tombol "Simpan" | |
| | 7 Validasi email belum terdaftar |
| | 8 Hash password |
| | 9 Menyimpan user ke database |
| | 10 Assign role ke user (menggunakan Spatie\Permission) |
| | 11 Jika pekerja, assign ke kandang tertentu |
| | 12 Menampilkan pesan "User berhasil dibuat" |

---

### Skenario Edit User

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Edit User |
| **Actor** | Pemilik |
| **Tujuan** | Mengubah data user atau role |
| **Kondisi Awal** | Pemilik memilih user yang akan diedit |
| **Kondisi Akhir** | Data user berhasil diupdate |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Edit" pada user | |
| | 2 Menampilkan form edit dengan data user terkini |
| 3 Mengubah nama, email, atau role | |
| 4 Klik tombol "Simpan" | |
| | 5 Validasi email (jika berubah, harus unique) |
| | 6 Update data user di database |
| | 7 Update role jika ada perubahan |
| | 8 Menampilkan pesan "User berhasil diupdate" |

---

### Skenario Hapus User

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Hapus User |
| **Actor** | Pemilik |
| **Tujuan** | Menghapus user dari sistem |
| **Kondisi Awal** | Pemilik berada di halaman detail user |
| **Kondisi Akhir** | User berhasil dihapus dari sistem |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Hapus" pada user | |
| | 2 Menampilkan konfirmasi penghapusan |
| 3 Klik "Ya, Hapus" | |
| | 4 Menghapus user dan semua permission terkait |
| | 5 Menampilkan pesan "User berhasil dihapus" |

---

## 11. EDIT PROFIL

Edit profil adalah fitur untuk pemilik dan pekerja dalam mengelola informasi pribadi akun mereka. Setiap pengguna dapat mengubah nama dan email mereka sendiri kapan saja melalui halaman profil. Pengguna juga dapat mengubah password mereka dengan memasukkan password lama terlebih dahulu untuk verifikasi keamanan. Fitur edit profil ini memastikan bahwa data personal pengguna selalu up-to-date dan password tetap aman. Untuk scenario use case edit profil dapat dilihat pada tabel berikut.

### Skenario Edit Profil

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Edit Profil |
| **Actor** | Pemilik, Pekerja |
| **Tujuan** | Mengubah data profil pengguna |
| **Kondisi Awal** | Pengguna berada di halaman profil |
| **Kondisi Akhir** | Data profil berhasil disimpan |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan halaman profil dengan data terkini |
| 2 Pengguna klik tombol "Edit Profil" | |
| | 3 Menampilkan form edit profil |
| 4 Pengguna mengubah nama atau email | |
| 5 Pengguna klik tombol "Simpan" | |
| | 6 Validasi email (jika berubah, harus unique) |
| | 7 Update data profil ke database |
| | 8 Menampilkan pesan "Profil berhasil diupdate" |

---

### Skenario Ganti Password

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Ganti Password |
| **Actor** | Pemilik, Pekerja |
| **Tujuan** | Mengganti password pengguna |
| **Kondisi Awal** | Pengguna berada di halaman profil |
| **Kondisi Akhir** | Password berhasil diubah |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Pengguna klik tombol "Ganti Password" | |
| | 2 Menampilkan form ganti password |
| 3 Mengisi password lama | |
| 4 Mengisi password baru | |
| 5 Mengisi konfirmasi password baru | |
| 6 Klik tombol "Simpan" | |
| | 7 Validasi password lama sesuai |
| | 8 Validasi password baru minimal 8 karakter |
| | 9 Hash password baru |
| | 10 Update password di database |
| | 11 Menampilkan pesan "Password berhasil diubah" |

#### Skenario Alternatif: Password lama salah

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan error "Password lama tidak sesuai" |

---

## 12. KELOLA SETTING SISTEM

Kelola setting sistem adalah fitur untuk pemilik dalam mengatur konfigurasi dan parameter sistem sesuai dengan kebutuhan spesifik peternakan. Setting sistem mencakup berbagai parameter seperti rasio konversi butir telur ke kg, yang digunakan untuk auto-convert satuan input ketika pekerja atau pemilik input data. Pemilik hanya dapat mengedit nilai dari setting yang sudah ada, tidak dapat membuat setting baru. Setiap setting dilengkapi dengan keterangan untuk menjelaskan fungsi dan pentingnya setting tersebut. Kemampuan untuk customize setting memastikan bahwa sistem dapat disesuaikan dengan karakteristik unik dari setiap peternakan. Untuk scenario use case kelola setting dapat dilihat pada tabel berikut.

### Skenario Edit Setting

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Edit Setting Sistem |
| **Actor** | Pemilik |
| **Tujuan** | Mengubah konfigurasi sistem |
| **Kondisi Awal** | Pemilik berada di halaman pengaturan |
| **Kondisi Akhir** | Setting berhasil diupdate |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan daftar setting dengan nilai terkini |
| | 2 Menampilkan keterangan untuk setiap setting |
| 3 Pemilik mengubah nilai setting (misal: konversi butir per kg) | |
| 4 Klik tombol "Simpan" | |
| | 5 Validasi nilai sesuai tipe_data |
| | 6 Update value setting di database |
| | 7 Menampilkan pesan "Setting berhasil diupdate" |

#### Skenario Alternatif: Nilai tidak valid

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan error "Nilai setting harus berupa angka" |

---

## 13. LOGOUT

Logout adalah fitur untuk pemilik dan pekerja dalam keluar dari sistem dengan aman. Ketika pengguna mengklik tombol logout, sistem akan menghapus session pengguna dan mengarahkan kembali ke halaman login. Fitur logout yang tepat memastikan bahwa tidak ada orang lain yang dapat mengakses akun pengguna setelah mereka selesai bekerja, terutama penting dalam lingkungan dengan multiple user atau public computer. Untuk scenario use case logout dapat dilihat pada tabel berikut.

### Skenario Logout

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Logout |
| **Actor** | Pemilik, Pekerja |
| **Tujuan** | Keluar dari sistem |
| **Kondisi Awal** | Pengguna sudah login |
| **Kondisi Akhir** | Pengguna berhasil logout dan redirect ke halaman login |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Pengguna klik tombol "Logout" | |
| | 2 Menghapus session pengguna |
| | 3 Redirect ke halaman login |
| || 4 Menampilkan pesan "Anda berhasil logout" |

---

## 14. LIHAT DETAIL KANDANG & STATISTIK

Lihat detail kandang & statistik adalah fitur untuk pemilik dalam melihat informasi lengkap dan statistik performa dari setiap kandang. Halaman detail kandang menampilkan informasi dasar kandang (nama, jumlah ayam, PIC, status) serta statistik produksi seperti total produksi, rata-rata HDP, HHP, dan mortality rate. Pemilik juga dapat melihat tracking jumlah ayam yang mati (all-time, per periode, atau sebelum periode tertentu) untuk analisis kesehatan kandang. Sistem juga menampilkan grafik trend produksi dan KPI metrik untuk membantu pemilik mengidentifikasi pattern dan issue di kandang tertentu. Untuk scenario use case lihat detail kandang dapat dilihat pada tabel berikut.

### Skenario Lihat Detail Kandang

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Detail Kandang & Statistik |
| **Actor** | Pemilik |
| **Tujuan** | Melihat detail kandang beserta statistik produksi |
| **Kondisi Awal** | Pemilik memilih kandang dari daftar |
| **Kondisi Akhir** | Sistem menampilkan detail dan statistik kandang |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| 1 Klik tombol "Detail" pada kandang | |
| | 2 Menampilkan informasi kandang (nama, jumlah ayam, PIC, status) |
| | 3 Menampilkan statistik: total produksi, rata-rata HDP, HHP, mortality |
| | 4 Menampilkan tracking ayam mati (all-time, periode, sebelum periode) |
| 5 Pemilik memilih filter periode analitik | |
| | 6 Update statistik sesuai periode |
| | 7 Menampilkan grafik: produksi trend, KPI metrik |

---

## 15. LIHAT STOK REAL-TIME

Lihat stok real-time adalah fitur untuk pemilik dan pekerja dalam memantau ketersediaan telur terkini yang siap untuk dijual. Stok telur ditampilkan dalam dua satuan (butir dan kg) untuk memberikan informasi yang lengkap sesuai dengan kebutuhan. Sistem melakukan kalkulasi stok secara real-time berdasarkan rumus: opening stok periode sebelumnya + total produksi periode ini - total penjualan periode ini = stok saat ini. Stok akan terupdate otomatis setiap kali ada input produksi baru atau transaksi penjualan terjadi. Informasi stok real-time yang akurat sangat penting untuk decision making dalam pricing, pemasaran, dan perencanaan operasional. Untuk scenario use case lihat stok real-time dapat dilihat pada tabel berikut.

### Skenario Lihat Stok Real-Time

| Aspek | Deskripsi |
|-------|-----------|
| **Nama** | Lihat Stok Real-Time |
| **Actor** | Pemilik, Pekerja |
| **Tujuan** | Melihat stok telur terkini dalam satuan butir & kg |
| **Kondisi Awal** | Pengguna membuka dashboard atau halaman stok |
| **Kondisi Akhir** | Sistem menampilkan stok terkini yang akurat |

#### Skenario Utama (basic flow)

| Actor | Sistem |
|-------|--------|
| | 1 Menampilkan stok telur terkini |
| | 2 Menampilkan stok dalam satuan butir & kg |
| | 3 Melakukan kalkulasi real-time dari: |
| || a. Opening stok periode sebelumnya |
| || b. Total produksi periode ini |
| || c. Total penjualan periode ini |
| | 4 Formula: stok = opening + produksi - penjualan |
| | 5 Update otomatis setiap ada produksi atau penjualan |

---

**TOTAL USE CASE SCENARIO: 15 USE CASE LENGKAP**

---

*Dokumen ini mencakup semua use case scenario untuk sistem Hans Jaya Poultry Farm Management System*  
*Setiap use case dirancang berdasarkan actual implementation dan fitur yang sudah verified*

*Last Updated: 8 April 2026*