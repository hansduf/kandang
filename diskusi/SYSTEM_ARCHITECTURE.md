# Hans Jaya Poultry System - Structural & Technical Analysis

**Document Date:** April 14, 2026  
**Analysis Focus:** Technical structure, data models, workflows, and system architecture  
**Scope:** Application codebase (excluding diskusi/ and documentation folders)

---

## Executive Summary

Hans Jaya Poultry is a Laravel-based management system for egg production tracking, inventory management, and sales operations. The system centers around:
- **Coops (Kandang)** as primary operational units
- **Production records** tracking daily egg output with performance metrics (HDP, HHP, Mortality)
- **Dynamic stock calculation** using cumulative production minus sales
- **Role-based access** with Owner (Pemilik) and Worker (Pekerja) roles
- **Time-tracked pricing** with historical audit trail

---

## 1. DATABASE ARCHITECTURE

### Core Entity Schema

#### 🏠 **kandangs** - Coop/Enclosure Registry
```sql
id (PK)
nama_kandang VARCHAR(100)
jumlah_ayam UNSIGNED INT          -- Initial capacity baseline
keterangan TEXT
status ENUM('aktif', 'nonaktif')
pic_id UNSIGNED BIGINT (FK → users.id)  -- Person in Charge
timestamps (created_at, updated_at)
```
**Purpose:** Represents physical poultry coops/enclosures  
**Relationships:** One-to-many with ProduksiTelur, Users (pekerja)  
**Key Field:** pic_id identifies supervisor responsible for coop

---

#### 📊 **produksi_telur** - Daily Production Records
```sql
id (PK)
kandang_id BIGINT (FK → kandangs.id)
user_id BIGINT (FK → users.id)     -- Worker recording
tanggal_produksi DATE
satuan_input ENUM('butir', 'kg')   -- Input unit: eggs or weight
jumlah_input DECIMAL(10,2)         -- Raw input quantity

-- Normalized to both units
jumlah_butir INT                   -- Eggs (base unit, no decimals)
jumlah_kg DECIMAL(10,3)            -- Kilograms

-- Health metrics
ayam_mati INT (default 0)          -- Dead birds today
ayam_hidup INT                     -- Living birds today
catatan TEXT

-- Performance indicators
hdp DECIMAL(5,2)                   -- Hen Day Production %
hhp DECIMAL(5,2)                   -- Hen House Production %
mortality DECIMAL(5,2)             -- Mortality percentage

timestamps (created_at, updated_at)
```
**Purpose:** Daily production logs per coop with performance metrics  
**Key Insight:** 
- HDP = (Eggs / Living Birds) × 100
- HHP = (Eggs / Initial Birds) × 100
- Mortality = (Dead Birds / Initial Birds) × 100
- All metrics calculated on entry and stored for historical analysis

---

#### 💰 **penjualan** - Sales Transaction Headers
```sql
id (PK)
user_id BIGINT (FK → users.id)     -- Owner/user recording sale
tanggal_jual DATE
nama_pembeli VARCHAR(100) NULLABLE
total_harga DECIMAL(15,2) default 0 -- Sum of detail items
keterangan TEXT
timestamps (created_at, updated_at)
```
**Purpose:** Header record for each sales transaction  
**Relationships:** One-to-many with DetailPenjualan

---

#### 📦 **detail_penjualan** - Sales Line Items
```sql
id (PK)
penjualan_id BIGINT (FK → penjualan.id)
harga_telur_id BIGINT (FK → harga_telur.id)
satuan_jual ENUM('butir', 'kg')    -- Sale unit
jumlah_jual DECIMAL(10,2)          -- User input quantity in satuan

-- Normalized units
jumlah_butir INT                   -- For calculations
jumlah_kg DECIMAL(10,3)

-- Pricing snapshot
harga_satuan DECIMAL(12,2)         -- Price per unit
harga_per_butir_saat_jual DECIMAL(12,2) NULLABLE
harga_per_kg_saat_jual DECIMAL(12,2) NULLABLE
subtotal DECIMAL(15,2)             
jam_penjualan TIME NULLABLE        -- Time of sale for intra-day tracking

-- NOTE: NO timestamps (TIMESTAMPS = false) - immutable records
```
**Purpose:** Individual items within a sales order  
**Key Feature:** Captures price snapshot at moment of transaction for audit trail  
**Design**: Immutable records (no created_at/updated_at) represent locked transactions

---

#### 💵 **harga_telur** - Price Master with Time Validity
```sql
id (PK)
jenis_harga ENUM('kandang', 'grosir', 'konsumen')
harga_per_kg DECIMAL(12,2)
harga_per_butir DECIMAL(12,2) NULLABLE
tanggal_berlaku DATE              -- Price effective date
tanggal_akhir DATE NULLABLE       -- End of validity
status ENUM('aktif', 'hangus')
user_id BIGINT (FK → users.id)
keterangan TEXT
timestamps (created_at, updated_at)
```
**Purpose:** Pricing history with time-based validity windows  
**Key Logic:**
- When new price entered for jenis_harga, old prices marked 'hangus'
- `aktifPadaTanggalJam()` scope retrieves price valid at specific date/time
- Multiple prices per day tracked via created_at ordering
- Immutable design—prices never updated, only old ones marked expired

---

#### ⚙️ **pengaturan** - System Configuration
```sql
id (PK)
kunci VARCHAR(100) UNIQUE
nilai VARCHAR(255)
tipe_data ENUM('string', 'integer', 'decimal', 'boolean')
keterangan TEXT
updated_at TIMESTAMP (NO CREATED_AT)
```
**Currently Used:** 
- `konversi_butir_per_kg` = 16 (default eggs per kilogram)

---

#### 📈 **stok_telur** - Stock Singleton (Deprecated)
```sql
id (PK)
stok_butir INT default 0
stok_kg DECIMAL(10,3) default 0
updated_at TIMESTAMP (NO CREATED_AT)
```
**Status:** Unused. Stock now calculated **dynamically** via `StockService`  
**Reason:** Cumulative calculation better serves audit trail requirements

---

#### 👤 **users** - Authentication & Authorization
```sql
id (PK)
name VARCHAR
username VARCHAR
email VARCHAR
password VARCHAR (hashed)
email_verified_at TIMESTAMP NULLABLE
role ENUM('pemilik', 'pekerja')
kandang_id BIGINT (FK → kandangs.id) NULLABLE  -- Worker's assigned coop
remember_token VARCHAR NULLABLE
timestamps (created_at, updated_at)
```
**Roles:**
- **pemilik** (Owner): Full system access, pricing, sales, reports
- **pekerja** (Worker): Production entry only, for assigned coop

**Integration:** Spatie Permission traits enabled for future role-based access control

---

### Foreign Key Relationships

```
kandangs (1) ──────┬─→ (many) ProduksiTelur
                   └─→ (many) Users (pekerja)
                   └─→ (1) users.pic_id

produksi_telur (many) ─→ (1) kandangs
                    ─→ (1) users

penjualan (many) ─→ (1) users
          (1) ──────→ (many) detail_penjualan

detail_penjualan (many) ─→ (1) harga_telur
                 ─→ (1) penjualan

harga_telur (many) ─→ (1) users
```

---

## 2. MODEL RELATIONSHIPS & ELOQUENT STRUCTURE

### Kandang Model
- **Relations:**
  - `produksiTelur()` - hasMany ProduksiTelur
  - `pekerja()` - hasMany User (workers assigned)
  - `pic()` - belongsTo User (supervisor)

### ProduksiTelur Model
- **Relations:**
  - `kandang()` - belongsTo Kandang
  - `user()` - belongsTo User
- **Casts:** tanggal_produksi → date
- **Key Calculation:** HDP, HHP, Mortality computed on store

### HargaTelur Model
- **Relations:**
  - `user()` - belongsTo User
  - `detailPenjualan()` - hasMany DetailPenjualan
- **Casts:** tanggal_berlaku, tanggal_akhir → date
- **Scopes:**
  - `aktif()` - Returns currently active prices ordered by date DESC, created_at DESC
  - `aktifPadaTanggalJam($tanggal, $jam)` - Get price valid at specific date/time
- **Methods:**
  - `isAktif()` - Check if price currently active
  - `isHangus()` - Check if price expired
  - `getHargaBerlakuPada($jenis, $tanggal, $jam)` - Static helper

### Penjualan Model
- **Relations:**
  - `user()` - belongsTo User
  - `detail()` - hasMany DetailPenjualan
- **Casts:** tanggal_jual → date

### DetailPenjualan Model
- **Relations:**
  - `penjualan()` - belongsTo Penjualan
  - `hargaTelur()` - belongsTo HargaTelur
- **Note:** No timestamps (TIMESTAMPS = false)
- **Casts:** tanggal_penjualan → date, jam_penjualan → string

### StokTelur Model
- Minimal model: stok_butir, stok_kg
- No creation tracking (CREATED_AT = null)
- **Status:** Not actively used (deprecated)

### Pengaturan Model
- Key-value store access
- No CREATED_AT tracking
- Used for system configuration retrieval

### User Model
- **Relations:**
  - `kandang()` - belongsTo Kandang
  - `produksiTelur()` - hasMany ProduksiTelur
  - `penjualan()` - hasMany Penjualan
  - `hargaTelur()` - hasMany HargaTelur
- **Traits:** HasFactory, Notifiable, HasRoles (Spatie)

---

## 3. CORE BUSINESS WORKFLOWS

### Workflow A: Daily Production Logging (Worker Role)

**Actors:** Pekerja (assigned to kandang)

**Steps:**
1. Worker logs into dashboard → sees assigned kandang only
2. Navigation → Production (Produksi) → Create New
3. Form entry:
   - Select date (tanggal_produksi)
   - Choose unit: Eggs (Butir) or Kilograms (Kg)
   - Enter quantity
   - Enter living bird count (ayam_hidup)
   - Optional: dead birds today, notes
4. System processing (ProduksiTelurController::store):
   - Validates input
   - Retrieves konversi_butir_per_kg from pengaturan (default 16)
   - **Unit Conversion:**
     - If input=Butir: jumlah_butir = input; jumlah_kg = input ÷ 16
     - If input=Kg: jumlah_kg = input; jumlah_butir = input × 16
   - **Metric Calculation:**
     - HDP = (jumlah_butir ÷ ayam_hidup) × 100
     - HHP = (jumlah_butir ÷ kandang.jumlah_ayam) × 100
     - Mortality = (ayam_mati ÷ kandang.jumlah_ayam) × 100
   - Creates ProduksiTelur record with all metrics
5. **Stock Impact:** StockService now includes this production in available stock
6. Response: Redirect to production list with success message

**Data Changes:**
- produksi_telur: +1 new record
- stok_telur: No direct update (dynamic calculation)

---

### Workflow B: Sales Transaction Creation (Owner Role)

**Actors:** Pemilik (owner/sales manager)

**Steps:**
1. Check current stock: GET /api/stok endpoint calls StockService
   - Calculates: All Production Before - All Sales Before + Period Production - Period Sales
   - Returns available butir count
2. Navigation → Penjualan → Create New
3. Form entry:
   - Sale date (tanggal_jual)
   - Optional: Time (jam_jual), Buyer name (nama_pembeli)
   - **Line Items** (multiple allowed):
     - Select price category from aktif prices (HargaTelur::aktif())
     - Choose unit (Butir or Kg)
     - Enter quantity
4. **Validation & Stock Check (PenjualanController::store):**
   - For each line item:
     - If satuan_jual = 'butir': jumlahButir = jumlah_jual
     - If satuan_jual = 'kg': jumlahButir = jumlah_jual × 16
   - Sum all jumlahButir
   - Compare: totalJual ≤ stokTersedia
   - If insufficient: Return error, preserve form data
5. **Transaction Processing (DB::transaction):**
   - Create Penjualan record with total_harga = 0
   - For each line item:
     - Lookup HargaTelur record
     - Calculate: subtotal = jumlah_jual × hargaSatuan
     - Create DetailPenjualan with price snapshots
     - Accumulate total
   - Update Penjualan.total_harga
6. **Stock Impact:** DetailPenjualan records now deducted in StockService calculations
7. Response: Redirect to sales list with success message

**Data Changes:**
- penjualan: +1 new transaction
- detail_penjualan: +N line items (immutable)
- Stock: Dynamically reduced by sum of sales

**Key Safety:** Atomic transaction—all items save or transaction rolled back

---

### Workflow C: Price Management (Owner Role)

**Actors:** Pemilik (owner/manager)

**Steps:**
1. Navigation → Harga Telur → Create New
2. Form entry:
   - Price category: Kandang | Grosir | Konsumen
   - Price per KG
   - Price per Butir (optional, auto-calculated if empty)
   - Effective date (tanggal_berlaku)
3. **Date-Based Logic (HargaTelurController::store):**
   - If tanggal_berlaku < today: Set status='hangus' immediately
   - If tanggal_berlaku >= today: Set status='aktif'
4. **Old Price Expiration:**
   - Find existing 'aktif' price for same jenis_harga
   - Mark as status='hangus'
   - Set tanggal_akhir:
     - If new price for today: tanggal_akhir = today
     - If new price for future: tanggal_akhir = day before new price date
5. **Create new HargaTelur:**
   - jenis_harga: as entered
   - harga_per_kg, harga_per_butir: as entered
   - tanggal_berlaku: as entered
   - status: 'aktif' or 'hangus'
   - user_id: auth()->id()
6. Response: Redirect with success message

**Data Changes:**
- harga_telur: +1 new record
- harga_telur: Update old record to status='hangus', set tanggal_akhir

**Key Feature:** Multiple prices per day tracked via created_at timestamp ordering

---

### Workflow D: Dynamic Stock Calculation (Core Service)

**Trigger:** Dashboard display, Sales validation, API endpoint

**Algorithm (StockService::calculateAvailableStock):**

```
INTENT: Track stock across entire production history
METHOD: Cumulative accounting (opening balance + period activity)

STEP 1: Calculate Opening Balance (all data BEFORE period start)
  Opening = Sum(ProduksiTelur.jumlah_butir where date < periodStart)
          - Sum(DetailPenjualan.jumlah_butir where sale date < periodStart)

STEP 2: Calculate Period Production
  Production = Sum(ProduksiTelur.jumlah_butir where periodStart ≤ date ≤ periodEnd)

STEP 3: Calculate Period Sales
  Sales = Sum(DetailPenjualan.jumlah_butir where periodStart ≤ date ≤ periodEnd)

STEP 4: Calculate Available Stock
  Stock = Opening + Production - Sales
  Return max(0, Stock)  -- Never negative
```

**Example:**
- Jan 1-31: 1000 produced, 600 sold → Closing 400
- Feb 1-28:
  - Opening = 1000 - 600 = 400
  - Feb Production = 1200
  - Feb Sales = 500
  - Feb Stock = 400 + 1200 - 500 = 1100

---

## 4. ROUTE STRUCTURE & ENDPOINTS

### Route Organization by Role

**Middleware Stack:** `auth`, `verified`, `role:*`

#### Public Routes
```
GET /              → Welcome page
```

#### Authenticated All Users
```
GET /dashboard     → DashboardController@index (adaptive by role)
```

#### Pemilik (Owner) Routes - `role:pemilik`
```
RESOURCE: kandang
  GET    /kandang              → List coops with stats
  GET    /kandang/create       → Create form
  POST   /kandang              → Store
  GET    /kandang/{id}         → Show detail
  GET    /kandang/{id}/edit    → Edit form
  PUT    /kandang/{id}         → Update
  DELETE /kandang/{id}         → Delete

RESOURCE: harga (HargaTelur)
  GET    /harga                → List active & expired prices
  GET    /harga/create         → Create form
  POST   /harga                → Store (auto-expire old)
  GET    /harga/{id}/edit      → Edit form
  PUT    /harga/{id}           → Update
  DELETE /harga/{id}           → Delete

RESOURCE: penjualan (Sales)
  GET    /penjualan            → List transactions
  GET    /penjualan/create     → Create form
  POST   /penjualan            → Store (with validation)
  GET    /penjualan/{id}       → Show detail
  GET    /penjualan/{id}/edit  → Edit form
  PUT    /penjualan/{id}       → Update (with re-validation)
  DELETE /penjualan/{id}       → Delete
  GET    /penjualan-harga-by-date  → AJAX: Fetch prices for date

CUSTOM API: Stock
  GET    /api/stok             → JSON: Available stock in butir

RESOURCE: pengaturan (Settings)
  GET    /pengaturan           → List settings
  GET    /pengaturan/create    → Create form
  POST   /pengaturan           → Store
  GET    /pengaturan/{id}/edit → Edit form
  PUT    /pengaturan/{id}      → Update
  DELETE /pengaturan/{id}      → Delete

RESOURCE: users (User Management)
  GET    /users                → List users
  GET    /users/create         → Create form
  POST   /users                → Store
  GET    /users/{id}           → Show
  GET    /users/{id}/edit      → Edit form
  PUT    /users/{id}           → Update
  DELETE /users/{id}           → Delete

REPORTS: Produksi (Production)
  GET    /laporan/produksi                → View report with filters
  GET    /laporan/produksi/export-pdf    → Export to PDF
  GET    /laporan/produksi/export-excel  → Export to Excel

REPORTS: Penjualan (Sales)
  GET    /laporan/penjualan               → View report with filters
  GET    /laporan/penjualan/export-pdf   → Export to PDF
  GET    /laporan/penjualan/export-excel → Export to Excel
```

#### Pekerja (Worker) Routes - `role:pekerja`
```
RESOURCE: produksi (Production)
  GET  /produksi        → List own production records
  GET  /produksi/create → Create form
  POST /produksi        → Store (for assigned kandang)
  GET  /produksi/{id}   → View detail
  (no edit/update/delete)
```

---

## 5. CONTROLLER BUSINESS LOGIC

### DashboardController

**Method:** `index()`

**Role Adaptation:**
- If `pekerja`: Display worker-specific dashboard
- If `pemilik`: Display owner dashboard with global metrics

**Pemilik Dashboard (Full Scope):**
- **Stock Display (dynamic via StockService):**
  - Current period stock in butir and kg
- **Period Filtering:**
  - Options: Today (hari), Last 7 days (7hari), This month (bulan), All time (semua)
  - Parameters: tanggal (specific date), bulan (month), tahun (year)
- **Metrics Aggregated via selectRaw:**
  - Today: produksi, avgHDP, avgHHP, avgMortality
  - Period: produksi, kematian (deaths), avgHDP, avgHHP, avgMortality
  - Kandang: jumlah_kandang, total_kapasitas, totalKematianPeriode
  - Sales: penjualanPeriode (total revenue)
- **Breakdown Calculations:**
  - totalAyamSekarang = totalKapasitas - totalKematianAllTime
  - totalAyamAwal = totalKapasitas - totalKematianSebelumPeriode
- **Charts:**
  - Production per kandang over time (multi-line chart)
  - Trend visualization by date

**Pekerja Dashboard (Single Coop):**
- Show assigned kandang performance only
- Same metrics but scoped to kandang_id

---

### ProduksiTelurController

**Method:** `store(Request $request)`

**Process:**
1. **Input Validation:**
   ```
   tanggal_produksi: required, date
   satuan_input: required, in:butir,kg
   jumlah_input: required, numeric, min:0
   ayam_mati: nullable, integer, min:0
   ayam_hidup: required, integer, min:0
   catatan: nullable, string, max:500
   ```
2. **Unit Conversion:**
   - Fetch konversi_butir_per_kg from pengaturan (default 16)
   - If satuan='butir': jumlah_butir = input; jumlah_kg = input/16
   - If satuan='kg': jumlah_kg = input; jumlah_butir = input*16
3. **Metric Calculation:**
   - HDP = (jumlah_butir / ayam_hidup) × 100, or 0 if ayam_hidup = 0
   - HHP = (jumlah_butir / kandang.jumlah_ayam) × 100
   - Mortality = (ayam_mati / kandang.jumlah_ayam) × 100
4. **Create Record:**
   - ProduksiTelur::create([all fields including metrics])
5. **Response:** Redirect to produksi.index with success message

**Note:** Worker can only log for assigned kandang (enforced via relation)

---

### PenjualanController

**Method:** `store(Request $request)`

**Complex Logic with Atomic Transactions:**

1. **Input Validation:**
   ```
   tanggal_jual: required, date
   jam_jual: nullable, date_format:H:i
   nama_pembeli: nullable, string, max:100
   items: required, array, min:1
   items.*.harga_telur_id: required, exists:harga_telur,id
   items.*.satuan_jual: required, in:butir,kg
   items.*.jumlah_jual: required, numeric, min:0
   items.*.jumlah_butir/kg: nullable, numeric (at least one provided)
   ```

2. **Stock Validation:**
   - Calculate stokTersedia via StockService
   - For each item:
     - If satuan='butir': jumlahButir = jumlah_jual (direct)
     - If satuan='kg': jumlahButir = jumlah_jual × konversi
   - Sum all jumlahButir
   - If totalButir > stokTersedia: Return error with warning

3. **Transaction Processing (DB::transaction):**
   - Create Penjualan (total_harga = 0 initially)
   - For each item:
     - Lookup HargaTelur
     - Determine hargaSatuan:
       - If satuan='kg': harga.harga_per_kg
       - If satuan='butir': harga.harga_per_butir
     - Calculate subtotal = jumlah_jual × hargaSatuan
     - Create DetailPenjualan with:
       * satuan_jual, jumlah_jual (as input)
       * jumlah_butir, jumlah_kg (normalized)
       * harga snapshots (price at time of sale)
       * jam_penjualan (for intra-day tracking)
     - Accumulate total
   - Update Penjualan.total_harga = sum of subtotals
   - No stok_telur update (stock stays dynamic)

4. **Response:** Redirect to penjualan.index with success

**Key Design:**
- jumlah_jual: User-entered quantity in their chosen satuan
- jumlah_butir/kg: Normalized for calculations
- Price snapshots: Captured to preserve historical accuracy

---

### HargaTelurController

**Method:** `store(Request $request)`

**Complex Price Lifecycle Management:**

1. **Input Validation:**
   ```
   jenis_harga: required, in:kandang,grosir,konsumen
   harga_per_kg: required, numeric, min:0
   harga_per_butir: nullable, numeric, min:0
   tanggal_berlaku: required, date
   ```

2. **Date-Based Status Logic:**
   - Parse tanggalBerlaku
   - Get today's date
   - If tanggalBerlaku < today: status = 'hangus'
   - If tanggalBerlaku >= today: status = 'aktif'

3. **Transaction (DB::transaction):**
   - If status='aktif':
     - Determine tanggal_akhir_harga_lama:
       - If tanggalBerlaku = today: Use today (allows multiple prices per day)
       - If tanggalBerlaku > today: Use day before (clear cutoff)
     - Update old aktif prices to hangus:
       ```
       HargaTelur::where('jenis_harga', request.jenis_harga)
                  ->where('status', 'aktif')
                  ->update(['status' => 'hangus', 'tanggal_akhir' => tanggal])
       ```
   - Create new HargaTelur
   - If harga_per_butir empty: Auto-calculate = harga_per_kg / 16

4. **Response:** Redirect to harga.index with success

**Design Note:** Allows system to track price evolution over time; old prices never deleted, marked expired

---

### LaporanController

**Reports: Produksi (Production) & Penjualan (Sales)**

**Features:**
- Period filtering: bulan, 3bulan, 6bulan, semua
- Kandang filtering: Optional by coop
- Pagination: 50 records per page for performance
- Aggregation: Database-level selectRaw queries
- Exports: PDF (via Barryvdh DomPDF) & Excel

**Produksi Report Metrics:**
- Total production (butir, kg)
- Average metrics: HDP, HHP, Mortality percentage
- Per-kandang KPIs with performance comparison
- Charts: Multi-line production trend per coop
- Death statistics per period

**Penjualan Report Metrics:**
- Total revenue
- Item count breakdown
- Price category sales distribution
- Buyer analysis (if tracked)

---

## 6. SERVICE LAYER ARCHITECTURE

### StockService (Core Business Logic)

**Purpose:** Centralized, immutable stock calculation ensuring accounting accuracy

**Method:** `calculateAvailableStock($startDate = null, $endDate = null)`

**Algorithm (Cumulative Accounting):**
```
IF no dates provided:
  PERIOD = current month

STEP 1: Calculate Opening Balance (all before period)
  openingBalance = Sum(ProduksiTelur.jumlah_butir where date < startDate)
                 - Sum(DetailPenjualan.jumlah_butir where sale < startDate)

STEP 2: Calculate Period Production
  productionInPeriod = Sum(ProduksiTelur.jumlah_butir where startDate ≤ date ≤ endDate)

STEP 3: Calculate Period Sales
  salesInPeriod = Sum(DetailPenjualan.jumlah_butir where startDate ≤ date ≤ endDate)

RETURN max(0, openingBalance + productionInPeriod - salesInPeriod)
```

**Design Rationale:**
- Opening balance captures all history before period
- Current data always included in calculation
- No manual updates to stok_telur table (deprecated)
- Every call recomputes from source data (audit trail preserved)

**Conversion Helpers:**
- `getKonversiFactor()` - Fetch from pengaturan
- `butirToKg($butir)` - Divide by factor
- `kgToButir($kg)` - Multiply by factor

**Usage Locations:**
- DashboardController: Stock display
- PenjualanController: Stock validation before sale
- API endpoint: /api/stok for frontend queries

---

## 7. KEY DATA FLOW SEQUENCES

### Sequence 1: Production Entry to Stock Visibility
```
Worker enters production
  ↓
ProduksiTelurController::store() validates & calculates metrics
  ↓
ProduksiTelur record inserted with HDP/HHP/Mortality
  ↓
Worker redirected to success page
  ↓
Dashboard calls StockService::calculateAvailableStock()
  ↓
New production included in next call (immediate effect)
  ↓
Stock display updated
```

### Sequence 2: Sales Validation & Deduction
```
Owner initiates sale creation
  ↓
Frontend calls /api/stok → StockService calculates available
  ↓
Owner adds line items with quantities
  ↓
Owner submits form
  ↓
PenjualanController::store() sums all quantities in butir
  ↓
Compares sum vs stokTersedia
  ↓
If valid: Save with DB::transaction
  ↓
DetailPenjualan items created (immutable)
  ↓
Next StockService call subtracts these sales
  ↓
Stock reduced by net effect
```

### Sequence 3: Price History Tracking
```
Owner sets price for category (e.g., Konsumen) on date D+1
  ↓
HargaTelurController::store() determines status
  ↓
If D+1 > today: status = 'aktif'
  ↓
Find existing aktif price for Konsumen
  ↓
Mark it 'hangus', set tanggal_akhir = day before D+1
  ↓
Insert new price: status='aktif', tanggal_berlaku=D+1
  ↓
Sales on D use old price (searched via aktifPadaTanggalJam)
  ↓
Sales on D+1 use new price
  ↓
Price history queryable for reporting
```

---

## 8. ACCESS CONTROL & AUTHORIZATION

### Role-Based Route Protection

**Pemilik (Owner):**
- Full CRUD on: Kandang, HargaTelur, Penjualan, Pengaturan, Users
- Full Reports access: Production & Sales
- View global dashboard

**Pekerja (Worker):**
- Production logging only (index, create, store, show)
- Only for assigned kandang
- Personal dashboard view of assigned coop

### Middleware Stack
```
Route::middleware(['auth', 'verified', 'role:pemilik'])->group(...)
Route::middleware(['auth', 'verified', 'role:pekerja'])->group(...)
```

**Integration:** Spatie Permission package enabled (HasRoles trait on User)

---

## 9. UNIT CONVERSION SYSTEM

### Conversion Mechanism
- **Master Setting:** `pengaturan.kunci='konversi_butir_per_kg'` (default: 16)
- **Direction 1:** kg → butir = kg × 16
- **Direction 2:** butir → kg = butir ÷ 16 (rounded to 3 decimals)

### Applied At Entry Points
1. **Production Logging:**
   - Worker inputs in butir OR kg
   - System converts to both (jumlah_butir INT, jumlah_kg DECIMAL 10,3)
2. **Sales Entry:**
   - Owner specifies unit per line (butir or kg)
   - User enters quantity in that unit
   - System normalizes for billing and stock tracking

### Data Integrity
- jumlah_butir always stored as INTEGER (no decimals)
- jumlah_kg always DECIMAL(10,3) for precision
- Conversions recalculated on load if settings change
- Historical records preserve original unit and quantity

---

## 10. AUDIT & COMPLIANCE

### Data Immutability
- **DetailPenjualan:** No timestamps, locked records
- **HargaTelur:** Never updated, old prices marked expired (soft delete)
- **ProduksiTelur:** Can be deleted by workers (no edit to preserve original entry)

### Price Snapshot Preservation
- detailpenjualan.harga_per_*_saat_jual captures prices at transaction time
- Allows historical accuracy even if prices change
- Audit trail via harga_telur history table

### User Tracking
- user_id recorded on: ProduksiTelur, Penjualan, HargaTelur
- Timestamp tracking on all major inserts
- jam_penjualan tracks intra-day transactions
- Dashboard metrics show changes within period (filterable)

### Death/Mortality Tracking
- Cumulative ayam_mati across all production records
- Kandang.jumlah_ayam maintained as baseline
- Living birds = baseline - cumulative deaths
- Mortality% calculation based on baseline
- Reportable by period for performance analysis

---

## 11. OPTIMIZATION PATTERNS

### Database Query Optimization
1. **Eager Loading:**
   ```php
   Penjualan::with('user', 'detail.hargaTelur')->get()
   ```
2. **Selective Columns:**
   ```php
   Penjualan::select('id', 'user_id', 'tanggal_jual', 'total_harga')->get()
   ```
3. **Database-Level Aggregation:**
   ```php
   ProduksiTelur::selectRaw('SUM(jumlah_butir) as total, AVG(hdp) as avg_hdp')
   ```
4. **Pagination:**
   - Penjualan: 50 per page
   - HargaTelur: 20 aktif, 10 hangus
   - Laporan: 50 records
5. **groupBy with keyBy():**
   - Single query load, memory-efficient keying by ID

### Business Logic Optimization
1. **Scope Methods:**
   - HargaTelur::aktif() - Reusable active price query
   - HargaTelur::aktifPadaTanggalJam() - Temporal lookup
2. **Atomic Transactions:**
   - PenjualanController sales in DB::transaction
   - All-or-nothing atomicity
3. **StockService Caching Strategy:**
   - No caching (intentional)—always fresh data for accuracy
   - Performance acceptable with indexed queries

---

## 12. SYSTEM CONSTRAINTS & RULES

### Production Constraints
- Each worker logs only for assigned kandang
- tanggal_produksi cannot be in future
- ayam_hidup must be > 0 for HDP calculation
- jumlah_butir must be ≥ 1 (practical minimum)

### Sales Constraints
- Must have available stock (DetailPenjualan.jumlah_butir total ≤ available)
- Each item requires price from aktif prices table
- Multiple items allowed per transaction
- tanggal_jual cannot be future
- Total transaction calculated from line items (no manual entry)

### Pricing Constraints
- One active price per jenis_harga (old ones auto-expire)
- Can set future prices (become aktif on date)
- Can set past prices (created as hangus)
- Multiple prices per day allowed (tracked via created_at)

### Stock Constraints
- Never goes negative (max(0, calculated) rule)
- Immutable—no corrections (compensating entry required)
- Cumulative across all time unless filtered by period

---

## 13. EXTENSION POINTS

### Future Features (Architecture Supports)
1. **Multi-Location Support:**
   - Kandang already supports multiple coops
   - Dashboard already groups by kandang
   - Scaling to multi-supplier already possible

2. **Batch Operations:**
   - Penjualan supports multiple detail items (extensible)
   - Could add batch pricing for volume discounts

3. **Inventory Adjustments:**
   - Create ProduksiTelur adjustment records with negative quantities
   - Would naturally integrate into stock calculation

4. **Customer Management:**
   - Expand nama_pembeli to full customer records
   - Track payment terms, history

5. **Permission Levels:**
   - Spatie Permission traits enabled (not fully utilized)
   - Could add granular role-based permissions

6. **Price Tiers:**
   - HargaTelur already supports multiple jenis_harga
   - Could extend with quantity-based tiers

---

## 14. DEPLOYMENT CONSIDERATIONS

### Database Schema
- Migrations ordered by date (2026-03-31 onwards)
- Foreign keys with cascadeOnDelete for production/sales
- No rollback constraints blocking

### Required Configuration
- `.env` settings: DB connection, mail (for auth)
- `pengaturan` table seed: konversi_butir_per_kg initial value
- User roles seeded: pemilik, pekerja

### Performance Tuning
- Index recommendations:
  - produksi_telur: (kandang_id, tanggal_produksi)
  - detail_penjualan: (penjualan_id, jam_penjualan)
  - harga_telur: (jenis_harga, status, tanggal_berlaku)
- Consider partition on produksi_telur if > 1M records

---

## Summary: Key Architectural Insights

| Aspect | Design |
|--------|--------|
| **Stock Model** | Cumulative calculation (no updates), immutable history |
| **Price Model** | Time-windowed with soft deletion (hangus status) |
| **Sales Model** | Atomic transactions, price snapshots preserved |
| **Production Model** | Calculated metrics (HDP/HHP/Mortality) on entry |
| **Unit System** | Flexible dual-unit (butir/kg) with configurable conversion |
| **Access Control** | Role-based (pemilik/pekerja) via Laravel middleware |
| **Audit Trail** | User-trackedrecords, timestamps, immutable details |
| **Performance** | Database aggregation, eager loading, pagination |
| **Extensibility** | Multi-kandang ready, role-permission framework present |

