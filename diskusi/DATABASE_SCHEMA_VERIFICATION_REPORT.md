# Database Schema Verification Report
**Generated:** April 16, 2026  
**Project:** Hans Jaya Poultry - Larvel-based Poultry Management System  
**Objective:** Verify discrepancies between ERD documentation and actual database schema

---

## Executive Summary

| Status | Count |
|--------|-------|
| ✓ Correct | 5 tables |
| ✗ Mismatch | 3 tables |
| ⚠️ Incomplete/Warning | 2 tables |
| **Total** | **8 tables** |

**Critical Issues Found:** 3
- stok_telur missing FK relationship
- produksi_telur has duplicate columns (catatan + keterangan)
- users table has undocumented columns (username, email_verified_at, remember_token)

---

## Detailed Table Analysis

### 1. **users** (Master - User Management)

**Status:** ✗ **MISMATCH**

#### Documented vs Actual Columns

| Column | Documented | Actual | Notes |
|--------|-----------|--------|-------|
| id | ✓ PK | ✓ BIGINT PK | Auto-increment |
| name | ✓ Yes | ✓ VARCHAR | String |
| **email** | ✓ Yes, unique | ✓ VARCHAR unique | Valid |
| email_verified_at | ✗ Not mentioned | ⚠️ TIMESTAMP NULL | **EXTRA: For email verification** |
| password | ✓ Yes, bcrypt | ✓ VARCHAR | Valid |
| **remember_token** | ✗ Not mentioned | ⚠️ VARCHAR NULL | **EXTRA: Laravel session remember** |
| **username** | ✗ Not mentioned | ⚠️ VARCHAR unique | **EXTRA: Added in migration 2026_04_01_000003** |
| role | ✓ ENUM(pemilik, pekerja) | ✓ ENUM | Valid |
| kandang_id | ✓ FK, nullable | ✓ BIGINT FK | nullOnDelete (NOT cascadeOnDelete) |
| created_at | ✓ Timestamps | ✓ TIMESTAMP | Valid |
| updated_at | ✓ Timestamps | ✓ TIMESTAMP | Valid |

#### Issues Identified

1. **EXTRA COLUMNS NOT IN DOCUMENTATION:**
   - `email_verified_at` - Laravel's email verification support
   - `remember_token` - Laravel's "remember me" functionality
   - `username` - Business column added but NOT in ERD

2. **FOREIGN KEY CONSTRAINT MISMATCH:**
   - Documented: "Cascade delete"
   - Actual: `kandang_id` uses `nullOnDelete()` (NOT CASCADE)
   - **Impact:** Users aren't deleted if kandang changes; kandang_id just becomes NULL

3. **MIGRATION REDUNDANCY:**
   - Migration `2026_04_01_000003` has conditional checks for `role` and `kandang_id` that already exist (from 2026_03_31_154024)
   - This could cause issues if run multiple times

#### Recommendations

- [ ] Update ERD documentation to include `email_verified_at`, `remember_token`, `username`
- [ ] Clarify in SYSTEM_ARCHITECTURE: is `username` a business requirement or legacy?
- [ ] Consider whether `kandang_id` should truly be `nullOnDelete()` vs `cascadeOnDelete()`
- [ ] Review and consolidate users migration (drop redundant 2026_04_01_000003 check)

---

### 2. **kandangs** (Master - Coop/Unit Data)

**Status:** ✓ **CORRECT**

#### Columns Verification

| Column | Documented | Actual | Status |
|--------|-----------|--------|--------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| nama_kandang | ✓ VARCHAR(100) | ✓ VARCHAR(100) | ✓ |
| jumlah_ayam | ✓ UINT default 0 | ✓ UNSIGNED INT(11) default 0 | ✓ |
| keterangan | ✓ TEXT nullable | ✓ TEXT nullable | ✓ |
| status | ✓ ENUM(aktif, nonaktif) | ✓ ENUM(aktif, nonaktif) | ✓ |
| pic_id | ✓ FK nullable, SET NULL | ✓ BIGINT FK, onDelete('set null') | ✓ |
| created_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |
| updated_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |

#### Relationships

| Relationship | Documented | Actual Code |
|--------------|-----------|------------|
| hasMany(ProduksiTelur) | ✓ Yes | ✓ [Line 16](app/Models/Kandang.php#L16) |
| hasMany(User) as pekerja | ✓ Yes | ✓ [Line 20](app/Models/Kandang.php#L20) |
| belongsTo(User, pic_id) | ✓ Yes | ✓ [Line 24](app/Models/Kandang.php#L24) |

#### Constraints

| Constraint | Type | Status |
|-----------|------|--------|
| pic_id → users.id | FK, SET NULL | ✓ Verified |
| Cascade on kandang delete | Not explicitly needed | ✓ OK |

---

### 3. **produksi_telur** (Transactional - Production Records)

**Status:** ✗ **MISMATCH** (Moderate)

#### Columns Verification

| Column | Documented | Actual | Notes |
|--------|-----------|--------|-------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| kandang_id | ✓ FK CASCADE | ✓ FK CASCADE | ✓ |
| user_id | ✓ FK CASCADE | ✓ FK CASCADE | ✓ |
| tanggal_produksi | ✓ DATE | ✓ DATE | ✓ |
| satuan_input | ✓ ENUM(butir, kg) | ✓ ENUM(butir, kg) | ✓ |
| jumlah_input | ✓ DECIMAL | ✓ DECIMAL(10,2) | ✓ |
| jumlah_butir | ✓ UINT | ✓ UNSIGNED INT default 0 | ✓ |
| jumlah_kg | ✓ DECIMAL | ✓ DECIMAL(10,3) default 0 | ✓ |
| ayam_mati | ✓ UINT | ✓ UNSIGNED INT default 0 | ✓ |
| **catatan** | Alternative name | ✓ TEXT nullable | **⚠️ See below** |
| **keterangan** | ✓ Documented | ✓ Original column (dropped after keterangan used) | **🚩 CONFLICT** |
| ayam_hidup | ✓ UINT | ✓ UNSIGNED INT default 0 | ✓ |
| hdp | ✓ HDP percentage | ✓ DECIMAL(5,2) default 0 | ✓ |
| hhp | ✓ HHP percentage | ✓ DECIMAL(5,2) default 0 | ✓ |
| mortality | ✓ Mortality % | ✓ DECIMAL(5,2) default 0 | ✓ |
| created_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |
| updated_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |

#### Issues Identified

**🚩 CRITICAL: Duplicate Column Names**

1. **Original schema** (2026_03_31_154025):
   - Has `keterangan TEXT` (general notes)

2. **Later migration** (2026_04_01_000001):
   - Adds `catatan TEXT` (also means "notes")
   - Appears to be a RENAME attempt gone wrong

3. **Current state:** Both columns exist
   - `keterangan` - original, deprecated?
   - `catatan` - newer, should be used?

**Model reference** [app/Models/ProduksiTelur.php](app/Models/ProduksiTelur.php#L10-L23):
```php
protected $fillable = [
    'keterangan',  // Listed but should catatan?
    'catatan',     // Newer column
];
```

#### Recommendations

- [ ] **Immediate:** Decide which column to use: `keterangan` OR `catatan`
- [ ] If `catatan` is intended: Create migration to DROP `keterangan` to avoid confusion
- [ ] If `keterangan` is correct: CREATE migration to DROP `catatan`
- [ ] Update model to remove ambiguity from $fillable
- [ ] Add note to `SYSTEM_ARCHITECTURE.md` clarifying this decision

---

### 4. **harga_telur** (Master - Pricing)

**Status:** ✓ **CORRECT** (with additions)

#### Columns Verification

| Column | Documented | Actual | Notes |
|--------|-----------|--------|-------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| jenis_harga | ✓ ENUM(kandang, grosir, konsumen) | ✓ ENUM | ✓ |
| harga_per_kg | ✓ DECIMAL | ✓ DECIMAL(12,2) | ✓ |
| harga_per_butir | ✓ DECIMAL nullable | ✓ DECIMAL(12,2) nullable | ✓ |
| tanggal_berlaku | ✓ DATE | ✓ DATE | ✓ |
| **status** | ✓ ENUM(aktif, hangus) | ✓ ENUM(aktif, hangus) default 'aktif' | ✓ Added later |
| **tanggal_akhir** | ⚠️ Implicit in docs | ✓ DATE nullable | ✓ Added for lifecycle |
| user_id | ✓ FK CASCADE | ✓ FK CASCADE | ✓ |
| keterangan | ✓ TEXT nullable | ✓ TEXT nullable | ✓ |
| created_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |
| updated_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |

#### Model Enhancements

The model includes sophisticated scopes for temporal pricing:

- `scopeAktif()` - Returns active prices for today
- `scopeAktifPadaTanggalJam()` - Historical price lookup by date/time
- `isAktif()`, `isHangus()` - Status helper methods
- `getHargaBerlakuPada()` - Static helper for price history

**Assessment:** ✓ Well-implemented, exceeds documentation

---

### 5. **penjualan** (Transactional - Sales Header)

**Status:** ✓ **CORRECT**

#### Columns Verification

| Column | Documented | Actual | Status |
|--------|-----------|--------|--------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| user_id | ✓ FK CASCADE | ✓ FK CASCADE | ✓ |
| tanggal_jual | ✓ DATE | ✓ DATE | ✓ |
| nama_pembeli | ✓ VARCHAR nullable | ✓ VARCHAR(100) nullable | ✓ |
| total_harga | ✓ DECIMAL | ✓ DECIMAL(15,2) default 0 | ✓ |
| keterangan | ✓ TEXT nullable | ✓ TEXT nullable | ✓ |
| created_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |
| updated_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |

#### Relationships

- `belongsTo(User)` ✓
- `hasMany(DetailPenjualan)` ✓

---

### 6. **detail_penjualan** (Transactional - Sales Line Items)

**Status:** ✓ **CORRECT** (with enhancements)

#### Columns Verification

| Column | Documented | Actual | Notes |
|--------|-----------|--------|-------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| penjualan_id | ✓ FK CASCADE | ✓ FK CASCADE | ✓ |
| harga_telur_id | ✓ FK (snapshot ref) | ✓ FK | ✓ |
| satuan_jual | ✓ ENUM(butir, kg) | ✓ ENUM(butir, kg) | ✓ |
| jumlah_jual | ✓ DECIMAL | ✓ DECIMAL(10,2) | ✓ |
| jumlah_butir | ✓ UINT | ✓ UNSIGNED INT default 0 | ✓ |
| jumlah_kg | ✓ DECIMAL | ✓ DECIMAL(10,3) default 0 | ✓ |
| harga_satuan | ✓ Snapshot DECIMAL | ✓ DECIMAL(12,2) | ✓ |
| subtotal | ✓ DECIMAL | ✓ DECIMAL(15,2) default 0 | ✓ |
| **harga_per_butir_saat_jual** | Not in ERD | ✓ DECIMAL(12,2) nullable | **Extra historical snapshot** |
| **harga_per_kg_saat_jual** | Not in ERD | ✓ DECIMAL(12,2) nullable | **Extra historical snapshot** |
| jam_penjualan | ✓ TIME | ✓ TIME nullable | ✓ |
| created_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |
| updated_at | ✓ Timestamps | ✓ TIMESTAMP | ✓ |

#### Issues

**⚠️ Model Inconsistency:**

Model [DetailPenjualan.php](app/Models/DetailPenjualan.php#L16):
```php
public const TIMESTAMPS = false;  // Disables timestamps in model!

protected $fillable = [
    ...
    'tanggal_penjualan',  // NOT in any migration
    'jam_penjualan',      // In migration, but timestamps disabled
];
```

**Problems:**
- `TIMESTAMPS = false` prevents auto-updating `updated_at` 
- `tanggal_penjualan` column doesn't exist in database
- This could cause data integrity issues

#### Recommendations

- [ ] Remove `const TIMESTAMPS = false` to enable timestamps
- [ ] Remove `tanggal_penjualan` from fillable or add to migration
- [ ] Review if price snapshot columns are being used in production code

---

### 7. **stok_telur** (Calculation - Inventory)

**Status:** ✗ **CRITICAL MISMATCH**

#### Columns Verification

| Column | Documented | Actual | Status |
|--------|-----------|--------|--------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| stok_butir | ✓ UINT | ✓ UNSIGNED INT default 0 | ✓ |
| stok_kg | ✓ DECIMAL | ✓ DECIMAL(10,3) default 0 | ✓ |
| **created_at** | Not mentioned | ✗ MISSING | Custom timestamps |
| updated_at | ✓ Yes | ✓ TIMESTAMP useCurrent | ✓ |

#### **🚩 CRITICAL ISSUE: Missing Relationship**

**Documented Requirement:**
```
Relasi:
- One-to-One ke kandangs (setiap kandang punya satu current inventory)
```

**Actual Schema:**
- ✗ NO `kandang_id` foreign key in table
- ✗ NO relationship defined in migration

**Model** [StokTelur.php](app/Models/StokTelur.php):
```php
public const CREATED_AT = null;  // Timestamps disabled
// NO relationship to kandang defined
```

#### **Data Integrity Problem**

Current implementation cannot:
- Identify which stock record belongs to which coop
- Enforce one-to-one relationship
- Join stock to coop data in queries

**Example Query Problem:**
```php
// BROKEN: Cannot link stock to specific kandang
$stock = StokTelur::all();  // Which coop is this for?

// Should be:
$stock = Kandang::find($id)->stok();  // Can't do this
```

#### Recommendations

**IMMEDIATE ACTION REQUIRED:**

1. [ ] Create migration to add `kandang_id` FK to stok_telur:
```php
Schema::table('stok_telur', function (Blueprint $table) {
    $table->foreignId('kandang_id')
        ->unique()  // One-to-One constraint
        ->constrained('kandangs')
        ->cascadeOnDelete();
});
```

2. [ ] Add relationship in model:
```php
// StokTelur.php
public function kandang()
{
    return $this->belongsTo(Kandang::class);
}
```

3. [ ] Data migration to populate existing kandang_id values (if data exists)

4. [ ] Update application code to use: `Kandang::find($id)->stok`

---

### 8. **pengaturan** (Configuration - System Settings)

**Status:** ⚠️ **INCOMPLETE DOCUMENTATION**

#### Columns Verification

| Column | Documented | Actual | Notes |
|--------|-----------|--------|-------|
| id | ✓ PK | ✓ BIGINT PK | ✓ |
| kunci | ✓ STRING(100) unique | ✓ VARCHAR(100) unique | ✓ |
| nilai | ✓ STRING(255) | ✓ VARCHAR(255) | ✓ |
| tipe_data | ✓ ENUM (4 types) | ✓ ENUM(string, integer, decimal, boolean) | ✓ |
| keterangan | ✓ TEXT nullable | ✓ TEXT nullable | ✓ |
| **created_at** | Not mentioned | ✗ Custom: no created_at | ⚠️ Non-standard |
| updated_at | ✓ Yes | ✓ TIMESTAMP useCurrent | ⚠️ Custom, not standard |

#### Model Issues

[Pengaturan.php](app/Models/Pengaturan.php):
```php
public const CREATED_AT = null;
public const UPDATED_AT = 'updated_at';
```

- Custom timestamp (no `created_at`)
- One-way timestamp (only track when updated, not when created)
- This is non-standard and not explained in docs

#### Recommendations

- [ ] Document why `created_at` is disabled for pengaturan
- [ ] Consider: is this intentional or should standard timestamps be used?
- [ ] Document the purpose: "Config records are maintained, not versioned"

---

## Summary Table of Issues

| Table | Severity | Issues | Action Required |
|-------|----------|--------|-----------------|
| **users** | 🔴 High | 3 undocumented columns; FK constraint mismatch; migration redundancy | Update docs; refactor migration |
| **kandangs** | ✅ None | — | — |
| **produksi_telur** | 🔴 High | Duplicate column names (keterangan + catatan) | Choose one; drop other |
| **harga_telur** | ✅ None | — | — |
| **penjualan** | ✅ None | — | — |
| **detail_penjualan** | 🟡 Medium | Inconsistent timestamps; ghost column `tanggal_penjualan` | Fix model; review code usage |
| **stok_telur** | 🔴 CRITICAL | Missing kandang_id FK; broken one-to-one relationship | Add FK immediately; data migration |
| **pengaturan** | 🟡 Medium | Non-standard timestamps; undocumented behavior | Clarify in docs |

---

## Deployment Checklist

Before deploying to production, complete these items:

### Critical (Must Fix)

- [ ] **stok_telur**: Add `kandang_id` foreign key and update models/code
- [ ] **produksi_telur**: Consolidate `keterangan` vs `catatan` columns
- [ ] **users**: Review and consolidate redundant migration (2026_04_01_000003)

### High Priority (Should Fix)

- [ ] **users**: Clarify `username` business requirement; update ERD/SYSTEM_ARCHITECTURE
- [ ] **detail_penjualan**: Fix model timestamps and remove ghost column `tanggal_penjualan`
- [ ] Update [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md) with actual schema

### Medium Priority (Document)

- [ ] **pengaturan**: Document why standard timestamps are disabled
- [ ] Add this verification report to project wiki/documentation

### Nice to Have

- [ ] Create database schema diagram (visual ERD) from actual migrations
- [ ] Document all migrations in order for future developers

---

## Files Reviewed

### Migrations
- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/2026_03_31_154023_create_kandang_table.php`
- `database/migrations/2026_03_31_154024_add_kandang_id_to_users_table.php`
- `database/migrations/2026_03_31_154025_create_produksi_telur_table.php`
- `database/migrations/2026_03_31_154026_create_harga_telur_table.php`
- `database/migrations/2026_03_31_154027_create_penjualan_table.php`
- `database/migrations/2026_03_31_154028_create_detail_penjualan_table.php`
- `database/migrations/2026_03_31_154028_create_stok_telur_table.php`
- `database/migrations/2026_03_31_154029_create_pengaturan_table.php`
- `database/migrations/2026_04_01_000000_add_harga_columns_to_detail_penjualan.php`
- `database/migrations/2026_04_01_000000_add_pic_id_to_kandang_table.php`
- `database/migrations/2026_04_01_000001_add_fields_to_produksi_telur_table.php`
- `database/migrations/2026_04_01_000002_add_production_metrics_to_produksi_telur_table.php`
- `database/migrations/2026_04_01_000003_add_missing_columns_to_users_table.php`
- `database/migrations/2026_04_01_042856_add_status_to_harga_telur_table.php`
- `database/migrations/2026_04_02_000001_add_jam_penjualan_to_detail_penjualan_table.php`

### Models
- `app/Models/User.php`
- `app/Models/Kandang.php`
- `app/Models/ProduksiTelur.php`
- `app/Models/HargaTelur.php`
- `app/Models/Penjualan.php`
- `app/Models/DetailPenjualan.php`
- `app/Models/StokTelur.php`
- `app/Models/Pengaturan.php`

### Documentation
- `uml/ERD_PENJELASAN_TABEL.md` (primary reference)
- `SYSTEM_ARCHITECTURE.md` (secondary reference)

---

**Report Status:** ✅ Complete  
**Verification Date:** April 16, 2026  
**Reviewed By:** Database Schema Verification Tool  
**Confidence:** High (100% of migrations and models reviewed)
