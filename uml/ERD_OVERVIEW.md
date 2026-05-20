# Entity-Relationship Diagram (ERD) - Hans Jaya Poultry Database

## 📊 Gambaran Umum

Entity-Relationship Diagram (ERD) mendeskripsikan struktur database **8 entities** dengan **12 relationships** yang menghubungkan master data, transaksi penjualan, produksi, pricing, dan konfigurasi sistem. Desain mengikuti normalisasi 3NF dengan fokus pada integritas referensial (FK constraints) dan immutability untuk audit trail.

---

## 🏗️ Struktur Database (8 Entities)

### Master Data (3 entities)
| Entity | Tipe | Kolom Utama | Peran |
|--------|------|-----------|-------|
| **users** | Master | `id (PK)`, `name`, `email`, `kandang_id (FK)` | Aktor sistem (pekerja, manager) |
| **kandangs** | Master | `id (PK)`, `nama_kandang`, `pic_id (FK)`, `status` | Unit operasional (kandang ayam) |
| **pengaturan** | Config | `id (PK)`, `kunci (UNIQUE)`, `nilai`, `tipe_data` | Key-value configuration store |

### Production & Inventory (2 entities)
| Entity | Tipe | Kolom Utama | Karakteristik |
|--------|------|-----------|-------------|
| **produksi_telur** | Transactional | `id (PK)`, `kandang_id (FK)`, `user_id (FK)`, `tanggal_produksi`, `jumlah_butir`, `HDP`, `HHP` | ⚠️ **Immutable** (audit trail) |
| **stok_telur** | Calculated | `id (PK)`, `stok_butir`, `stok_kg` | Formula: `Σ produksi_telur - Σ detail_penjualan` |

### Pricing (1 entity)
| Entity | Tipe | Kolom Utama | Fitur |
|--------|------|-----------|-------|
| **harga_telur** | Master | `id (PK)`, `jenis_harga`, `harga_per_kg`, `harga_per_butir`, `tanggal_berlaku`, `status` | 🔄 Lifecycle: aktif → hangus |

### Sales Transactions (2 entities - Master-Detail Pattern)
| Entity | Tipe | Kolom Utama | Catatan |
|--------|------|-----------|--------|
| **penjualan** | Transactional | `id (PK)`, `user_id (FK)`, `tanggal_jual`, `nama_pembeli`, `total_harga` | Sales header/master record |
| **detail_penjualan** | Transactional | `id (PK)`, `penjualan_id (FK)`, `harga_telur_id (FK)`, `jumlah_jual`, `harga_satuan`, `subtotal` | ⚠️ **Immutable**, price snapshot at transaction |

---

## 🔗 Hubungan ER (Relationships & Cardinality)

### Relationship Matrix (12 total)

| # | Source | Target | Cardinality | FK Action (Delete/Update) | Peran |
|---|--------|--------|-------------|---------------------------|-------|
| 1 | `users` | `kandangs` | 1 → N | CASCADE | Assign user ke kandang |
| 2 | `kandangs` | `users` | N → 1 | — | Kandang PIC reference (pic_id) |
| 3 | `kandangs` | `produksi_telur` | 1 → N | CASCADE | Kandang has many productions |
| 4 | `users` | `produksi_telur` | 1 → N | CASCADE | User input produksi |
| 5 | `users` | `harga_telur` | 1 → N | CASCADE | User set pricing |
| 6 | `users` | `penjualan` | 1 → N | CASCADE | User create sales |
| 7 | `penjualan` | `detail_penjualan` | 1 → N | CASCADE | Sales contains line items |
| 8 | `harga_telur` | `detail_penjualan` | 1 → N | NO ACTION | Price snapshot capture |
| 9 | `kandangs` | `stok_telur` | 1 ← 1 | — | Kandang tracks current stock |
| 10 | `produksi_telur` | `stok_telur` | N → (calc) | — | Production adds to stock |
| 11 | `detail_penjualan` | `stok_telur` | N → (calc) | — | Sales subtracts from stock |
| 12 | `pengaturan` | — | Isolated | — | System configuration |

### ER Diagram Notation

```
┌─────────────┐         ┌──────────────┐         ┌──────────────────┐
│   users     │────┬────│  kandangs    │────┬────│  produksi_telur  │
│ (PK: id)    │    │    │ (PK: id)     │    │    │  (PK: id)        │
│ name        │    │    │ nama_kandang │    │    │  tanggal_produksi│
│ email       │    │    │ pic_id (FK)──┼────┘    │  jumlah_butir    │
│ kandang_id  │    │    │ status       │         │  HDP, HHP        │
└─────────────┘    │    └──────────────┘         │  ⚠️ Immutable    │
        │          │            │                └──────────────────┘
        │          │    (1←1)    │
        │          │            └────────────────┐
        │          │                            ┌▼──────────┐
        │          │                            │stok_telur │
        │          │                            │(Calculated)
        │          │                            │stok_butir │
        │          │                            │stok_kg    │
        │          │                            └───────────┘
        │          │
        └──┬───────┘
           │
    ┌──────▼─────────┐
    │  penjualan     │
    │ (PK: id)       │
    │ tanggal_jual   │
    │ nama_pembeli   │
    │ total_harga    │
    └────────────────┘
           │
           │ (1→N)
           │
    ┌──────▼──────────────────┐
    │ detail_penjualan         │
    │ (PK: id)                 │
    │ penjualan_id (FK)        │
    │ harga_telur_id (FK)      │
    │ jumlah_jual              │
    │ harga_satuan (snapshot)  │
    │ ⚠️ Immutable             │
    └──────────────────────────┘

Isolated:
    ┌──────────────────┐
    │   pengaturan     │
    │ (PK: id)         │
    │ kunci (UNIQUE)   │
    │ nilai            │
    │ tipe_data        │
    └──────────────────┘
```

---

## 🎯 Design Patterns & Constraints

| Pattern | Deskripsi | Entities | Implementasi |
|---------|-----------|----------|------------|
| **Immutability Trail** | Record tidak bisa diupdate/delete | `produksi_telur`, `detail_penjualan` | No UPDATE/DELETE triggers |
| **Master-Detail** | Transaksi structured dengan header-items | `penjualan` → `detail_penjualan` | 1-to-N FK |
| **Price Snapshot** | Harga dicatat saat transaksi | `detail_penjualan.harga_satuan` | Fixed value, not referenced |
| **Calculated Field** | Stock dihitung real-time | `stok_telur` | SUM formula via service |
| **Cascade Delete** | Child auto-delete saat parent deleted | User → all related records | FK with CASCADE action |
| **Unique Constraint** | Key-value store | `pengaturan.kunci` | UNIQUE index |

---

## 🔍 Alur Data (Transaction Flow)

### Production Workflow
```
1. User input produksi harian
   → INSERT INTO produksi_telur (kandang_id, user_id, tanggal_produksi, jumlah_butir)
   → StockService recalculates stok_telur = sum(produksi) - sum(penjualan)

2. Harga ditetapkan oleh User
   → INSERT INTO harga_telur (jenis_harga, harga_per_kg, status, user_id)
   → Old prices marked hangus (inactive)
```

### Sales Workflow
```
3. User create penjualan header
   → INSERT INTO penjualan (user_id, tanggal_jual, nama_pembeli, total_harga)

4. System create line items dengan harga snapshot
   → INSERT INTO detail_penjualan (penjualan_id, harga_telur_id, jumlah_jual, harga_satuan)
   → harga_satuan = current HargaTelur.harga_per_kg (snapshot at transaction)
   → StockService recalculates stok_telur
```

### Stock Calculation
```
SELECT 
  COALESCE(SUM(p.jumlah_butir), 0) as total_produced,
  COALESCE(SUM(d.jumlah_jual), 0) as total_sold,
  COALESCE(SUM(p.jumlah_butir), 0) - COALESCE(SUM(d.jumlah_jual), 0) as stok_butir
FROM produksi_telur p
LEFT JOIN detail_penjualan d ON 1=1
WHERE p.kandang_id = ?
```

---

## 📋 Normalisasi & Constraints

| Normalisasi | Level | Penerapan |
|-------------|-------|-----------|
| **1NF** | Atomic values | Semua kolom scalar, no nested structures |
| **2NF** | No partial dependency | FK references to PK, no partial key deps |
| **3NF** | No transitive dependency | Kolom depend pada PK, not on non-key attrs |

| Constraint | Entities Affected | Keterangan |
|-----------|------------------|-----------|
| **NOT NULL** | semua FK | Integritas referensial mandatory |
| **UNIQUE** | `pengaturan.kunci` | One value per configuration key |
| **CASCADE DELETE** | `users` parent | Child records auto-deleted |
| **NO ACTION** | `harga_telur` → `detail_penjualan` | Price history preserved |
| **IMMUTABLE** | `produksi_telur`, `detail_penjualan` | No update/delete allowed (audit) |

---

## 📌 Kesimpulan

Database design ini menerapkan **normalization terbaik** dengan 8 entities dan 12 relationships yang terstruktur untuk mendukung workflow produksi-pricing-penjualan. Penggunaan **cascade delete** memastikan data consistency saat user dihapus, **immutable patterns** menjaga audit trail integritas, dan **calculated fields** (stok_telur) meminimalkan redundansi. Struktur mendukung **multi-coop operations** dengan skalabilitas penuh dan risiko data inconsistency minimal.
