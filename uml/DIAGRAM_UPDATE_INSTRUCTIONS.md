# Class Diagram Update Instructions

**Date:** April 22, 2026  
**Status:** Ready for manual update in Draw.io  
**Reference:** [class_diagram.md](./class_diagram.md) - Now synchronized with actual database schema

---

## Overview

The markdown documentation has been updated to reflect the actual database schema from `hans_jaya_poultry.sql`. This file provides step-by-step instructions to update the visual `class_diagram.drawio` file to match.

---

## Changes Required

### 1. **User Class Updates**

**Add/Update the following attributes:**

```
- id: bigint
- name: string
- username: string ← NEW
- email: string
- email_verified_at: timestamp (nullable) ← NEW
- password: string
- role: enum[pemilik|pekerja] ← NEW
- kandang_id: bigint (FK)
- remember_token: string (nullable) ← NEW
- created_at: timestamp
- updated_at: timestamp
```

**Update Methods:**
```
+ hasRole(role): boolean ← MODIFY SIGNATURE
+ getKandang(): Kandang
+ getProduksi(): Collection<ProduksiTelur>
+ getPenjualan(): Collection<Penjualan>
+ getHarga(): Collection<HargaTelur>
```

**Color:** Keep current green (#E8F5E9) with 2pt border

---

### 2. **Kandang Class Updates**

**Update attributes to show column types (bigint, int):**

```
- id: bigint
- nama_kandang: string
- jumlah_ayam: int
- keterangan: text (nullable)
- status: enum[aktif|nonaktif]
- pic_id: bigint (FK)
- created_at: timestamp
- updated_at: timestamp
```

**Update Methods:**
```
+ getPic(): User ← NEW
+ tambahProduksi(data): ProduksiTelur
+ getProduksi(): Collection<ProduksiTelur>
+ getStokTerkini(): tuple(int, decimal) ← CHANGE RETURN TYPE
+ calculateAyamHidup(): int ← NEW
+ setStatus(status): void
```

---

### 3. **ProduksiTelur Class Updates** ⚠️ **IMPORTANT**

This class has significant schema additions. **Expand size if needed.**

**Replace attributes with:**

```
- id: bigint
- kandang_id: bigint (FK)
- user_id: bigint (FK)
- tanggal_produksi: date
- satuan_input: enum[butir|kg] ← NEW
- jumlah_input: decimal ← NEW
- jumlah_butir: int
- jumlah_kg: decimal
- ayam_mati: int ← NEW
- catatan: text (nullable) ← NEW
- ayam_hidup: int ← NEW
- hdp: decimal
- hhp: decimal
- mortality: decimal
- keterangan: text (nullable)
- created_at: timestamp
- updated_at: timestamp
```

**Update Methods:**
```
+ getKandang(): Kandang
+ getInputer(): User ← RENAME FROM getUser()
+ konversiKeButir(): int
+ konversiKeKg(): decimal
+ calculateMetrics(): array ← NEW
+ getMetricsProduction(): array
```

**Color:** Keep green (#E8F5E9) with 2pt border  
**Note:** This class will be taller — expand canvas if needed

---

### 4. **HargaTelur Class Updates**

**Add/Modify attributes:**

```
- id: bigint
- jenis_harga: enum[kandang|grosir|konsumen]
- harga_per_kg: decimal
- harga_per_butir: decimal
- tanggal_berlaku: date
- status: enum[aktif|hangus]
- tanggal_akhir: date (nullable) ← NEW
- user_id: bigint (FK)
- keterangan: text (nullable)
- created_at: timestamp
- updated_at: timestamp
```

**Update Methods:**
```
+ setHargaBaru(data): void
+ isAktif(): boolean
+ getHargaSaatIni(): HargaTelur ← CHANGE RETURN TYPE
+ getNilaiHarga(satuan): decimal
+ expireOldPrices(): void ← NEW (was hangusHargaLama)
```

**Color:** Keep pink (#FCE4EC) with 2pt border

---

### 5. **Penjualan Class — Minor Updates**

**Update attribute types to bigint/decimal:**

```
- id: bigint
- user_id: bigint (FK)
- tanggal_jual: date
- nama_pembeli: string (nullable) ← ADD NULLABLE
- total_harga: decimal
- keterangan: text (nullable)
- created_at: timestamp
- updated_at: timestamp
```

**Add Method:**
```
+ commit(): void ← NEW
```

**Color:** Keep blue (#E3F2FD)

---

### 6. **DetailPenjualan Class** ⚠️ **MAJOR UPDATE**

This class has **4 new price snapshot fields**. **Likely needs size expansion.**

**Replace data types and add fields:**

```
- id: bigint
- penjualan_id: bigint (FK)
- harga_telur_id: bigint (FK)
- satuan_jual: enum[butir|kg]
- jumlah_jual: decimal
- jumlah_butir: int
- jumlah_kg: decimal
- jam_penjualan: time (nullable) ← NEW (was in schema but missing)
- harga_satuan: decimal
- harga_per_butir_saat_jual: decimal ← NEW (audit trail)
- harga_per_kg_saat_jual: decimal ← NEW (audit trail)
- subtotal: decimal
- created_at: timestamp
- updated_at: timestamp
```

**Update Methods:**
```
+ getPenjualan(): Penjualan
+ getHarga(): HargaTelur
+ hitungSubtotal(): decimal
+ getAuditTrail(): array ← RENAME FROM getAuditTrail (keep same)
+ isImmutable(): boolean ← NEW
```

**Stereotype:** Keep `«immutable»` label at top  
**Color:** Keep orange (#FFF3E0) with 2pt border  
**Note:** Class will be taller due to 4 new fields

---

### 7. **StokTelur Class — Minor Updates**

**Update attribute data types:**

```
- id: bigint
- stok_butir: int
- stok_kg: decimal
- updated_at: timestamp
```

**Update Methods:**
```
+ getStokButir(): int ← RENAME FROM hitungStok component
+ getStokKg(): decimal ← RENAME FROM hitungStok component
+ isStokCukup(jumlah): boolean
+ getStokHistory(): Collection ← NEW
```

**Remove Methods:**
```
- hitungStok(): void ← DELETE (now computed property)
- kurangiStok(jumlah): void ← DELETE (immutable)
- tambahStok(jumlah): void ← DELETE (immutable)
```

**Color:** Keep purple (#F3E5F5)

---

### 8. **Pengaturan Class — Update Content**

**Attributes stay same (already accurate):**

```
- id: bigint
- kunci: string (unique)
- nilai: string
- tipe_data: enum[string|integer|decimal|boolean]
- keterangan: text (nullable)
- updated_at: timestamp
```

**Update Methods:**
```
+ get(kunci): mixed
+ set(kunci, nilai, tipe): void ← UPDATE SIGNATURE
+ getAsInteger(kunci): int ← KEEP
+ getAsDecimal(kunci): decimal ← KEEP
+ getAsBoolean(kunci): boolean ← KEEP
+ getKonversiRatio(): int ← KEEP
```

**Add a note box somewhere indicating:**
```
Current Configuration:
• konversi_butir_per_kg: 16
```

**Color:** Keep purple (#F3E5F5)

---

### 9. **StockService Class**

**Create NEW class if not present.** If present, update as follows:

**Attributes:** None (stateless service)

**Methods:**
```
+ calculateAvailableStock(): tuple<int, decimal>
+ calculateStockPerKandang(kandang_id): tuple
+ calculateStockByDate(start, end): array
+ updateStokAfterSales(penjualan_id): void
+ validateStokCukup(detail): boolean
+ convertUnits(value, from, to): decimal
+ getKonversiRatio(): int
```

**Color:** Suggest yellow/orange accent (#FFEB3B) with 2pt border  
**Stereotype:** `«service»`

---

### 10. **Relationship Updates**

**Add/Modify the following relationships:**

| From | To | Type | Label | Changes |
|------|----|----|-------|---------|
| Kandang | User | One-to-One (component) | `supervised_by` | ← ADD (pic_id FK) |
| User | Kandang | One-to-Many | `assigns` | ← Already exists |
| ProduksiTelur | Pengaturan | Dependency | `reads_config` | ← ADD (for conversion) |
| DetailPenjualan | Pengaturan | Dependency | `references` | ← ADD (for calculations) |
| StockService | Pengaturan | Dependency | `reads` | ← ADD |
| StockService | StokTelur | Directed | `updates` | ← Already exists |

---

### 11. **Color Coding Update**

Suggest consistent color scheme:
- **Green** (#E8F5E9): User, Kandang, ProduksiTelur
- **Pink** (#FCE4EC): HargaTelur (pricing related)
- **Blue** (#E3F2FD): Penjualan (sales related)
- **Orange** (#FFF3E0): DetailPenjualan (audit trail - immutable)
- **Purple** (#F3E5F5): StokTelur, Pengaturan (system data)
- **Yellow** (#FFEB3B): StockService (business logic)

---

### 12. **Legend/Notes to Add**

At bottom of diagram, add text box:

```
LEGEND:
✓ Immutable: DetailPenjualan records cannot be updated/deleted
◆ Configuration: Pengaturan key-value store
→ Relationship: Direct reference/FK
⇡ Dependency: Service access pattern

KEY PATTERNS:
• Stock = SUM(ProduksiTelur) - SUM(DetailPenjualan)
• Price snapshots stored in DetailPenjualan for audit
• Conversion ratio (16:1) from Pengaturan table
• Role-based access: pemilik vs pekerja
```

---

## Steps to Update in Draw.io

1. **Open** `c:\xampp\htdocs\hans-jaya-poultry\uml\class_diagram.drawio` in Draw.io
2. **For each class**, double-click to edit:
   - Update attributes (right-click → Edit Data)
   - Add new fields in order
   - Remove deprecated fields
   - Update method signatures
3. **Update relationships** by dragging connection points between classes
4. **Resize boxes** as needed for new fields (especially ProduksiTelur, DetailPenjualan)
5. **Update colors** to match scheme above
6. **Save** and commit to git

---

## Validation Checklist

After updating, verify:

- [ ] All User fields present (including username, role, email_verified_at)
- [ ] ProduksiTelur shows ayam_mati, catatan, ayam_hidup, satuan_input, jumlah_input
- [ ] DetailPenjualan shows price snapshot fields (harga_per_butir_saat_jual, harga_per_kg_saat_jual, jam_penjualan)
- [ ] HargaTelur shows tanggal_akhir field
- [ ] StockService class present with all methods
- [ ] All FK relationships shown (esp. kandang.pic_id → User)
- [ ] Pengaturan box includes current configuration example
- [ ] Legend/notes visible
- [ ] Colors consistent and readable
- [ ] No overlapping text or elements

---

## Notes

- **Canvas Size:** May need to increase if relationships overflow
- **Font Size:** Consider reducing from 11pt to 10pt for readability with more content
- **Grid:** Ensure snap-to-grid is enabled (helps alignment)
- **Export:** After update, export as PNG for documentation

---

## References

- Updated Markdown: [class_diagram.md](./class_diagram.md)
- Database Schema: [../database/sql/hans_jaya_poultry.sql](../database/sql/hans_jaya_poultry.sql)
- Model Files: [../app/Models/](../app/Models/)
- Service Layer: [../app/Services/StockService.php](../app/Services/StockService.php)
