# Test Scenario Reference Guide

This reference provides documented test scenarios, expected outcomes, and verification steps for the poultry management system.

## Scenario 1: Kandang Lifecycle

**What it tests:** Basic CRUD operations on kandang (coop) entities

### Setup
```sql
-- Clear existing test data
DELETE FROM kandang WHERE nama_kandang LIKE 'TEST_%';
```

### Test Steps

#### 1. Create a kandang
```bash
./test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "TEST_Kandang A",
  "lokasi": "Area 1",
  "kapasitas": 500,
  "pic_id": 1
}'
```

**Expected Response:**
- HTTP 201 Created
- JSON with `id`, `nama_kandang`, timestamps
- Record appears in `kandang` table

#### 2. Retrieve kandang
```bash
./test-api.sh '/api/kandang/1' 'GET' ''
```

**Expected Response:**
- HTTP 200 OK
- Contains exact field values

#### 3. Update kandang
```bash
./test-api.sh '/api/kandang/1' 'PUT' '{
  "nama_kandang": "TEST_Kandang A Updated",
  "kapasitas": 600
}'
```

**Verification:**
```php
// In Tinker
$kandang = Kandang::find(1);
assert($kandang->nama_kandang === 'TEST_Kandang A Updated');
assert($kandang->kapasitas === 600);
```

#### 4. Delete kandang
```bash
./test-api.sh '/api/kandang/1' 'DELETE' ''
```

**Verification:**
```php
assert(Kandang::find(1) === null);
```

---

## Scenario 2: Stock Calculation Workflow

**What it tests:** Accurate stock tracking through production → sales

### Initial State
```php
$kandang = Kandang::find(1);
$initial = app('StockService')->calculateAvailableStock($kandang->id);
// Expect: { butir: 0, kg: 0 } (or existing opening stock)
```

### Test Steps

#### 1. Add Production
```bash
./test-api.sh '/api/produksi' 'POST' '{
  "kandang_id": 1,
  "tanggal_produksi": "2026-04-22",
  "jumlah_butir": 500,
  "jumlah_kg": 31.25,
  "hdp": 95,
  "hhp": 92,
  "mortalitas": 3
}'
```

**Expected stock after:** 500 butir = 31.25 kg

#### 2. Record First Sale
```bash
# Create sale header
$sale = Penjualan::create([
  'user_id' => 1,
  'pembeli' => 'Toko ABC',
  'tanggal_penjualan' => now()
]);

# Add line item (100 butir sold)
DetailPenjualan::create([
  'penjualan_id' => $sale->id,
  'harga_telur_id' => 1,
  'jumlah_butir' => 100,
  'jumlah_kg' => 6.25,
  'harga_satuan' => 500,
  'subtotal' => 50000
]);
```

**Expected stock after:** 400 butir = 25.0 kg

#### 3. Verify Stock Calculation

```php
$stock = app('StockService')->calculateAvailableStock(1);

// Breakdown:
// Opening: 0 butir
// + Production: 500 butir
// - Sale: 100 butir
// = Final: 400 butir

assert($stock['butir'] === 400);
assert($stock['kg'] === 25.0);
```

### Verification Query
```sql
SELECT 
  COALESCE(SUM(CASE WHEN type='produksi' THEN jumlah_butir ELSE 0 END), 0) as total_produced,
  COALESCE(SUM(CASE WHEN type='penjualan' THEN jumlah_butir ELSE 0 END), 0) as total_sold,
  COALESCE(SUM(CASE WHEN type='produksi' THEN jumlah_butir ELSE -jumlah_butir END), 0) as available
FROM inventory_log
WHERE kandang_id = 1
  AND tanggal >= DATE_SUB(NOW(), INTERVAL 1 DAY);
```

---

## Scenario 3: Permission & Role Testing

**What it tests:** Role-based access control (RBAC)

### Users
| Role | Email | Permissions |
|------|-------|-------------|
| pemilik (Owner) | owner@test.local | Full CRUD |
| pekerja (Worker) | worker@test.local | CREATE produksi, VIEW only |

### Test Cases

#### 1. Worker cannot create kandang
```php
$worker = User::role('pekerja')->first();
auth()->setUser($worker);

// This should fail
try {
  Kandang::create(['nama_kandang' => 'Test', 'kapasitas' => 500]);
  throw new Exception("FAILED: Worker should not create kandang");
} catch (AuthorizationException $e) {
  echo "✅ PASS: Worker cannot create kandang";
}
```

#### 2. Worker can create production record
```php
$worker = User::role('pekerja')->first();
auth()->setUser($worker);

$produksi = ProduksiTelur::create([
  'kandang_id' => 1,
  'tanggal_produksi' => now(),
  'jumlah_butir' => 100,
  'jumlah_kg' => 6.25,
]);

echo "✅ PASS: Worker created production record";
```

#### 3. Owner can modify pricing
```php
$owner = User::role('pemilik')->first();
auth()->setUser($owner);

$price = HargaTelur::first();
$price->update(['harga_per_butir' => 600]);

echo "✅ PASS: Owner can update pricing";
```

### Verification
```sql
-- List all role assignments
SELECT u.email, r.name FROM users u
LEFT JOIN model_has_roles mr ON u.id = mr.model_id
LEFT JOIN roles r ON mr.role_id = r.id;

-- Expected:
-- owner@test.local | pemilik
-- worker@test.local | pekerja
```

---

## Scenario 4: Pricing History & Sales Accuracy

**What it tests:** Price snapshots in detail_penjualan, not live lookups

### Setup
```php
$priceV1 = HargaTelur::create([
  'harga_per_butir' => 500,
  'status' => 'aktif',
  'tanggal_berlaku' => '2026-04-01'
]);

$priceV2 = HargaTelur::create([
  'harga_per_butir' => 600,  // Price increases
  'status' => 'aktif',
  'tanggal_berlaku' => '2026-04-20'
]);
```

### Test: Price Change Doesn't Affect Historical Sales

#### 1. Sell 100 eggs at v1 price ($500 each)
```php
DetailPenjualan::create([
  'penjualan_id' => 1,
  'harga_telur_id' => $priceV1->id,
  'jumlah_butir' => 100,
  'harga_satuan' => 500,
  'subtotal' => 50000
]);
```

#### 2. Later query the sale
```php
$sale = DetailPenjualan::find(1);
echo $sale->harga_satuan;  // Still 500, NOT 600
```

**Why this matters:** 
- Audit trail shows what customer actually paid
- Can't retroactively change pricing history
- Always use `harga_telur_id` snapshot, not live price lookup

---

## Scenario 5: Data Validation (Constraints)

**What it fails when:** Invalid data is submitted

### Test: Duplicate kandang name in same location
```bash
./test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Kandang A",
  "lokasi": "Area 1",
  "kapasitas": 500,
  "pic_id": 1
}'

# Try again with same name
./test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Kandang A",
  "lokasi": "Area 1",
  "kapasitas": 600,
  "pic_id": 1
}'
```

**Expected:** HTTP 422 (validation error)

### Test: Missing required fields
```bash
./test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Kandang Test"
  # Missing: lokasi, kapasitas, pic_id
}'
```

**Expected:** HTTP 422 with field names

### Test: Production with invalid kandang
```bash
./test-api.sh '/api/produksi' 'POST' '{
  "kandang_id": 9999,  # Doesn't exist
  "tanggal_produksi": "2026-04-22",
  "jumlah_butir": 100
}'
```

**Expected:** HTTP 422

---

## Scenario 6: Report Generation from Test Results

After running tests, generate documented reports:

### Markdown Report
```bash
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=test-report.md
```

Output includes:
- Summary stats (pass/fail counts, percentage)
- Each test case with endpoint, status, HTTP code
- Assertions with expected vs actual values
- Overall pass/fail conclusion

### JSON Report
```bash
php scripts/generate-report.php \
  --format=json \
  --input=test-results.json \
  --output=test-results-final.json
```

For CI/CD integration, archiving, or downstream processing.

### HTML Report
```bash
php scripts/generate-report.php \
  --format=html \
  --input=test-results.json \
  --output=test-report.html
```

Interactive dashboard with:
- Sortable test table
- Charts (pass rate, timeline)
- Expandable test details

---

## Running Full Test Suite

### 1. Reset database
```bash
php artisan migrate:fresh --seed
```

### 2. Seed test data
```bash
php artisan tinker
> include 'scripts/seed-test-data.php'
> seedStockTesting()
```

###3. Execute all scenarios
```bash
# Kandang CRUD
./scripts/test-api.sh '/api/kandang' 'GET' ''
./scripts/test-api.sh '/api/kandang' 'POST' '{...}'

# Stock workflow
./scripts/test-api.sh '/api/produksi' 'POST' '{...}'
./scripts/test-api.sh '/api/penjualan' 'POST' '{...}'

# Validation
./scripts/test-api.sh '/api/kandang' 'POST' '{}' # Should fail

# Collect results
ls test-results/
```

### 4. Generate reports
```bash
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=test-report.md

php scripts/generate-report.php \
  --format=html \
  --input=test-results.json \
  --output=test-report.html
```

### 5. Review & document findings
- Open `test-report.md` in editor
- Open `test-report.html` in browser
- Note any issues found
- Commit report to version control

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 404 on API endpoint | Verify `routes/api.php` has the endpoint defined |
| 401 Unauthorized | Add authentication header or token to request |
| 500 Internal Error | Check `storage/logs/laravel.log` for stack trace |
| JSON parsing error | Validate request body is valid JSON; use `echo '...' \| jq` |
| Stock calculation off | Run `StockService::recalculate()` or check for orphaned records |
| Permission denied | Verify user has correct role via `User::find(id)->roles` |

---

## Best Practices

1. **Reset state between test runs** — Use `php artisan migrate:fresh` to avoid pollution
2. **Use transactions** — Wrap tests in DB transactions for quick rollback
3. **Seed known data** — Use factories for reproducible starting conditions
4. **Capture all outputs** — Save HTTP responses, DB queries, console output
5. **Generate reports** — Document test results for audit trail
6. **Version test cases** — Keep test scenarios in git alongside code
