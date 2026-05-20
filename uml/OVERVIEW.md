# Class Diagram - Hans Jaya Poultry System

## 📊 Gambaran Umum

Class diagram Hans Jaya Poultry System menggambarkan **9 class utama** yang membentuk arsitektur sistem manajemen produksi dan penjualan telur, diorganisir dalam 5 kategori fungsional dengan hubungan 1-to-many dan service dependency.

---

## 🏗️ Struktur UML

### Core/Master Data (3 class)
| Class | Peran | Atribut Kunci |
|-------|-------|--------------|
| **User** | Aktor sistem | `id`, `name`, `email`, `kandang_id` |
| **Kandang** | Unit operasional | `id`, `nama_kandang`, `pic_id`, `status` |
| **Pengaturan** | Konfigurasi | `kunci`, `nilai`, `tipe_data` |

### Production Flow (1 class)
| Class | Peran | Karakteristik |
|-------|-------|----------------|
| **ProduksiTelur** | Pencatatan harian | `tanggal_produksi`, `jumlah_butir`, `HDP`, `HHP`<br/>⚠️ **Immutable** (no update/delete) |

### Pricing (1 class)
| Class | Peran | Fitur |
|-------|-------|-------|
| **HargaTelur** | Manajemen harga | `jenis_harga`, `harga_per_kg`, `status`<br/>🔄 **Lifecycle**: aktif → hangus |

### Sales Transactions (2 class - Master-Detail)
| Class | Peran | Catatan |
|-------|-------|--------|
| **Penjualan** | Header penjualan | Master record, FK to User |
| **DetailPenjualan** | Line item penjualan | ⚠️ **Immutable**, harga snapshot |

### Stock & Services (2 class)
| Class | Peran | Formula |
|-------|-------|---------|
| **StokTelur** | Inventory tracker | `Σ ProduksiTelur - Σ DetailPenjualan`<br/>⚠️ **Calculated** (read-only) |
| **StockService** | Business logic | Orchestrates stock calculations |

---

## 🔗 Hubungan UML (Relationships)

```
User (1) ──── (N) Kandang
User (1) ──┬─ (N) ProduksiTelur
           ├─ (N) HargaTelur
           └─ (N) Penjualan

Kandang (1) ──┬─ (N) ProduksiTelur
              └─ (1) StokTelur

Penjualan (1) ──── (N) DetailPenjualan
HargaTelur (1) ──┐ (N) DetailPenjualan
                 ↓ (snapshot at transaction time)
```

**Cardinality & Actions:**
- `1-to-N`: User → Kandang/ProduksiTelur/HargaTelur/Penjualan (CASCADE on DELETE)
- `1-to-1`: Kandang ↔ StokTelur (bidirectional tracking)
- `1-to-N`: Penjualan → DetailPenjualan (CASCADE on DELETE)
- `1-to-N`: HargaTelur → DetailPenjualan (NO ACTION on DELETE)

---

## 🎯 Design Patterns & Features

| Pola | Deskripsi | Implementasi |
|-----|-----------|-------------|
| **Immutable Audit Trail** | Produksi dan detail penjualan tidak bisa diubah | ProduksiTelur, DetailPenjualan → no update method |
| **Price Snapshot** | Harga tercatat saat transaksi | DetailPenjualan.harga_satuan ≠ dynamic reference |
| **Calculated Field** | Stock real-time dari formula | StokTelur via StockService calculation |
| **Lifecycle Management** | Status progression otomatis | HargaTelur: aktif → hangus |
| **Service Layer** | Business logic separation | StockService orchestrates calculations |
| **Multi-Coop Support** | Fleksibilitas unit operasional | User → Kandang (1-to-N) design |

---

## 📋 Validasi Data & Constraints

| Constraint | Entitas | Keterangan |
|-----------|---------|-----------|
| **Unique** | Pengaturan.`kunci` | Satu nilai konfigurasi per key |
| **Not Null** | semua FK | Integritas referensial mandatory |
| **Cascade Delete** | User parent | Semua child records auto-delete |
| **Immutability** | ProduksiTelur, DetailPenjualan | Audit trail protection |

---

## 🔍 Alur Data (Data Flow)

```
1. User input Produksi → ProduksiTelur record (immutable, audit logged)
2. System calculate → StokTelur updated via StockService
3. User set Harga → HargaTelur record (aktif dengan tanggal_berlaku)
4. User buat Penjualan → Penjualan + DetailPenjualan (harga snapshot)
5. System recalculate stok → StokTelur berkurang (ProduksiTelur - DetailPenjualan)
```

---

## 📌 Kesimpulan

Arsitektur 9-class ini menerapkan **separation of concerns** dengan clear boundaries antara master data (User, Kandang), transactive data (Penjualan, DetailPenjualan), dan calculated views (StokTelur). Penggunaan immutable patterns dan price snapshots memastikan integritas audit yang ketat, sementara service layer (StockService) mengelola kompleksitas business logic. Desain mendukung **skalabilitas multi-coop** dengan fleksibilitas maksimal dan risiko data inconsistency minimal.

