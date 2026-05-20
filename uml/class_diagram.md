# Class Diagram - Hans Jaya Poultry System

## Overview
This document describes the class diagram for the Hans Jaya Poultry Management System. It shows the core business entities, their attributes, methods, and relationships.

---

## Core Classes

### 1. **User**
Represents system users (workers, managers, owners)

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| name | string | User full name |
| username | string | Login username (unique) |
| email | string | Email address (unique) |
| email_verified_at | timestamp (nullable) | Email verification timestamp |
| password | string | Hashed password |
| role | enum | Role type: **pemilik** (owner) or **pekerja** (worker) |
| kandang_id | bigint (FK) | Assigned coop reference (workers only) |
| remember_token | string (nullable) | "Remember me" token for session persistence |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

| Method | Return | Description |
|--------|--------|-------------|
| hasRole(role) | boolean | Check if user has specific role |
| getKandang() | Kandang | Get assigned coop (for workers) |
| getProduksi() | Collection\<ProduksiTelur\> | Get production records by user |
| getPenjualan() | Collection\<Penjualan\> | Get sales transactions by user |
| getHarga() | Collection\<HargaTelur\> | Get price configurations set by user |

---

### 2. **Kandang**
Represents physical operational units (coops/cages)

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| nama_kandang | string | Coop name/identifier (e.g., "Kandang 1") |
| jumlah_ayam | int | Total number of birds in coop (capacity) |
| keterangan | text (nullable) | Notes/description |
| status | enum | Status: **aktif** or **nonaktif** |
| pic_id | bigint (FK) | Person in charge (FK to User, typically **pekerja** role) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

| Method | Return | Description |
|--------|--------|-------------|
| getPic() | User | Get assigned supervisor/worker |
| tambahProduksi(data) | ProduksiTelur | Add new production record |
| getProduksi() | Collection\<ProduksiTelur\> | Get all production records |
| getStokTerkini() | decimal (tuple) | Get current stock (butir, kg) |
| calculateAyamHidup() | int | Calculate living birds (capacity - mortality) |
| setStatus(status) | void | Update coop status |

---

### 3. **ProduksiTelur**
Represents daily egg production logs with health metrics

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| kandang_id | bigint (FK) | Reference to coop |
| user_id | bigint (FK) | Worker who logged the data (FK to User) |
| tanggal_produksi | date | Production date |
| satuan_input | enum | Input unit: **butir** (pieces) or **kg** (kilograms) |
| jumlah_input | decimal | Input quantity (user-entered value) |
| jumlah_butir | int | Calculated eggs (pcs) — auto-converted or user-entered |
| jumlah_kg | decimal | Calculated eggs (kg) — auto-converted or user-entered |
| ayam_mati | int | Dead birds count (for mortality tracking) |
| catatan | text (nullable) | Notes/observations (health events, treatments) |
| ayam_hidup | int | Living birds (calculated: capacity - cumulative mortality) |
| hdp | decimal | Hen Daily Production % (jumlah_ayam / 100) |
| hhp | decimal | Hen Health Production % (production capacity effectiveness) |
| mortality | decimal | Mortality rate % (cumulative deaths / capacity * 100) |
| keterangan | text (nullable) | Additional notes/remarks |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

| Method | Return | Description |
|--------|--------|-------------|
| getKandang() | Kandang | Get associated coop |
| getInputer() | User | Get worker who logged data |
| konversiKeButir() | int | Convert to eggs (pcs) using Pengaturan ratio |
| konversiKeKg() | decimal | Convert to kg using Pengaturan ratio |
| calculateMetrics() | array | Calculate HDP, HHP, mortality based on candang capacity |
| getMetricsProduction() | array | Get all health metrics as array |

---

### 4. **HargaTelur**
Represents pricing configuration with time-window lifecycle

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| jenis_harga | enum | Price type: **kandang** (farm), **grosir** (wholesale), or **konsumen** (retail) |
| harga_per_kg | decimal | Price per kilogram (Rp) |
| harga_per_butir | decimal | Price per individual egg (Rp) |
| tanggal_berlaku | date | Effective start date |
| status | enum | Status: **aktif** (current) or **hangus** (expired/archived) |
| tanggal_akhir | date (nullable) | End date when price expires |
| user_id | bigint (FK) | Manager/admin who set the price |
| keterangan | text (nullable) | Notes (e.g., market conditions) |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Last update |

| Method | Return | Description |
|--------|--------|-------------|
| setHargaBaru(data) | void | Set new price (old auto-expire via tanggal_akhir) |
| isAktif() | boolean | Check if price is currently active |
| getHargaSaatIni() | HargaTelur | Get currently active price by type |
| getNilaiHarga(satuan) | decimal | Get price by unit (**butir** or **kg**) |
| expireOldPrices() | void | Mark old prices as hangus on effective date change |

---

### 5. **Penjualan**
Represents sales transaction headers (master records)

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint (FK) | Seller/cashier entering transaction (FK to User) |
| tanggal_jual | date | Sale date |
| nama_pembeli | string (nullable) | Buyer name/company |
| total_harga | decimal | Total transaction amount (Rp) — sum of all DetailPenjualan subtotals |
| keterangan | text (nullable) | Transaction notes (order type, delivery notes, etc.) |
| created_at | timestamp | Record creation (timestamp of sale) |
| updated_at | timestamp | Last update |

| Method | Return | Description |
|--------|--------|-------------|
| tambahDetail(detail) | DetailPenjualan | Add line item to transaction |
| getDetail() | Collection\<DetailPenjualan\> | Get all line items |
| getPenjual() | User | Get seller/cashier info |
| hitungTotal() | decimal | Calculate total from detail items |
| getInvoice() | string | Generate/retrieve invoice number |
| commit() | void | Finalize and trigger StockService update |

---

### 6. **DetailPenjualan**
Represents sales line items with price snapshots (immutable audit trail)

**Critical:** This table is immutable to maintain audit compliance. Price snapshots capture exact pricing at sale time.

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| penjualan_id | bigint (FK) | Parent sales transaction |
| harga_telur_id | bigint (FK) | Price reference at sale time (snapshot) |
| satuan_jual | enum | Sale unit: **butir** (pieces) or **kg** (kilograms) |
| jumlah_jual | decimal | Quantity sold (in satuan_jual unit) |
| jumlah_butir | int | Converted quantity (eggs in pieces) |
| jumlah_kg | decimal | Converted quantity (kg) |
| jam_penjualan | time (nullable) | Sale time of day (for reconciliation) |
| harga_satuan | decimal | Unit price at sale time (Rp per satuan_jual unit) |
| harga_per_butir_saat_jual | decimal | Price per egg (pcs) snapshot at sale time |
| harga_per_kg_saat_jual | decimal | Price per kg snapshot at sale time |
| subtotal | decimal | Line item total (harga_satuan × jumlah_jual) |
| created_at | timestamp | Record creation (sale timestamp) |
| updated_at | timestamp | Last update |

| Method | Return | Description |
|--------|--------|-------------|
| getPenjualan() | Penjualan | Get parent transaction |
| getHarga() | HargaTelur | Get price snapshot reference |
| hitungSubtotal() | decimal | Calculate line total (should match stored subtotal) |
| getAuditTrail() | array | Get immutable audit data (all prices captured) |
| isImmutable() | boolean | Verify record is locked from edit/delete |

**Immutability Note:** DetailPenjualan records CANNOT be directly updated or deleted. Stock recalculation happens via StockService after sales are committed.

---

### 7. **StokTelur**
Represents current egg stock (calculated, not manually updated)

**Critical:** Stock is a **computed property** — never updated directly. Calculated as: `Σ(ProduksiTelur) - Σ(DetailPenjualan.jumlah_jual)` in real-time.

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key (typically 1 record for system) |
| stok_butir | int | Current total stock (eggs in pieces) |
| stok_kg | decimal | Current total stock (kilograms) |
| updated_at | timestamp | Last recalculation time |

| Method | Return | Description |
|--------|--------|-------------|
| getStokButir() | int | Get stock in pieces |
| getStokKg() | decimal | Get stock in kg |
| isStokCukup(jumlah) | boolean | Validate if enough stock exists for sale |
| getStokHistory() | Collection | Get historical stock snapshots |

**Design Note:** StokTelur rows are immutable snapshots updated only by `StockService.calculateStock()` after transaction writes. No manual updates or deletes allowed.

---

### 8. **Pengaturan**
Represents system configuration (key-value store)

Configuration keys stored in this table control system-wide settings used by services and controllers.

| Attribute | Type | Description |
|-----------|------|-------------|
| id | bigint | Primary key |
| kunci | string (unique) | Configuration key name |
| nilai | string | Configuration value (serialized if complex) |
| tipe_data | enum | Data type hint: **string**, **integer**, **decimal**, or **boolean** |
| keterangan | text (nullable) | Description/documentation of the setting |
| updated_at | timestamp | Last modification time |

**Current Configuration Keys:**

| Kunci | Nilai | Tipe | Deskripsi |
|-------|-------|------|-----------|
| `konversi_butir_per_kg` | `16` | integer | Conversion ratio: 16 pieces (butir) = 1 kilogram |

| Method | Return | Description |
|--------|--------|-------------|
| get(kunci) | mixed | Get configuration value (auto-typed) |
| set(kunci, nilai, tipe) | void | Set/update configuration |
| getAsInteger(kunci) | int | Get value as integer |
| getAsDecimal(kunci) | decimal | Get value as decimal |
| getAsBoolean(kunci) | boolean | Get value as boolean |
| getKonversiRatio() | int | Convenience method: get conversion ratio |

**Usage:** Controllers fetch `Pengaturan` values and pass to views (e.g., `{{ $konversi ?? 16 }}`) for dynamic UI calculations.

---

### 9. **StockService** (Service Layer)
Orchestrates complex business logic for stock calculation and inventory management.

**Responsibilities:**
- Calculate available stock from production and sales data
- Convert between units (butir/kg) using Pengaturan configuration
- Validate stock sufficiency before sales confirmation
- Maintain immutable audit trail via StokTelur snapshots

| Method | Parameter(s) | Return | Description |
|--------|--------------|--------|-------------|
| calculateAvailableStock() | — | tuple\<int, decimal\> | Calculate total (stok_butir, stok_kg) from all production minus sales |
| calculateStockPerKandang(kandang_id) | kandang_id | tuple | Get cumulative stock for specific coop |
| calculateStockByDate(start_date, end_date) | start_date, end_date | array | Get stock change history for date range |
| updateStokAfterSales(penjualan_id) | penjualan_id | void | Recalculate and snapshot stock after transaction commit |
| validateStokCukup(detail) | detail: DetailPenjualan | boolean | Verify sufficient stock before sale confirmation |
| convertUnits(value, from_unit, to_unit) | value, from_unit, to_unit | decimal | Convert between butir and kg using Pengaturan ratio |
| getKonversiRatio() | — | int | Fetch conversion ratio from Pengaturan table |

**Pseudo-code Calculation:**
```
available_stock = SUM(ProduksiTelur.jumlah_butir) 
                  - SUM(DetailPenjualan.jumlah_jual WHERE satuan_jual='butir')
                  - (SUM(DetailPenjualan.jumlah_jual WHERE satuan_jual='kg') * konversi_ratio)
```

**Dependencies:**
- Reads: `Pengaturan` (konversi_butir_per_kg), `ProduksiTelur`, `DetailPenjualan`, `StokTelur`
- Writes: `StokTelur` (only via calculateAvailableStock())

---

## Relationships

**Primary Cardinality Rules:**

```
User (1) ──→ (N) Kandang          [Owner/Admin → Coops managed]
User (1) ◇──→ (1) Kandang         [Worker assigned to ONE coop via kandang_id]
User (1) ──→ (N) ProduksiTelur    [Worker logs production]
User (1) ──→ (N) Penjualan        [Cashier/Owner creates sales]
User (1) ──→ (N) HargaTelur       [Admin/Owner sets pricing]

Kandang (1) ◇──→ (1) User         [PIC: Coop assigned to ONE supervisor]
Kandang (1) ──→ (N) ProduksiTelur [Coop has daily production records]

ProduksiTelur (1) ──→ (1) Kandang [Production record links to 1 coop]
ProduksiTelur (1) ──→ (1) User    [Production logged by 1 worker]

HargaTelur (1) ──→ (N) DetailPenjualan [Price referenced in multiple sales]
HargaTelur (1) ──→ (1) User       [Price set by 1 user]

Penjualan (1) ──→ (N) DetailPenjualan  [Transaction has multiple items]
Penjualan (1) ──→ (1) User             [Sales created by 1 user]

DetailPenjualan (N) ──→ (1) HargaTelur [Each item references price snapshot]
DetailPenjualan (N) ──→ (1) Penjualan  [Items belong to 1 transaction]

ProduksiTelur ────→ StockService       [Input: Production data]
DetailPenjualan ────→ StockService     [Input: Sales data]
Pengaturan ◆─────→ StockService       [Config: Conversion ratios]
StockService ────→ StokTelur           [Output: Stock calculation]
```

**Relationship Symbols:**
- `──→` = One-to-Many directed
- `◇──→` = Component/required association
- `◆` = Configuration dependency
- `────→` = Service dependency

---

## Data Flow Diagram & Controller Integration

```
┌──────────────────────────────────────────────────────────────────────┐
│                    COMPLETE WORKFLOW SEQUENCE                        │
└──────────────────────────────────────────────────────────────────────┘

PRODUCTION ENTRY FLOW:
  User (pekerja) → ProduksiTelurController@create
    ↓ fetch Kandang, Pengaturan (konversi)
    ↓ ProductionView rendered with:
        • $kandangs (dropdown)
        • $konversi = 16 (from Pengaturan or default)
        • convertible inputs: butir ↔ kg via JavaScript
    ↓ user submits ProduksiTelur data
    ↓ stored with jumlah_butir, jumlah_kg, metrics calculated
    ✓ production record created

PRICING SETUP FLOW:
  User (pemilik) → HargaTelurController@create
    ↓ displays current prices grouped by jenis_harga
    ↓ user enters: harga_per_kg, harga_per_butir, tanggal_berlaku
    ↓ system auto-expires old prices (status → hangus)
    ✓ new HargaTelur created with status=aktif

SALES TRANSACTION FLOW:
  User (pemilik/pekerja) → PenjualanController@create
    ↓ fetch Kandang options, available stock, HargaTelur aktif, Pengaturan
    ↓ SalesView rendered with:
        • Current stok (from StockService.calculateAvailableStock())
        • $konversi (for unit conversion display)
        • Dynamic pricing selector (HargaTelur.jenis_harga)
    ↓ user creates Penjualan header + adds DetailPenjualan items
        ├─ each item: select satuan_jual (butir/kg), jumlah
        ├─ system fetches HargaTelur price snapshot at that moment
        ├─ calculates: harga_satuan, harga_per_butir_saat_jual, harga_per_kg_saat_jual
        └─ subtotal = harga_satuan × jumlah
    ↓ total_harga = SUM(subtotal from all items)
    ↓ on save: PenjualanController commits → triggers StockService.updateStokAfterSales()
    ↓ StockService:
        ├─ reads Pengaturan.konversi_butir_per_kg
        ├─ recalculates: available = production - sales
        └─ updates StokTelur snapshot
    ✓ transaction locked (DetailPenjualan immutable)

CONFIG MANAGEMENT:
  User (pemilik) → PengaturanController@update
    ↓ fetch current Pengaturan values (kunci, nilai, tipe_data)
    ↓ display form with editable configuration
    ↓ on update: Pengaturan record modified
    ↓ impacts: all future unit conversions across app
    ✓ configuration persisted

FRONTEND DYNAMIC BEHAVIOR (Alpine.js):
  • Unit converter: {{ satuan_input }} change triggers:
      → if satuan='butir': show jumlah_butir, hide jumlah_kg
      → if satuan='kg': show jumlah_kg, hide jumlah_butir
      → conversion: jumlah_butir = jumlah_kg * $konversi
  • Stock display: shows available stock in real-time
      → format: "{{ stock_butir }} butir ({{ stock_kg }} kg)"
      → calculated using Pengaturan ratio
  • Price selector: shows aktif prices only
      → grayed out hangus prices
      → onclick: populates harga_satuan for calculation
```

---

## Database Transaction Flow (Backend)

```ruby
# In PenjualanController@store:

transaction do
  # 1. Create header
  penjualan = Penjualan.create(user_id: auth()->id(), ...)
  
  # 2. Create line items (immutable on commit)
  detail_params.each do |detail|
    harga = HargaTelur.where(status: 'aktif').first
    DetailPenjualan.create(
      penjualan_id: penjualan.id,
      harga_telur_id: harga.id,
      harga_saat_jual: harga.harga_per_satuan,  # SNAPSHOT
      harga_per_butir_saat_jual: harga.harga_per_butir,  # AUDIT
      harga_per_kg_saat_jual: harga.harga_per_kg,        # AUDIT
      subtotal: calculated_value,
      # DetailPenjualan now LOCKED - no update/delete
    )
  end
  
  # 3. Recalculate stock via service
  StockService.updateStokAfterSales(penjualan.id)
  
  # 4. Commit creates snapshot in StokTelur
  # On rollback: all reverted
end
```

---

## Key Design Patterns

### 1. **Immutable Audit Trail (DetailPenjualan)**
- **Purpose:** Maintain compliance and prevent accidental data corruption
- Price snapshots capture exact pricing at sale time: `harga_per_butir_saat_jual`, `harga_per_kg_saat_jual`
- No UPDATE/DELETE allowed on `DetailPenjualan` records after creation
- Provides forensic capability: can always audit what was charged on specific date

### 2. **Dynamic Stock Calculation (StockService)**
- **Purpose:** Prevent manual update errors and ensure consistency
- `StokTelur` is never directly incremented/decremented
- Real-time calculation: available stock = Σ(production) - Σ(sales)
- Formula: `stok_butir = SUM(ProduksiTelur.jumlah_butir) - SUM(DetailPenjualan.jumlah_butir)`
- Service layer (`StockService`) orchestrates all calculations
- Stock updates ONLY via `StockService.updateStokAfterSales()` after transaction commit

### 3. **Price Lifecycle Management (HargaTelur)**
- **Purpose:** Maintain historical price record while preventing stale data
- Lifecycle: `aktif` (current) → `hangus` (expired)
- When new price set: old price auto-expires via `tanggal_akhir`
- `DetailPenjualan` stores foreign key `harga_telur_id` for audit trail
- Historical data preserved for reports and analysis

### 4. **Unit Conversion Flexibility**
- **Purpose:** Support flexible input/output while maintaining precision
- Every quantified entity stores BOTH units: `jumlah_butir` AND `jumlah_kg`
- Conversion ratio stored centrally in `Pengaturan.konversi_butir_per_kg` (default: 16)
- Automatic conversion at entry: if user enters kg, system calculates butir
- Conversion accessible to frontend via controller injection (e.g., `{{ $konversi }}`)

### 5. **Service Layer Orchestration**
- **Purpose:** Centralize business logic and enable testing
- `StockService` encapsulates:
  - Calculation algorithms
  - Unit conversion logic
  - Configuration access
  - Immutability enforcement
- Separates data models from complex workflows
- Controllers remain lean, focused on HTTP concerns
- Services can be tested independently via unit tests

### 6. **Role-Based Access Control (RBAC)**
- **Purpose:** Enforce authorization at route and data access level
- Two roles: `pemilik` (owner/admin) and `pekerja` (worker)
- Route middleware: `role:pemilik|pekerja` restricts access
- `User.kandang_id` links workers to assigned coops
- Worker can only view/edit production for assigned coop
- Owner/admin can view all data and modify pricing/users

### 7. **Dynamic Configuration Injection**
- **Purpose:** Enable runtime behavior changes without code redeployment
- Controllers fetch `Pengaturan` values and pass to views
- Example: `ProduksiTelurController` sends `$konversi` to view
- Views use: `<input data-konversi="{{ $konversi ?? 16 }}" />`
- Frontend JavaScript reads `data-konversi` for dynamic calculations
- Any configuration change applies immediately system-wide

---

## Cardinality Reference

| Symbol | Meaning |
|--------|---------|
| `1` | Exactly one |
| `N` | Zero or many |
| `◇` | Aggregation (component) |
| `-->` | Directed relationship |
| `--` | Undirected relationship |

---

## Database Constraints & Indexes

### Foreign Key Relationships

| FK Column | Table | References | Constraint | Purpose |
|-----------|-------|-----------|-----------|---------|
| `users.kandang_id` | users | kandangs.id | CASCADE | Worker assignment to coop |
| `kandangs.pic_id` | kandangs | users.id | SET NULL | Coop supervisor reference |
| `produksi_telur.kandang_id` | produksi_telur | kandangs.id | CASCADE | Production belongs to coop |
| `produksi_telur.user_id` | produksi_telur | users.id | CASCADE | Production logged by user |
| `penjualan.user_id` | penjualan | users.id | CASCADE | Sale recorded by user |
| `detail_penjualan.penjualan_id` | detail_penjualan | penjualan.id | CASCADE | Item belongs to transaction |
| `detail_penjualan.harga_telur_id` | detail_penjualan | harga_telur.id | RESTRICT | Price snapshot audit trail |
| `harga_telur.user_id` | harga_telur | users.id | CASCADE | Price set by user |

### Unique Constraints

| Column(s) | Table | Purpose |
|-----------|-------|---------|
| `email` | users | Prevent duplicate email registration |
| `username` | users | Prevent duplicate login credentials |
| `kunci` | pengaturan | Single value per configuration key |

### Indexes for Performance

| Index Name | Columns | Table | Purpose |
|------------|---------|-------|---------|
| PRIMARY | id | all | Primary key lookups |
| FK: kandang_id | kandang_id | users, produksi_telur | Fast worker/production filtering |
| FK: pic_id | pic_id | kandangs | Supervisor lookups |
| FK: user_id | user_id | penjualan, produksi_telur, harga_telur | User transaction history |
| FK: penjualan_id | penjualan_id | detail_penjualan | Line item lookups |
| FK: harga_telur_id | harga_telur_id | detail_penjualan | Price audit trail |
| tanggal_produksi | tanggal_produksi | produksi_telur | Daily production queries |
| tanggal_jual | tanggal_jual | penjualan | Sales date filtering |
| status | status | harga_telur | Active price queries |

---

## Implementation Notes

### Column Type Mappings

| Database Type | Laravel Type | Usage |
|---------------|-------------|-------|
| `bigint UNSIGNED` | `id()` | Primary keys, Foreign keys |
| `varchar(255)` | `string()` | Names, usernames, emails |
| `text` | `text()` | Long descriptions, notes |
| `enum('val1','val2')` | `enum()` | Role, status, unit types |
| `decimal(10,2)` or `decimal(10,3)` | `decimal()` | Currency, weights |
| `int UNSIGNED` | `integer()` | Counts (birds, eggs) |
| `date` | `date()` | Production date, effective date |
| `time` | `time()` | Sale time of day |
| `timestamp` | `timestamp()` | created_at, updated_at |

### Data Integrity Rules

1. **Production Entry Integrity:**
   - `ProduksiTelur.jumlah_butir` and `jumlah_kg` must both be populated
   - User selects unit, system auto-calculates the other
   - Both must be internally consistent: `jumlah_kg * konversi_ratio ≈ jumlah_butir`

2. **Sales Transaction Integrity:**
   - `Penjualan.total_harga` = SUM(DetailPenjualan.subtotal)
   - Each `DetailPenjualan.subtotal` = quantity × harga_satuan
   - Price snapshots are immutable once committed
   - Stock must be sufficient before transaction is allowed

3. **Stock Calculation Integrity:**
   - `StokTelur` never directly decremented/incremented
   - Always recalculated: production - sales (both units)
   - Must be consistency-checked periodically against source records

4. **Price History Integrity:**
   - Old prices must have `status='hangus'` and `tanggal_akhir` set
   - Only ONE price per `jenis_harga` can have `status='aktif'` at any time
   - `DetailPenjualan.harga_telur_id` creates immutable audit link

5. **User Role Integrity:**
   - Workers (`pekerja`) must have `kandang_id` assigned
   - Owners (`pemilik`) have `kandang_id = NULL`
   - Role determines accessible data and operations

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-04-22 | Initial class diagram documentation |
| 1.1 | 2026-04-22 | Updated with actual database schema from hans_jaya_poultry.sql:<br/>- Added missing User fields (username, email_verified_at, role)<br/>- Documented all ProduksiTelur health metrics<br/>- Added price snapshot fields to DetailPenjualan<br/>- Clarified Pengaturan configuration values<br/>- Added comprehensive Data Flow, Patterns, and Constraints documentation<br/>- Added database indexes and foreign key reference table |

---

## References & Documentation

- **System Architecture:** [SYSTEM_ARCHITECTURE.md](../SYSTEM_ARCHITECTURE.md)
- **Database Migrations:** [database/migrations/](../database/migrations)
- **Database Dump:** [database/sql/hans_jaya_poultry.sql](../database/sql/hans_jaya_poultry.sql)
- **Stock Service Logic:** [app/Services/StockService.php](../app/Services/StockService.php)
- **Controllers:** [app/Http/Controllers/](../app/Http/Controllers/)
- **Models:** [app/Models/](../app/Models/)

