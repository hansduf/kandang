# Draw.io Activity Diagrams
## Hans Jaya Poultry Farm Management System

**Format:** `.drawio` files (XML-based) untuk preview & edit di draw.io editor

---

## 📋 Daftar Activity Diagram

| No | File | Deskripsi Singkat |
|:--:|------|-----------|
| 1 | `01-login.drawio` | User input email & password → Sistem validasi → Tampilkan dashboard sesuai role (Pemilik/Pekerja). Jika gagal ada opsi retry. |
| 2 | `02-lihat-dashboard.drawio` | User klik Dashboard → Sistem load data default (bulan ini) & query stok, penjualan, produksi → Render dashboard. User bisa filter periode untuk update data. |
| 3 | `03-kelola-harga-telur.drawio` | Pemilik buka halaman harga → Bisa Tambah harga baru (auto-hangus harga lama), Edit harga aktif, Tandai harga hangus, atau Lihat history harga lengkap. |
| 4 | `04-input-produksi-telur.drawio` | Pekerja input produksi: tanggal, satuan, jumlah telur, ayam hidup/mati → Sistem auto-konversi satuan & auto-calculate HDP/HHP/Mortality → Simpan & update stok. |
| 5 | `05-input-penjualan-telur.drawio` | Pemilik klik tambah penjualan → Input tanggal/jam/pembeli → Loop tambah item (pilih harga, satuan, qty) → Validasi stok → Jika cukup: atomic transaction, kurangi stok, simpan, invoice. Jika tidak: warning. |
| 6 | `06-lihat-laporan-penjualan.drawio` | Pemilik buka laporan penjualan → Load default (bulan ini) dengan summary, breakdown per harga, grafik → Bisa filter periode/kandang → Export PDF/Excel. |
| 7 | `07-lihat-laporan-produksi.drawio` | Pemilik buka laporan produksi → Load default dengan summary total/rata-rata, tabel per hari, grafik trend → Filter periode & kandang → Export PDF/Excel. |
| 8 | `08-kelola-kandang.drawio` | Pemilik buka kelola kandang → Daftar kandang → Bisa Tambah kandang baru (input nama, ayam, PIC), Edit data kandang, atau Hapus dengan cascade delete. |
| 9 | `09-lihat-stok-real-time.drawio` | User buka halaman stok → Sistem kalkulasi real-time (Opening + Produksi - Penjualan) → Convert ke satuan Butir & KG → Display dengan last updated time. Auto-update saat ada input produksi/penjualan baru. |
| 10 | `10-kelola-user.drawio` | Pemilik buka kelola user → Daftar user → Bisa Tambah user (input nama, email, password, role, kandang jika pekerja), Edit user, atau Hapus user dengan permission terkait. |
| 11 | `11-pengaturan-sistem.drawio` | Pemilik buka pengaturan sistem → Query semua setting → Display dengan nilai & keterangan → Pilih setting untuk diubah → Input nilai baru → Validasi → Update DB. |
| 12 | `12-alur-keseluruhan-sistem.drawio` | User login → Dashboard sesuai role. Pemilik akses: Kandang/Harga/Penjualan/Laporan/User/Setting/Profil. Pekerja akses: Produksi/Dashboard/Profil. Loop hingga logout. |

---

## 🚀 Cara Preview & Edit

### Online (Recommended):
1. Kunjungi [draw.io Online Editor](https://app.diagrams.net/)
2. Klik **File → Open** → **My Computer** (atau drag-drop file)
3. Pilih file `.drawio` dari folder `drawio/`
4. Diagram akan terbuka dan siap diedit
5. Setiap perubahan otomatis tersimpan

### Offline (dengan installation):
1. Download [draw.io Desktop](https://github.com/jgraph/drawio-desktop/releases)
2. Install aplikasi
3. Buka file `.drawio` langsung di aplikasi
4. Edit dan simpan

### VS Code Integration:
1. Install extension: `Draw.io Integration` (id: `hediet.vscode-drawio`)
2. Open `.drawio` file
3. Visual editor akan muncul di samping
4. Double-click untuk edit

---

## 📝 Struktur File Draw.io

Setiap file `.drawio` mengikuti format XML (mxGraphModel):
- **Swimlanes**: Menampilkan actor/role (Pemilik, Pekerja, Sistem, User)
- **Activities**: Rounded rectangles untuk aksi/proses
- **Decisions**: Diamond shapes untuk conditional logic (if-then-else)
- **Start/End**: Green circle (start), Red circle (end)
- **Connectors**: Arrows menunjukkan alur flow

---

## 🔄 Sinkronisasi dengan PlantUML & Use Case

Semua file `.drawio` ini adalah alih rupa dari file `.puml` di folder `../plant/`:
- **Logic identik**: Flow, swimlanes, decisions sama
- **Format beda**: PlantUML (text-based) vs Draw.io (visual/XML-based)
- **Use Case reference**: Lihat `USE_CASE_SCENARIO.md` untuk deskripsi lengkap setiap use case

Panduan cross-reference:
| Draw.io File | PlantUML File | Use Case ID |
|--|--|--|
| 01-login.drawio | 01-login.puml | UC-001: Login |
| 02-lihat-dashboard.drawio | 02-lihat-dashboard.puml | UC-002: Dashboard (Pemilik/Pekerja) |
| 03-kelola-harga-telur.drawio | 03-kelola-harga-telur.puml | UC-005: Kelola Harga Telur |
| 04-input-produksi-telur.drawio | 04-input-produksi-telur.puml | UC-004: Input Produksi Telur |
| 05-input-penjualan-telur.drawio | 05-input-penjualan-telur.puml | UC-006: Input Penjualan Multi-Item |
| 06-lihat-laporan-penjualan.drawio | 06-lihat-laporan-penjualan.puml | UC-008: Laporan Penjualan |
| 07-lihat-laporan-produksi.drawio | 07-lihat-laporan-produksi.puml | UC-009: Laporan Produksi |
| 08-kelola-kandang.drawio | 08-kelola-kandang.puml | UC-003: Kelola Kandang |
| 09-lihat-stok-real-time.drawio | 09-lihat-stok-real-time.puml | UC-010: View Stok Real-Time |
| 10-kelola-user.drawio | 10-kelola-user.puml | UC-011: Kelola User |
| 11-pengaturan-sistem.drawio | 11-pengaturan-sistem.puml | UC-012: Pengaturan Sistem |
| 12-alur-keseluruhan-sistem.drawio | 12-alur-keseluruhan-sistem.puml | UC-000: Main Flow |

---

## ✅ Development & Testing

### Format Validation:
- Setiap file `.drawio` telah divalidasi XML structure
- Kompatibel dengan draw.io 14.0+
- Tested dengan online editor (`app.diagrams.net`)

### Customization:
Jika perlu memodifikasi:
1. Open di draw.io editor
2. Edit swimlanes, activities, connectors
3. **Export** ke format lain jika diperlukan:
   - PNG/SVG (untuk dokumentasi)
   - PDF (untuk cetak/presentasi)
   - PlantUML (convert back ke text format)

### Export Commands (draw.io Desktop):
```bash
# Export ke PNG
./drawio -x 01-login.drawio -o 01-login.png

# Export ke PDF
./drawio -x 01-login.drawio -o 01-login.pdf

# Export ke SVG
./drawio -x 01-login.drawio -o 01-login.svg
```

---

## 📚 Related Documentation

- **Requirements**: [`../../DAFTAR_KEBUTUHAN_FINAL.md`](../../DAFTAR_KEBUTUHAN_FINAL.md) - 52 business requirements
- **Use Cases**: [`../../USE_CASE_SCENARIO.md`](../../USE_CASE_SCENARIO.md) - 17 detailed scenarios
- **PlantUML Diagrams**: [`../plant/INDEX.md`](../plant/INDEX.md) - Alternative text-based format
- **Code Reference**: Lihat controllers di `app/Http/Controllers/`

---

## 🎯 Capstone Project Checklist

- ✅ 12 Activity Diagrams (draw.io format)
- ✅ Synchronized dengan USE_CASE_SCENARIO.md
- ✅ Swimlane structure (Pemilik/Pekerja/Sistem)
- ✅ Decision flows & error handling
- ✅ Role-based access control documented
- ✅ Real-time calculations captured
- ✅ Atomic transactions documented
- ✅ Cross-reference dengan PlantUML
- ✅ Ready for presentation & capstone submission

---

## 📧 Questions & Support

Untuk pertanyaan atau modifikasi:
1. Edit file `.drawio` langsung di draw.io editor
2. Export hasilnya ke format yang diperlukan
3. Update documentation sesuai kebutuhan

**Last Updated**: April 13, 2026  
**Format Version**: Draw.io 14.0+ compatible (XML mxGraphModel)
