# 🗂️ PANDUAN PENGUJIAN CEPAT - Quick Reference

## PRE-TESTING CHECKLIST

### Persiapan Lingkungan Pengujian
- [ ] Buka file `PENGUJIAN_SISTEM_LENGKAP.md`
- [ ] Siapkan template hasil pengujian
- [ ] Login ke sistem dengan akun test
- [ ] Pastikan database dalam kondisi clean (gunakan script reset jika perlu)
- [ ] Siapkan browser dev tools (F12) untuk monitoring

### Data Test yang Diperlukan
```
User Test:
- Owner: owner@test.local / password123
- Worker: worker@test.local / password123

Test Data:
- Kandang: Kandang A, B, C (sudah ada atau buat baru)
- Produksi: Ada minimal 2 record per kandang
- Harga: Ada harga aktif (default: Rp. 500/butir)
- Stok: Pastikan stok ada dan terlihat
```

### Tools yang Dibutuhkan
- [ ] Browser untuk UI testing
- [ ] Postman/Thunder Client untuk API testing (opsional)
- [ ] Database tool untuk query (opsional)
- [ ] Screenshot tool (built-in atau tool pihak ketiga)

---

## STRUKTUR TESTING EXECUTION

### Fase 1: User Management (30 menit)
```
┌─ 1.1 Login Testing (10 menit)
│  ├─ Test Case 1.1.1: Valid login
│  ├─ Test Case 1.1.2: Email tidak terdaftar
│  ├─ Test Case 1.1.3: Password salah
│  ├─ Test Case 1.1.4: Email kosong
│  └─ Test Case 1.1.5: Password kosong
│
├─ 1.2 Logout Testing (10 menit)
│  ├─ Test Case 1.2.1: Logout normal
│  └─ Test Case 1.2.2: Cek session setelah logout
│
└─ 1.3 Registrasi (10 menit)
   ├─ Test Case 1.3.1: Registrasi baru
   ├─ Test Case 1.3.2: Email duplikat
   ├─ Test Case 1.3.3: Password tidak cocok
   ├─ Test Case 1.3.4: Field kosong
   └─ Test Case 1.3.5: Email invalid
```

### Fase 2: Kandang Management (40 menit)
```
┌─ 2.1 Tambah Kandang (10 menit)
│  ├─ Test Case 2.1.1-4
│  └─ Validasi setiap field
│
├─ 2.2 Edit Kandang (10 menit)
│  ├─ Test Case 2.2.1-2
│  └─ Cek update di DB
│
├─ 2.3 Hapus Kandang (10 menit)
│  ├─ Test Case 2.3.1-3
│  └─ Cek permission worker
│
└─ 2.4 Lihat Daftar (10 menit)
   ├─ Test Case 2.4.1-3
   └─ Cek paginasi & search
```

### Fase 3: Produksi & Penjualan (60 menit)
```
┌─ 3.1 Produksi Telur (20 menit)
│  ├─ Test Case 3.1.1-5
│  └─ Cek stok bertambah
│
├─ 3.2-3.3 Edit & Hapus Produksi (15 menit)
│  ├─ Test Case 3.2.1-3
│  ├─ Test Case 3.3.1-3
│  └─ Cek stok berkurang
│
└─ 4.1-4.4 Penjualan (25 menit)
   ├─ Test Case 4.1.1-5: Buat penjualan
   ├─ Test Case 4.2.1-3: Edit penjualan
   ├─ Test Case 4.3.1-3: Hapus penjualan
   └─ Test Case 4.4.1-3: Laporan
```

### Fase 4: Permission Testing (20 menit)
```
┌─ 5.1 Owner Permission (10 menit)
│  ├─ Test Case 5.1.1-5
│  └─ Verify semua akses owner
│
└─ 5.2 Worker Permission (10 menit)
   ├─ Test Case 5.2.1-6
   └─ Verify restriction worker
```

### Fase 5: Stock & Pricing (30 menit)
```
┌─ 6. Stock Calculation (15 menit)
│  ├─ Test Case 6.1.1-7
│  ├─ Test Case 6.2.1-2
│  └─ Validasi perhitungan
│
└─ 7. Pricing Management (15 menit)
   ├─ Test Case 7.1.1-4
   ├─ Test Case 7.2-7.4
   └─ Cek riwayat harga
```

### Total Waktu Estimasi: ~3 jam (180 menit)

---

## TESTING PER MODULE - STEP BY STEP

### Module 1: LOGIN TEST (Test Case 1.1.1)

**Langkah Eksekusi:**
1. Buka aplikasi
2. Klik tombol Login
3. Masukkan: 
   - Email: `owner@test.local`
   - Password: `password123`
4. Klik "Login"

**Verifikasi:**
```
✓ Page berhasil redirect ke dashboard
✓ User profile menampilkan nama user
✓ Session aktif (tidak ada error)
✓ Sidebar menu tersedia sesuai role
```

**Jika PASSED:** Mark ✅ di template hasil  
**Jika FAILED:** Dokumentasikan error & screenshot

---

### Module 2: TAMBAH KANDANG (Test Case 2.1.1)

**Persiapan:**
- [ ] Login sebagai Owner
- [ ] Navigasi ke menu Kandang

**Langkah Eksekusi:**
1. Klik tombol "Tambah Kandang"
2. Isi form:
   ```
   Nama Kandang: Kandang Test
   Lokasi: Area A
   Kapasitas: 1000
   PIC: (pilih user owner)
   ```
3. Klik "Simpan"

**Verifikasi:**
```
✓ Muncul pesan sukses "Kandang berhasil ditambahkan"
✓ Redirect ke daftar kandang
✓ Data baru tampil di list
✓ Query DB: SELECT * FROM kandang WHERE nama = 'Kandang Test'
  → Harus muncul 1 record
```

---

### Module 3: TAMBAH PRODUKSI (Test Case 3.1.1)

**Persiapan:**
- [ ] Login sebagai Worker
- [ ] Navigasi ke menu Produksi

**Langkah Eksekusi:**
1. Klik "Tambah Produksi"
2. Isi form:
   ```
   Kandang: Kandang Test
   Tanggal: (hari ini)
   Jumlah Butir: 500
   Jumlah Kg: 31.25
   HDP: 95
   HHP: 92
   Mortalitas: 3
   ```
3. Klik "Simpan"

**Verifikasi:**
```
✓ Pesan sukses "Produksi berhasil tercatat"
✓ Stok kandang naik 500 butir
✓ Query DB: SELECT jumlah_butir FROM stok_telur 
  WHERE kandang_id = 1
  → Harus bertambah 500 dari stok sebelumnya
```

---

### Module 4: BUAT PENJUALAN (Test Case 4.1.1)

**Persiapan:**
- [ ] Login sebagai Owner
- [ ] Ada stok minimal 100 butir di kandang

**Langkah Eksekusi:**
1. Navigasi ke menu Penjualan
2. Klik "Tambah Penjualan"
3. Isi form:
   ```
   Pembeli: Toko ABC
   Tanggal: (hari ini)
   ```
4. Klik "Tambah Item"
5. Isi detail:
   ```
   Kandang: Kandang Test
   Jumlah: 100 butir
   Harga: 500/butir
   Subtotal: 50000 (auto)
   ```
6. Klik "Simpan Penjualan"

**Verifikasi:**
```
✓ Penjualan berhasil dibuat
✓ Stok berkurang 100 butir
✓ Invoice/No Ref tergenerasi
✓ Query: SELECT jumlah_butir FROM stok_telur 
  WHERE kandang_id = 1
  → Harus berkurang 100
```

---

### Module 5: TEST PERMISSION WORKER (Test Case 5.2.1-6)

**Persiapan:**
- [ ] Login sebagai Worker (worker@test.local)

**Test Workflow:**
```
1. Navigasi → Kandang
   ✓ Bisa lihat daftar
   ✗ Tidak ada tombol Edit/Hapus
   
2. Navigasi → Produksi
   ✓ Ada tombol Tambah
   ? Lihat apakah bisa edit/hapus
   
3. Navigasi → Penjualan (Jika terlihat)
   ✗ Tidak bisa akses atau readonly
   
4. Navigasi → Harga
   ✗ Tidak ada akses atau readonly
   
5. Try akses URL: /admin/penjualan
   ✗ Harus error 403 atau redirect
```

**Dokumentasi:**
- Screenshot setiap fitur
- Catat akses level per menu
- Verifikasi sesuai matrix permission

---

### Module 6: STOCK CALCULATION (Test Case 6.1.1-6.1.7)

**Test Scenario:**

Kondisi Awal:
```
Kandang A Stok: 100 butir
```

**Proses:**
1. ✅ Tambah Produksi 200 butir → Stok = 300
2. ✅ Tambah Produksi 150 butir → Stok = 450
3. ✅ Buat Penjualan 100 butir → Stok = 350
4. ✅ Buat Penjualan 50 butir → Stok = 300

**Final Verification:**
```
Stok dashboard: 300 ✓
Query DB stok: 300 ✓
Laporan stok harian: 300 ✓
Riwayat: 100 + 200 + 150 - 100 - 50 = 300 ✓
```

---

### Module 7: PRICING HISTORY (Test Case 7.4.2-7.4.3)

**Scenario:**

**Timeline:**
```
2026-04-01: Harga 1 = 500/butir (Aktif)
2026-04-15: Penjualan 1 = 100 butir @ 500 (OK)
2026-04-20: Harga 2 = 600/butir (Aktif baru)
2026-04-22: Penjualan 2 = 100 butir @ 600 (OK)
```

**Verificatio:**
```
✓ Penjualan 1 tetap 500/butir (tidak ikut harga baru)
✓ Penjualan 2 gunakan 600/butir (harga aktif saat itu)
✓ Riwayat harga lengkap menampilkan keduanya
✓ Edit harga v1 tidak bisa (sudah ada transaksi)
```

---

## AUTOMATED TESTING CHECKLIST

Jika menggunakan script automated testing:

```bash
# Run full test suite
./scripts/test-api.sh '/api/kandang' 'GET' ''
./scripts/test-api.sh '/api/produksi' 'GET' ''
./scripts/test-api.sh '/api/penjualan' 'GET' ''

# Generate report
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=hasil_pengujian_automated.md

# Check report
cat hasil_pengujian_automated.md
```

---

## COMMON ISSUE TROUBLESHOOTING

### ❌ Test Gagal: Invalid data validation

**Gejala:** Form menerima input invalid (misal kapasitas = "abc")

**Action:**
1. Screenshot form
2. Cek browser console (F12) untuk error
3. Dokumentasikan di bug report
4. Severity: 🟠 HIGH

---

### ❌ Test Gagal: Stok tidak update

**Gejala:** Tambah produksi tapi stok tidak naik

**Diagnosis:**
```php
// Query di Tinker:
$ Kandang::find(1)->stokTelur
$ app('StockService')->calculateAvailableStock(1)
$ DB::select("SELECT * FROM stok_telur WHERE kandang_id=1")
```

**Action:**
1. Bandingkan hasil query
2. Cek di database apakah produksi tercatat
3. Dokumentasikan perbedaan
4. Severity: 🔴 CRITICAL

---

### ❌ Test Gagal: Worker bisa akses admin

**Gejala:** Worker bisa edit harga atau hapus kandang

**Action:**
1. Login ulang dengan browser baru (clear cache)
2. Cek role user: `/tinker → User::find(id)->roles`
3. Cek middleware di route: `grep -r "role:pemilik" routes/`
4. Report sebagai Security Issue
5. Severity: 🔴 CRITICAL

---

### ❌ Test Gagal: Laporan tidak generate

**Gejala:** Export PDF/Excel tidak download

**Action:**
1. Buka Dev Tools → Network tab
2. Coba export dan lihat response
3. Check file permissions: `chmod 777 storage/`
4. Check disk space: `df -h`
5. Severity: 🟡 MEDIUM

---

## AFTER-TESTING

### Documentation
- [ ] Semua hasil testing terisi di template
- [ ] Bug/Issue didokumentasikan dengan jelas
- [ ] Screenshot evidence tersimpan
- [ ] Sign-off sudah ada

### Next Steps
- [ ] Review hasil dengan QA Lead
- [ ] Assign bugs ke development team
- [ ] Set deadline untuk bug fix
- [ ] Schedule re-testing jika ada bug

### Archive
- [ ] Simpan file hasil pengujian
- [ ] Push ke repository: `git add pengujian/ && git commit -m "Test results - [date]"`
- [ ] Update status di project tracker

---

## CONTACT & ESCALATION

**Issues & Questions:**
- QA Lead: [nama & contact]
- Dev Lead: [nama & contact]  
- PM: [nama & contact]

**Report Format untuk Bug:**
```
Subject: [TEST-XXX] Bug name - Module
Assignee: Dev Lead
Severity: [CRITICAL/HIGH/MEDIUM/LOW]
Steps: 1. ... 2. ... 3. ...
Expected: ...
Actual: ...
```

---

*Last Updated: April 2026*  
*Format: Black Box Testing - User Acceptance Criteria*
