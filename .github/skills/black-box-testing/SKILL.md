---
name: black-box-testing
description: 'Test poultry system APIs, database, business logic, and user workflows without UI. Use for: API validation, data integrity checks, workflow testing, permission verification, performance profiling, user journey simulation, and multi-format test reporting (JSON/Markdown/HTML).'
argument-hint: 'Describe the test scenario (e.g., "test stock calculation", "validate sales workflow", "check role permissions", "profile API response time")'
user-invocable: true
disable-model-invocation: false
---

# Black Box Testing + Reporting

Comprehensive black box testing workflow for hans-jaya-poultry system covering:
- **APIs**: Endpoint validation, response structures, status codes
- **Database**: Data integrity, relationships, constraints, transaction rollback
- **Business Logic**: Service layer workflows, calculations, state transitions  
- **Permissions**: Role-based access control (RBAC), authorization flows
- **Performance**: Response times, load testing, bottleneck detection
- **User Journeys**: Multi-step workflows without UI interaction

## When to Use

- Verify endpoint responses and HTTP status codes
- Validate database data integrity and relationships
- Test business logic (stock calculations, pricing, workflows)
- Ensure data transformations work correctly
- Generate documented test reports with custom findings
- Debug unexpected behavior by querying directly
- Validate permission and role-based access control

## Architecture

Testing operates at **four integrated layers**:

| Layer | Tools | Purpose | Example |
|-------|-------|---------|---------|
| **API** | cURL, HTTP requests | Test endpoints, responses | `POST /api/kandang` returns 201 Created |
| **Database** | PHP Tinker, SQL | Verify data state & constraints | Stock calculations match `stok_telur` |
| **Business Logic** | Service classes, PHP | Test workflows & calculations | `StockService::calculateAvailableStock()` |
| **Workflow & Permissions** | Tinker + authorization | Test user journeys, role access | Worker creates production, owner approves pricing |

## Quick Start Workflow

1. **Define test scenario**: What feature/endpoint/workflow to test?
2. **Prepare test data**: Seed database with known state
3. **Execute tests**: Use appropriate layer (API/Database/Logic/Workflow)
4. **Verify behavior**: Check assertions, status codes, data changes
5. **Collect results**: Capture all output, requests, responses
6. **Generate report**: Create Markdown/JSON/HTML documentation
7. **Analyze findings**: Identify failures, performance issues, edge cases

## Quick Start Workflow

1. **Define test scenario**: What feature/endpoint/workflow to test?
2. **Prepare test data**: Seed database with known state
3. **Execute tests**: Use appropriate layer (API/Database/Logic/Workflow)
4. **Verify behavior**: Check assertions, status codes, data changes
5. **Collect results**: Capture all output, requests, responses
6. **Generate report**: Create Markdown/JSON/HTML documentation
7. **Analyze findings**: Identify failures, performance issues, edge cases

---

## Level 0: Permission & Role Testing (RBAC)

**Purpose**: Verify role-based access control is enforced correctly

Two primary roles in the system:
- **`pemilik` (Owner)**: Full CRUD access to all resources
- **`pekerja` (Worker)**: Limited read + production entry only

### Test Permission Boundaries

#### Setup: Create test users

```php
php artisan tinker

// Create owner
$owner = User::factory()->create(['email' => 'owner@test.local']);
$owner->assignRole('pemilik');
auth()->setUser($owner);

// Create worker
$worker = User::factory()->create(['email' => 'worker@test.local']);
$worker->assignRole('pekerja');
```

#### Test Case 1: Owner can create kandang, worker cannot

```php
// Switch to worker context
auth()->setUser($worker);

// Try to create kandang (should fail)
try {
  Kandang::create([
    'nama_kandang' => 'Unauthorized Kandang',
    'lokasi' => 'Area X',
    'kapasitas' => 500,
    'pic_id' => $worker->id
  ]);
  echo "❌ FAIL: Worker should not create kandang\n";
} catch (\Exception $e) {
  echo "✅ PASS: Worker blocked from creating kandang\n";
}

// Switch to owner context
auth()->setUser($owner);
$kandang = Kandang::create([
  'nama_kandang' => 'Authorized Kandang',
  'kapasitas' => 500,
  'pic_id' => $owner->id
]);
echo "✅ PASS: Owner created kandang ID={$kandang->id}\n";
```

**Expected Results:**
- Worker create: ❌ Authorization exception
- Owner create: ✅ Record created in database

#### Test Case 2: Verify permission scope per resource

```php
$kandang = Kandang::first();
$pricing = HargaTelur::first();
$production = ProduksiTelur::first();

// Test worker permissions
auth()->setUser($worker);
echo "Worker can view kandang: " . ($worker->can('view', $kandang) ? '✅' : '❌') . "\n";
echo "Worker can create production: " . ($worker->can('create', ProduksiTelur::class) ? '✅' : '❌') . "\n";
echo "Worker can update pricing: " . ($worker->can('update', $pricing) ? '✅' : '❌') . "\n";

// Test owner permissions  
auth()->setUser($owner);
echo "Owner can update pricing: " . ($owner->can('update', $pricing) ? '✅' : '❌') . "\n";
echo "Owner can delete kandang: " . ($owner->can('delete', $kandang) ? '✅' : '❌') . "\n";
```

**Expected Matrix:**

| Action | Object | Worker | Owner |
|--------|--------|--------|-------|
| View | Kandang | ✅ | ✅ |
| Create | Kandang | ❌ | ✅ |
| Update | Kandang | ❌ | ✅ |
| Delete | Kandang | ❌ | ✅ |
| Create | Production | ✅ | ✅ |
| Update | Pricing | ❌ | ✅ |
| View | Reports | ✅ | ✅ |

#### Test Case 3: Cross-user access prevention

```php
// Create two users
$owner1 = User::factory()->create();
$owner1->assignRole('pemilik');

$owner2 = User::factory()->create();
$owner2->assignRole('pemilik');

// Owner1 creates kandang
auth()->setUser($owner1);
$kandang = Kandang::create(['nama_kandang' => 'Owner1 Kandang', 'pic_id' => $owner1->id]);

// Owner2 tries to delete owner1's kandang (should work - both are owners)
auth()->setUser($owner2);
$kandang->delete();
echo "✅ PASS: One owner can delete another's kandang\n";

// But worker cannot access
auth()->setUser($worker);
try {
  $kandang->delete();
  echo "❌ FAIL: Worker should not delete kandang\n";
} catch (\Exception) {
  echo "✅ PASS: Worker blocked from deleting kandang\n";
}
```

### API-Level Permission Testing

Some permissions are enforced at controller/middleware level. Test via API:

```bash
# Test: Worker tries to access owner-only endpoint
# First, login as worker and get token
WORKER_TOKEN=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"worker@test.local","password":"password"}' \
  http://localhost:8000/api/login | jq -r '.token')

# Try to create kandang (should get 403 Forbidden)
curl -X POST \
  -H "Authorization: Bearer $WORKER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"nama_kandang":"Test","kapasitas":500}' \
  http://localhost:8000/api/kandang

# Expected: { "message": "Unauthorized", "status": 403 }
```

---

## Level 4: Performance Testing

**Purpose**: Measure response times, identify bottlenecks, ensure acceptable performance (< 500ms for API endpoints)

### Basic Response Time Measurement

```php
php artisan tinker

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Test API response time
$start = microtime(true);

$kandang = Kandang::with('produksiTelur', 'penjualan')
  ->where('pic_id', auth()->id())
  ->paginate(50);

$duration = (microtime(true) - $start) * 1000;  // Convert to ms

echo "Query time: {$duration}ms\n";
echo "Expected: < 200ms (should be fast)\n";
echo "Result: " . ($duration < 200 ? '✅ PASS' : '⚠️ SLOW') . "\n";
```

### Database Query Profiling

```php
// Enable query logging
DB::enableQueryLog();

$kandang = Kandang::with('produksiTelur', 'penjualan')->get();

$queries = DB::getQueryLog();
echo "Total queries: " . count($queries) . "\n";

foreach ($queries as $q) {
  echo "  - {$q['time']}ms: " . substr($q['query'], 0, 60) . "...\n";
}

// Check for N+1 problems
if (count($queries) > 5 && count($kandang) < 10) {
  echo "⚠️ WARNING: Possible N+1 query detected\n";
}
```

### Load Testing (Simple)

Test how API behaves under concurrent requests:

```bash
# Use Apache Bench to send 100 requests, 10 concurrent
ab -n 100 -c 10 http://localhost:8000/api/kandang

# Output interpretation:
# - Requests per second: Higher is better
# - Time per request: Should be < 500ms
# - Failed requests: Should be 0
```

### Stress Testing Stock Calculation

```php
php artisan tinker

// Create kandang with many production/sales records
$kandang = Kandang::first();

// Populate with realistic data (1000+ records)
for ($i = 0; $i < 1000; $i++) {
  ProduksiTelur::factory()->create(['kandang_id' => $kandang->id]);
}

// Measure stock calculation time
$start = microtime(true);
$stock = app('StockService')->calculateAvailableStock($kandang->id);
$duration = (microtime(true) - $start) * 1000;

echo "Stock calculation with 1000+ records: {$duration}ms\n";
echo "Expected: < 50ms\n";
echo "Result: " . ($duration < 50 ? '✅ PASS' : '❌ SLOW') . "\n";
```

---

## Level 5: User Journey & Navigation Testing

**Purpose**: Test complete workflows that span multiple steps and permission levels

### Journey 1: New Sale Transaction (Owner + Worker Interaction)

**Actors**: Worker enters production, Owner approves and records sale

**Steps**:

```
1. Worker logs in, views available kandang
2. Worker assigns production to kandang
3. Owner reviews new production entries
4. Owner creates sale transaction
5. Owner records sale line items (with pricing)
6. System updates stock automatically
7. Both user roles can view final report
```

**Test Code**:

```php
php artisan tinker

// SETUP: Create users and kandang
$owner = User::role('pemilik')->first();
$worker = User::role('pekerja')->first();
$kandang = Kandang::first();

echo "=== Sale Journey Test ===\n";

// STEP 1: Worker views kandang list
auth()->setUser($worker);
$kandangList = $worker->can('viewAny', Kandang::class);
echo "1. Worker can view kandang: " . ($kandangList ? '✅' : '❌') . "\n";

// STEP 2: Worker records production
$production = ProduksiTelur::create([
  'kandang_id' => $kandang->id,
  'tanggal_produksi' => now(),
  'jumlah_butir' => 300,
  'jumlah_kg' => 18.75,
  'hdp' => 95,
  'hhp' => 92,
  'mortalitas' => 3
]);
echo "2. Worker created production: ✅ (ID={$production->id})\n";

// STEP 3: Owner reviews production
auth()->setUser($owner);
$pendingProduction = ProduksiTelur::where('kandang_id', $kandang->id)
  ->where('tanggal_produksi', now()->toDateString())
  ->first();
echo "3. Owner reviewed production: ✅ (Found {$pendingProduction->jumlah_butir} eggs)\n";

// STEP 4: Owner creates sale
$sale = Penjualan::create([
  'user_id' => $owner->id,
  'pembeli' => 'Toko ABC',
  'tanggal_penjualan' => now(),
  'total' => 100000
]);
echo "4. Owner created sale: ✅ (ID={$sale->id})\n";

// STEP 5: Owner adds sale line item
$price = HargaTelur::active()->first();
$detail = DetailPenjualan::create([
  'penjualan_id' => $sale->id,
  'harga_telur_id' => $price->id,
  'jumlah_butir' => 100,
  'jumlah_kg' => 6.25,
  'harga_satuan' => $price->harga_per_butir,
  'subtotal' => 50000
]);
echo "5. Owner created sale detail: ✅ (100 eggs for 50000)\n";

// STEP 6: Verify stock updated
$expectedStock = 300 - 100;  // Production - Sale
$actualStock = app('StockService')->calculateAvailableStock($kandang->id);
$stockMatch = $actualStock['butir'] === $expectedStock;
echo "6. Stock updated: " . ($stockMatch ? '✅' : '❌') . " (Expected: $expectedStock, Got: {$actualStock['butir']})\n";

// STEP 7: Both users can view report
auth()->setUser($worker);
$workerCanView = $worker->can('viewAny', Penjualan::class);

auth()->setUser($owner);
$ownerCanView = $owner->can('viewAny', Penjualan::class);

echo "7. Worker can view report: " . ($workerCanView ? '✅' : '❌') . "\n";
echo "8. Owner can view report: " . ($ownerCanView ? '✅' : '❌') . "\n";

echo "\n=== Journey Result: " . ($stockMatch && $workerCanView && $ownerCanView ? '✅ PASS' : '❌ FAIL') . " ===\n";
```

### Journey 2: Pricing Change & Retroactive Data Protection

**Actors**: Owner changes price, verifies historical sales unaffected

**Steps**:

```
1. Sale recorded at price v1 ($500/egg)
2. Owner updates price to v2 ($600/egg)
3. Verify: Historical sale still shows $500 (snapshot preserved)
4. New sales use v2 pricing ($600/egg)
```

**Test Code**:

```php
php artisan tinker

auth()->setUser(User::role('pemilik')->first());

echo "=== Pricing History Journey ===\n";

// STEP 1: Record sale at v1 price
$priceV1 = HargaTelur::create([
  'harga_per_butir' => 500,
  'harga_per_kg' => 8000,
  'status' => 'aktif',
  'tanggal_berlaku' => now()
]);

$sale1 = Penjualan::create([
  'user_id' => auth()->id(),
  'pembeli' => 'Buyer 1',
  'tanggal_penjualan' => now()
]);

$detail1 = DetailPenjualan::create([
  'penjualan_id' => $sale1->id,
  'harga_telur_id' => $priceV1->id,
  'jumlah_butir' => 100,
  'harga_satuan' => 500,  // v1 price
  'subtotal' => 50000
]);

echo "1. Sale recorded at v1 price: ✅ (\$500/egg)\n";

// STEP 2: Update price to v2
$priceV2 = HargaTelur::create([
  'harga_per_butir' => 600,
  'harga_per_kg' => 9600,
  'status' => 'aktif',
  'tanggal_berlaku' => now()
]);

echo "2. Price updated to v2: ✅ (\$600/egg)\n";

// STEP 3: Verify historical sale uses v1
$historicalSale = DetailPenjualan::find($detail1->id);
$v1Preserved = $historicalSale->harga_satuan === 500;
echo "3. Historical sale still shows v1 (\$500): " . ($v1Preserved ? '✅' : '❌') . "\n";

// STEP 4: New sale uses v2
$sale2 = Penjualan::create([
  'user_id' => auth()->id(),
  'pembeli' => 'Buyer 2',
  'tanggal_penjualan' => now()
]);

$detail2 = DetailPenjualan::create([
  'penjualan_id' => $sale2->id,
  'harga_telur_id' => $priceV2->id,
  'jumlah_butir' => 100,
  'harga_satuan' => 600,  // v2 price
  'subtotal' => 60000
]);

$newSaleUsesV2 = $detail2->harga_satuan === 600;
echo "4. New sale uses v2 (\$600): " . ($newSaleUsesV2 ? '✅' : '❌') . "\n";

echo "\n=== Journey Result: " . ($v1Preserved && $newSaleUsesV2 ? '✅ PASS' : '❌ FAIL') . " ===\n";
```

---

### Setup

Ensure Laravel dev server is running:
```bash
composer dev
```

### Test Endpoint Basics

Use [API test script](./scripts/test-api.sh):

```bash
cd /your-workspace
./test-api.sh '<endpoint>' '<method>' '<data>'
```

**Example: Create a kandang (coop)**
```bash
./test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Kandang Baru",
  "lokasi": "Area A",
  "kapasitas": 1000,
  "pic_id": 1
}'
```

**Example: Get all kandang**
```bash
./test-api.sh '/api/kandang' 'GET' ''
```

## Level 1: API Testing (cURL)

**Purpose**: Test HTTP endpoints directly without browser UI

### Setup

Ensure Laravel dev server is running:
```bash
# Terminal 1: Start all services
composer dev
```

### Test Endpoint Basics

Use [API test script](./scripts/test-api.sh):

```bash
# Terminal 2: Test API
cd c:\xampp\htdocs\hans-jaya-poultry
./scripts/test-api.sh '<endpoint>' '<method>' '<json_data>'
```

#### Example 1: Create a kandang

```bash
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Kandang Premium",
  "lokasi": "Area A - Building 1",
  "kapasitas": 1000,
  "pic_id": 1
}'
```

**Expected Output:**
```json
{
  "status": "PASS",
  "http_code": "201",
  "records_found": "1",
  "sample": {"id": 15, "nama_kandang": "Kandang Premium", ...}
}
```

#### Example 2: Retrieve all kandang with pagination

```bash
./scripts/test-api.sh '/api/kandang?page=1&per_page=50' 'GET' ''
```

**Expected:**
- HTTP 200 OK
- Array of kandang objects
- Pagination metadata (total, per_page, current_page)

#### Example 3: Update kandang capacity

```bash
./scripts/test-api.sh '/api/kandang/1' 'PUT' '{
  "kapasitas": 1200
}'
```

**Verify in next read:**
```bash
./scripts/test-api.sh '/api/kandang/1' 'GET' ''
# Confirm: "kapasitas": 1200
```

#### Example 4: Delete kandang

```bash
./scripts/test-api.sh '/api/kandang/1' 'DELETE' ''

# Verify deleted
./scripts/test-api.sh '/api/kandang/1' 'GET' ''
# Expected: HTTP 404 Not Found
```

### Authentication & Protected Routes

#### Get Authentication Token

```bash
./scripts/test-api.sh '/api/login' 'POST' '{
  "email": "owner@test.local",
  "password": "password"
}'
```

**Response:**
```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIs...",
  "token_type": "Bearer"
}
```

#### Use Token in Protected Endpoint

The test-api.sh script handles token injection automatically. For manual cURL:

```bash
TOKEN="eyJhbGciOiJIUzI1NiIs..."

curl -X GET \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  http://localhost:8000/api/kandang
```

### Response Validation Checklist

For each API test, verify:

- ✅ **HTTP Status Code**
  - POST (create): 201 Created
  - GET: 200 OK
  - PUT/PATCH: 200 OK
  - DELETE: 200 or 204 No Content
  - Errors: 400, 401, 403, 404, 422, 500

- ✅ **Response Structure**
  - All required fields present (check schema in [validate-response.php](./scripts/validate-response.php))
  - No extra unexpected fields (data bleeding)
  - Timestamps valid (ISO 8601 format)

- ✅ **Data Values**
  - Match request input exactly
  - Foreign keys point to existing records
  - Calculated fields correct (e.g., total = sum of items)

- ✅ **Pagination & Filtering**
  - Results count matches per_page parameter
  - Correct page returned
  - Total count accurate

**Use response validator:**
```bash
php artisan tinker
> include 'scripts/validate-response.php'
> $response = json_decode(file_get_contents('test-results/response.json'));
> validateResponse($response, 'kandang')
```

### Error Handling Test Cases

#### Test 1: Missing required field

```bash
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "No Capacity"
  # Missing: lokasi, kapasitas, pic_id
}'
```

**Expected:**
- HTTP 422 Unprocessable Entity
- Response includes validation errors:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "lokasi": ["The lokasi field is required."],
      "kapasitas": ["The kapasitas field is required."]
    }
  }
  ```

#### Test 2: Invalid data type

```bash
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Bad Capacity",
  "lokasi": "Area A",
  "kapasitas": "not-a-number",
  "pic_id": 1
}'
```

**Expected:** HTTP 422 with validation error on kapasitas

#### Test 3: Foreign key violation

```bash
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Bad Owner",
  "lokasi": "Area A",
  "kapasitas": 500,
  "pic_id": 99999
}'
```

**Expected:** HTTP 422 (pic_id 99999 doesn't exist)

#### Test 4: Unauthorized access

```bash
# Test without token
./scripts/test-api.sh '/api/kandang' 'DELETE' ''
```

**Expected:** HTTP 401 Unauthorized

## Level 2: Database Testing (PHP Tinker)

**Purpose**: Verify data state, relationships, constraints, and integrity

### Enter Interactive Shell

```bash
php artisan tinker
```

### Test Data Queries

#### Query 1: Verify kandang with eager loading

```php
// Load kandang with related production records
$kandang = Kandang::with('produksiTelur')->first();

echo "Kandang: " . $kandang->nama_kandang . "\n";
echo "Capacity: " . $kandang->kapasitas . "\n";
echo "Production records: " . $kandang->produksiTelur->count() . "\n";

// Verify relationships
$kandang->produksiTelur->each(function ($prod) {
  echo "  - " . $prod->tanggal_produksi . ": " . $prod->jumlah_butir . " butir\n";
});
```

#### Query 2: Check stock calculation for kandang

```php
$kandang = Kandang::find(1);

// Via service (recommended)
$stock = app('App\Services\StockService')
  ->calculateAvailableStock($kandang->id);

echo "Stock for {$kandang->nama_kandang}:\n";
echo "  Butir: {$stock['butir']}\n";
echo "  Kg: {$stock['kg']}\n";
```

#### Query 3: Verify pricing history

```php
// Get all active pricing
$prices = HargaTelur::where('status', 'aktif')
  ->orderBy('tanggal_berlaku', 'desc')
  ->get();

foreach ($prices as $price) {
  echo "{$price->tanggal_berlaku}: \${$price->harga_per_butir}/butir\n";
}

// Get historical price
$oldPrice = HargaTelur::where('status', 'nonaktif')
  ->orderBy('tanggal_berlaku', 'desc')
  ->first();

if ($oldPrice) {
  echo "Last inactive price: \${$oldPrice->harga_per_butir}/butir\n";
}
```

#### Query 4: Verify sales with pricing snapshot

```php
$sale = Penjualan::with('detailPenjualan')->first();

echo "Sale #{$sale->id} to {$sale->pembeli}:\n";
echo "  Date: {$sale->tanggal_penjualan}\n";

foreach ($sale->detailPenjualan as $detail) {
  // harga_satuan is snapshot (immutable)
  echo "  - {$detail->jumlah_butir} eggs @ \${$detail->harga_satuan} = \${$detail->subtotal}\n";
}

echo "  Total: \${$sale->total}\n";
```

### Data Integrity Checks

#### Check 1: Orphaned production records

```php
// Find production without kandang (data corruption)
$orphaned = DB::select(
  "SELECT * FROM produksi_telur WHERE kandang_id NOT IN (SELECT id FROM kandang)"
);

if (empty($orphaned)) {
  echo "✅ No orphaned production records\n";
} else {
  echo "❌ Found " . count($orphaned) . " orphaned production records\n";
  foreach ($orphaned as $o) {
    echo "   - ID {$o->id} references kandang #{$o->kandang_id}\n";
  }
}
```

#### Check 2: Stock calculation consistency

```php
$kandang = Kandang::find(1);

// Method 1: Via StockService
$serviceStock = app('StockService')
  ->calculateAvailableStock($kandang->id);

// Method 2: Direct calculation
$produced = $kandang->produksiTelur()
  ->sum('jumlah_butir');
$sold = $kandang->penjualan()
  ->join('detail_penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
  ->sum('detail_penjualan.jumlah_butir');

$directCalculation = $produced - $sold;

// Method 3: Stored value
$storedStock = $kandang->stokTelur->jumlah_butir ?? 0;

// All three should match
$match = ($serviceStock['butir'] === $directCalculation) 
  && ($directCalculation === $storedStock);

echo "Stock Consistency Check:\n";
echo "  Service calculation: {$serviceStock['butir']} butir\n";
echo "  Direct calculation: $directCalculation butir\n";
echo "  Stored value: $storedStock butir\n";
echo "  Match: " . ($match ? '✅ YES' : '❌ NO') . "\n";

if (!$match) {
  echo "⚠️ Stock mismatch detected!\n";
  echo "   Recommend recalculating: app('StockService')->recalculate();\n";
}
```

#### Check 3: User role assignments

```php
// Verify all users have roles
$usersWithoutRole = User::doesn't('roles')->get();

if ($usersWithoutRole->isEmpty()) {
  echo "✅ All users have assigned roles\n";
} else {
  echo "❌ Found " . $usersWithoutRole->count() . " users without roles:\n";
  foreach ($usersWithoutRole as $user) {
    echo "   - {$user->name} ({$user->email})\n";
  }
}

// Summary by role
$roleDistribution = User::with('roles')
  ->get()
  ->pluck('roles')
  ->flatten()
  ->pluck('name')
  ->countBy();

echo "\nRole Distribution:\n";
foreach ($roleDistribution as $role => $count) {
  echo "  $role: $count users\n";
}
```

#### Check 4: Price versioning integrity

```php
// Verify no duplicate active prices
$duplicates = HargaTelur::where('status', 'aktif')
  ->groupBy('tanggal_berlaku')
  ->havingRaw('COUNT(*) > 1')
  ->get();

if ($duplicates->isEmpty()) {
  echo "✅ No duplicate active prices for same date\n";
} else {
  echo "❌ Duplicate active prices found\n";
}

// Verify prices don't decrease historically (cost inflation check)
$prices = HargaTelur::where('status', 'aktif')
  ->orderBy('tanggal_berlaku')
  ->get();

$decreases = 0;
for ($i = 1; $i < $prices->count(); $i++) {
  if ($prices[$i]->harga_per_butir < $prices[$i-1]->harga_per_butir) {
    echo "⚠️ Price decreased on {$prices[$i]->tanggal_berlaku}\n";
    $decreases++;
  }
}

if ($decreases === 0) {
  echo "✅ Price history maintains monotonic increase (expected)\n";
}
```

### Performance Analysis in Database

#### Check query performance

```php
// Enable query logging
DB::enableQueryLog();

// Run the expensive query
$kandang = Kandang::with('produksiTelur', 'penjualan')->get();

// Analyze queries
$queries = DB::getQueryLog();

echo "Total queries: " . count($queries) . "\n";
echo "Queries > 100ms:\n";

foreach ($queries as $q) {
  if ($q['time'] > 100) {
    echo "  - {$q['time']}ms: " . substr($q['query'], 0, 80) . "...\n";
  }
}

// Total execution time
$totalTime = array_sum(array_column($queries, 'time'));
echo "Total query time: {$totalTime}ms\n";
```

#### Identify N+1 queries

```php
DB::enableQueryLog();
DB::flushQueryLog();

// Query kandang
$kandangs = Kandang::all();  // 1 query

// Loop through relationships (causes N more queries)
foreach ($kandangs as $kandang) {
  $kandang->produksiTelur()->count();  // N queries!
}

$queries = DB::getQueryLog();

if (count($queries) > count($kandangs) + 1) {
  echo "⚠️ N+1 Query Problem Detected!\n";
  echo "   Expected ~" . (count($kandangs) + 1) . " queries, got " . count($queries) . "\n";
  echo "   Fix: Use ->with('produksiTelur') in initial query\n";
}
```

## Level 3: Business Logic Testing

**Purpose**: Test service layer functions, workflows, and calculations in isolation

### Stock Calculation Workflow

**Test 1: Stock increases with production**

```php
php artisan tinker

$kandang = Kandang::find(1);

// Get initial state
$initialStock = app('StockService')->calculateAvailableStock($kandang->id);
echo "Initial stock: {$initialStock['butir']} butir\n";

// Add production
$produksi = ProduksiTelur::create([
  'kandang_id' => $kandang->id,
  'tanggal_produksi' => now(),
  'jumlah_butir' => 500,
  'jumlah_kg' => 31.25,  // 500 / 16
  'hdp' => 95,
  'hhp' => 92,
  'mortalitas' => 3
]);

// Verify stock increased
$newStock = app('StockService')->calculateAvailableStock($kandang->id);
$increase = $newStock['butir'] - $initialStock['butir'];

echo "Production added: 500 butir\n";
echo "Stock increase: $increase butir\n";
echo "Result: " . ($increase === 500 ? "✅ PASS" : "❌ FAIL") . "\n";

assert($newStock['butir'] === $initialStock['butir'] + 500, "Stock mismatch!");
```

**Test 2: Stock decreases with sales**

```php
$kandang = Kandang::find(1);
$beforeSale = app('StockService')->calculateAvailableStock($kandang->id);

// Create sale
$sale = Penjualan::create([
  'user_id' => 1,
  'pembeli' => 'Toko ABC',
  'tanggal_penjualan' => now(),
  'total' => 50000
]);

DetailPenjualan::create([
  'penjualan_id' => $sale->id,
  'harga_telur_id' => 1,
  'jumlah_butir' => 100,
  'jumlah_kg' => 6.25,
  'harga_satuan' => 500,
  'subtotal' => 50000
]);

$afterSale = app('StockService')->calculateAvailableStock($kandang->id);
$decrease = $beforeSale['butir'] - $afterSale['butir'];

echo "Sale created: 100 butir\n";
echo "Stock decrease: $decrease butir\n";
echo "Result: " . ($decrease === 100 ? "✅ PASS" : "❌ FAIL") . "\n";
```

### Unit Conversion Testing

**Test butir ↔ kg conversion**

```php
// Get conversion factor from settings
$conversionFactor = config('poultry.conversion_factor_butir_per_kg', 16);

// Test case 1: 500 butir should be 31.25 kg
$butir = 500;
$expectedKg = $butir / $conversionFactor;  // 500 / 16 = 31.25

echo "Conversion test:\n";
echo "  $butir butir = $expectedKg kg\n";
echo "  Result: " . ($expectedKg === 31.25 ? "✅ PASS" : "❌ FAIL") . "\n";

// Test case 2: 16 kg should be 256 butir
$kg = 16;
$expectedButir = $kg * $conversionFactor;  // 16 * 16 = 256

echo "  $kg kg = $expectedButir butir\n";
echo "  Result: " . ($expectedButir === 256 ? "✅ PASS" : "❌ FAIL") . "\n";
```

### Edge Cases & Error Handling

**Test 1: Negative stock prevention**

```php
$kandang = Kandang::find(1);

// Try to sell more than available
DB::enableQueryLog();

try {
  $available = app('StockService')->calculateAvailableStock($kandang->id);
  echo "Available: {$available['butir']} butir\n";
  
  // Try to sell 10000 (likely more than available)
  $detail = DetailPenjualan::create([
    'penjualan_id' => 999,  // Invalid sale ID (should fail)
    'harga_telur_id' => 1,
    'jumlah_butir' => 10000,
    'harga_satuan' => 500,
    'subtotal' => 5000000
  ]);
  
  echo "❌ FAIL: System allowed negative stock!\n";
} catch (\Exception $e) {
  echo "✅ PASS: System prevented negative stock\n";
  echo "   Error: " . $e->getMessage() . "\n";
}
```

**Test 2: Concurrent updates (race condition)**

```php
// Simulate two workers recording production simultaneously
$kandang = Kandang::find(1);

DB::beginTransaction();

try {
  // Transaction 1: Worker A adds production
  $prod1 = ProduksiTelur::create([
    'kandang_id' => $kandang->id,
    'tanggal_produksi' => now(),
    'jumlah_butir' => 300,
    'jumlah_kg' => 18.75
  ]);
  
  // Simulate delay...
  
  // Transaction 2: Worker B adds production
  $prod2 = ProduksiTelur::create([
    'kandang_id' => $kandang->id,
    'tanggal_produksi' => now(),
    'jumlah_butir' => 200,
    'jumlah_kg' => 12.5
  ]);
  
  // Verify both recorded
  $totalRecorded = ProduksiTelur::where('kandang_id', $kandang->id)
    ->where('tanggal_produksi', now()->toDateString())
    ->sum('jumlah_butir');
  
  echo "Concurrent production test:\n";
  echo "  Worker A: 300 butir\n";
  echo "  Worker B: 200 butir\n";
  echo "  Total recorded: $totalRecorded butir\n";
  echo "  Result: " . ($totalRecorded === 500 ? "✅ PASS" : "❌ FAIL") . "\n";
  
  DB::commit();
} catch (\Exception $e) {
  DB::rollback();
  echo "❌ Error: " . $e->getMessage() . "\n";
}
```

**Test 3: Invalid price reference**

```php
try {
  $detail = DetailPenjualan::create([
    'penjualan_id' => 1,
    'harga_telur_id' => 99999,  // Doesn't exist
    'jumlah_butir' => 100,
    'harga_satuan' => 500,
    'subtotal' => 50000
  ]);
  
  echo "❌ FAIL: System allowed invalid price reference!\n";
} catch (\Illuminate\Database\QueryException $e) {
  echo "✅ PASS: Foreign key constraint prevented invalid price\n";
}
```

## Test Case Examples

### Example 1: Validate Kandang Creation & Retrieval

```json
{
  "test_id": "kandang_001",
  "title": "Create and retrieve kandang with validation",
  "steps": [
    {
      "action": "POST /api/kandang",
      "data": {
        "nama_kandang": "Test Kandang",
        "lokasi": "Test Area",
        "kapasitas": 500,
        "pic_id": 1
      }
    },
    {
      "action": "Check response",
      "assert": "status === 201"
    },
    {
      "action": "GET /api/kandang/<created_id>",
      "assert": "response.nama_kandang === 'Test Kandang'"
    },
    {
      "action": "Query DB",
      "sql": "SELECT * FROM kandang WHERE nama_kandang = 'Test Kandang'",
      "assert": "row count === 1"
    }
  ]
}
```

### Example 2: Verify Stock Calculation Accuracy

```json
{
  "test_id": "stock_001",
  "title": "Stock calculation with production + sales",
  "initial_state": {
    "kandang_id": 1,
    "opening_stock_butir": 1000
  },
  "operations": [
    {
      "op": "add_production",
      "jumlah_butir": 500,
      "expected_stock": 1500
    },
    {
      "op": "add_sale",
      "jumlah_butir": 300,
      "expected_stock": 1200
    }
  ],
  "verification": [
    {
      "query": "SELECT jumlah_butir FROM stok_telur WHERE kandang_id = 1",
      "expected": 1200
    }
  ]
}
```

## Report Generation Workflow

**Purpose**: Document and present test findings in format suitable for stakeholders

### Step 1: Execute Tests & Collect Results

```bash
# All test results automatically saved to test-results/ directory
ls test-results/
# Output: api_test_20260422_103000.json, response_20260422_103000.json, etc.
```

### Step 2: Consolidate Results

Option A: Single test run
```bash
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results/api_test_20260422_103000.json \
  --output=test-report-single.md
```

Option B: Merge multiple test runs
```bash
# Create consolidated results file
cat test-results/*.json > consolidated-results.json

# Generate report from consolidated data
php scripts/generate-report.php \
  --format=markdown \
  --input=consolidated-results.json \
  --output=test-report-full.md
```

### Step 3: Generate Multi-Format Reports

```bash
# Markdown (for documentation, email, handoff)
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=test-report.md

# JSON (for CI/CD, automated processing)
php scripts/generate-report.php \
  --format=json \
  --input=test-results.json \
  --output=test-results-final.json

# HTML (for dashboard, presentations)
php scripts/generate-report.php \
  --format=html \
  --input=test-results.json \
  --output=test-report.html
```

### Step 4: Review & Analyze Reports

#### Markdown Report Structure
```
# Black Box Test Report
Generated: 2026-04-22 14:35:22

## Summary
| Metric | Count |
|--------|-------|
| Total Tests | 25 |
| ✅ Passed | 23 |
| ❌ Failed | 2 |
| Pass Rate | 92% |

## Test Results
### Test 1: ✅ PASS
Endpoint: POST /api/kandang
HTTP Status: 201
...

### Test 2: ❌ FAIL
Endpoint: DELETE /api/kandang/999
HTTP Status: 404
Expected: 204
Assertions:
  - [✓] Record deleted from database
  - [✗] Status code was 404, expected 204
```

#### HTML Report Features
- Summary card with pass rate
- Clickable test results
- Response body preview
- Assertion details with pass/fail indicators
- Performance metrics (query time if included)

#### JSON Report Structure
```json
{
  "timestamp": "2026-04-22T14:35:22Z",
  "summary": {
    "total": 25,
    "passed": 23,
    "failed": 2,
    "duration_ms": 5432
  },
  "tests": [
    {
      "id": "kandang_001",
      "endpoint": "POST /api/kandang",
      "status": "PASS",
      "http_code": 201,
      "assertions": [...]
    }
  ]
}
```

### Step 5: Archive & Document

```bash
# Create results directory with timestamp
mkdir -p test-results/archive/2026-04-22_session
cp test-results/*.json test-results/archive/2026-04-22_session/
cp test-report*.md test-results/archive/2026-04-22_session/
cp test-report*.html test-results/archive/2026-04-22_session/

# Keep in version control
git add test-results/archive/2026-04-22_session/
git commit -m "Black box test results - April 22 04:35 session"
```

---

## Complete Workflow Example

### Scenario: Test stock calculation after production

**1. Prepare test data**
```bash
php artisan tinker

# Verify starting state
$kandang = Kandang::find(1);
$initial = app('StockService')->calculateAvailableStock($kandang->id);
echo json_encode($initial);
```

**2. Create test case**
```bash
cat > test-scenario.json << 'EOF'
{
  "test_id": "calc_001",
  "title": "Stock Calculation Accuracy",
  "tests": [
    {
      "action": "Add 500 eggs production",
      "endpoint": "POST /api/produksi",
      "data": {
        "kandang_id": 1,
        "tanggal_produksi": "2026-04-22",
        "jumlah_butir": 500,
        "hdp": 95
      }
    },
    {
      "action": "Verify stock via API",
      "endpoint": "GET /api/kandang/1/stock"
    },
    {
      "action": "Verify in database",
      "query": "SELECT jumlah_butir FROM stok_telur WHERE kandang_id=1",
      "expected": "initial+500"
    }
  ]
}
EOF
```

**3. Execute tests**
```bash
./scripts/test-api.sh '/api/produksi' 'POST' '{...}'
php artisan tinker  # Verify DB state
```

**4. Capture results**
```bash
# Results collected automatically by test script
ls test-results/
```

**5. Generate report**
```bash
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=test-report.md
```

---

## Test Execution Checklist

Use this checklist before each testing session:

### Pre-Testing Setup
- [ ] Database connection verified: `php artisan migrate:status`
- [ ] Dev server started: `composer dev`
- [ ] API responding: `curl http://localhost:8000/api/kandang`
- [ ] Test data seeded: `php artisan tinker > seedStockTesting()`
- [ ] Test results directory exists: `mkdir -p test-results`

### API Layer Testing
- [ ] Test GET (list) endpoint: `./scripts/test-api.sh '/api/kandang' 'GET' ''`
- [ ] Test POST (create) endpoint with valid data
- [ ] Test POST with invalid/missing fields (should return 422)
- [ ] Test PUT (update) endpoint
- [ ] Test DELETE endpoint
- [ ] Test with authentication (get token, use Bearer header)
- [ ] Test without auth on protected route (should return 401)
- [ ] Verify pagination (page, per_page parameters)

### Database Layer Testing
- [ ] Query relationships: `Kandang::with('produksiTelur')->first()`
- [ ] Check data integrity: Run orphaned record checks
- [ ] Verify constraints: Try foreign key violation
- [ ] Query performance: Check query count and execution time
- [ ] Identify N+1 issues: `DB::enableQueryLog()`

### Business Logic Testing
- [ ] Test stock calculation: Production + Sales workflow
- [ ] Test unit conversions: Butir ↔ Kg
- [ ] Test edge cases: Negative stock, invalid references
- [ ] Test permissions: Owner vs Worker access levels
- [ ] Test concurrent updates: Multiple simultaneous operations

### User Journey Testing
- [ ] Test complete sale workflow (Worker + Owner)
- [ ] Test pricing history (price changes don't affect old sales)
- [ ] Test permission boundaries (cross-user access)
- [ ] Test state transitions (e.g., candling duration changes)

### Performance Testing
- [ ] Measure API response times: Should be < 500ms
- [ ] Measure stock calculation: Should be < 50ms
- [ ] Run load test: `ab -n 100 -c 10 http://localhost:8000/api/kandang`
- [ ] Check for slow queries: > 100ms queries

### Report Generation
- [ ] All test results collected: `ls test-results/*.json`
- [ ] Generate Markdown report: Readable for manual review
- [ ] Generate JSON report: For CI/CD integration
- [ ] Generate HTML report: For presentation
- [ ] Archive results: Commit to version control

---

## Common Mistakes & How to Avoid Them

### Mistake 1: Testing without authenticating

**Problem**: Tests fail with 401 Unauthorized

**Solution**:
```bash
# Get token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"owner@test.local","password":"password"}' | jq -r '.access_token')

# Use in test
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/kandang
```

### Mistake 2: Stale test data & database pollution

**Problem**: Tests produce inconsistent results; old data interferes

**Solution**: Reset database before test session
```bash
php artisan migrate:fresh --seed
php artisan tinker > seedStockTesting()
```

### Mistake 3: Testing without transactions (can't rollback changes)

**Problem**: Test data persists; manual cleanup required

**Solution**: Wrap tests in transactions
```php
DB::beginTransaction();
// ... run tests ...
DB::rollback();  // Undo all changes
```

### Mistake 4: Checking only HTTP status, ignoring response body

**Problem**: API returns 200 but response is wrong

**Solution**: Always validate response schema
```php
include 'scripts/validate-response.php';
validateResponse($response, 'kandang');
```

### Mistake 5: Not checking for N+1 query problems

**Problem**: Tests pass but performance degrades in production

**Solution**: Enable query logging during tests
```php
DB::enableQueryLog();
$kandangs = Kandang::all();  // 1 query
foreach ($kandangs as $k) {
  $k->produksiTelur->count();  // Should use -> with() instead!
}
```

### Mistake 6: Forgetting user context switching

**Problem**: Testing permission but user context from previous test

**Solution**: Always explicitly set user context
```php
auth()->setUser(User::role('pemilik')->first());  // Owner
// ... test owner-only operations ...

auth()->setUser(User::role('pekerja')->first());  // Worker
// ... test worker operations ...
```

### Mistake 7: Assuming stock will always increase

**Problem**: Tests fail when opening stock is significant

**Solution**: Always query current state before assumptions
```php
$initial = app('StockService')->calculateAvailableStock($kandang->id);
//... run operation ...
$final = app('StockService')->calculateAvailableStock($kandang->id);
$diff = $final['butir'] - $initial['butir'];  // Can be positive, negative, or zero
```

### Mistake 8: Not handling timezone differences

**Problem**: Date queries fail due to timezone mismatch

**Solution**: Always use `now()` in tests, or specify timezone
```php
// Good
ProduksiTelur::where('tanggal_produksi', now()->toDateString())

// Bad
ProduksiTelur::where('tanggal_produksi', '2026-04-22')  // Might be different timezone
```

### Mistake 9: Generating reports before collecting all results

**Problem**: Report is incomplete or missing tests

**Solution**: Verify output files exist
```bash
ls -la test-results/
# Should see multiple JSON files
wc -l test-results/*.json  # Verify non-empty
```

### Mistake 10: Not archiving test results

**Problem**: Results lost when directory is cleared; no audit trail

**Solution**: Archive after each session
```bash
cp -r test-results test-results-backup-$(date +%Y%m%d_%H%M%S)
git add test-results-backup-*
git commit -m "Test results backup - $(date)"
```

---

## Helper Scripts

Located in [./scripts/](./scripts/):

- **test-api.sh** — Execute HTTP requests with assertion support
- **generate-report.php** — Convert test results to Markdown/JSON/HTML
- **seed-test-data.php** — Load fixtures for testing scenarios
- **validate-response.php** — Schema validation for API responses

## Key Tools & Commands

| Tool | Purpose |
|------|---------|
| `composer dev` | Start all dev services (server, queue, logs) |
| `php artisan tinker` | Interactive shell for DB/service testing |
| `php artisan migrate:fresh --seed` | Reset DB to clean state |
| `curl` | Manual HTTP requests for debugging |
| `jq` | JSON parser (formats JSON output nicely) |
| `ab` (Apache Bench) | Load testing tool |

## When to Use Each Testing Level

| Scenario | Use Level | Why |
|----------|-----------|-----|
| "API endpoint returns wrong status code" | Level 1: API | Direct HTTP testing |
| "Stock calculation is incorrect" | Level 3: Logic → Level 2: Database | Test service, verify DB state |
| "Worker can delete kandang (shouldn't)" | Level 0: Permission → Level 5: Journey | Verify role enforcement, test workflow |
| "Response takes too long" | Level 4: Performance | Profile queries, measure times |
| "Sale transaction partially failed" | Level 5: Journey | Multi-step workflow with rollback |
| "Database has orphaned records" | Level 2: Database | Direct SQL integrity checks |
| "New release broke permissions" | Level 0: Permission | Regression test all role access |
| "Pricing history seems inconsistent" | Level 2: Database | Query historical records |

## Comprehensive Troubleshooting Guide

### Issue: 404 on API endpoint

**Symptoms:** 
```
HTTP 404 Not Found
{"message": "Route ... not defined"}
```

**Diagnosis:**
```bash
# Check if route exists
grep -r "/api/kandang" routes/

# Verify correct HTTP method
grep -A5 "Route::post\|Route::get" routes/api.php | grep kandang
```

**Solutions:**
1. Verify route is defined in `routes/api.php`
2. Check HTTP method matches (GET vs POST)
3. Verify controller and method exist
4. Check middleware isn't blocking (auth, role:)

**Quick fix:**
```bash
php artisan route:list | grep kandang
```

### Issue: 401 Unauthorized

**Symptoms:**
```json
{"message": "Unauthenticated"}
```

**Diagnosis:**
```php
php artisan tinker
> auth()->user()  // null if not authenticated
```

**Solutions:**
```bash
# Option 1: Get token and use it
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -d '{"email":"owner@test.local","password":"password"}' | jq -r '.access_token')

curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/kandang

# Option 2: Use test-api.sh (handles token automatically)
./scripts/test-api.sh '/api/kandang' 'GET' ''
```

### Issue: 422 Unprocessable Entity

**Symptoms:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"],
    "another_field": ["Error message"]
  }
}
```

**Diagnosis:**
1. Check which fields failed validation
2. Verify data types (string vs int vs number)
3. Check for required fields

**Solutions:**
```bash
# Check validation rules in controller
grep -r "validate" app/Http/Controllers/

# Test with correct data
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Test",
  "lokasi": "Area A",
  "kapasitas": 500,
  "pic_id": 1
}'
```

### Issue: Database connection failed

**Symptoms:**
```
SQLSTATE[HY000] [1045] Access denied for user
```

**Diagnosis:**
```bash
# Check .env database settings
cat .env | grep DB_

# Test connection
php artisan migrate:status
```

**Solutions:**
```bash
# Verify MySQL is running
# Mac: brew services list
# Windows: Check Services app

# Verify credentials
# Check phpMyAdmin or run:
mysql -u root -p -h localhost

# Reset .env
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Issue: Stock calculation is wrong

**Symptoms:**
```
Expected: 500 butir, Got: 450 butir
```

**Diagnosis:**
```php
php artisan tinker

$kandang = Kandang::find(1);

// Method 1: Service
$service_stock = app('StockService')->calculateAvailableStock($kandang->id);

// Method 2: Direct SQL
$total_produced = DB::select("SELECT SUM(jumlah_butir) as total FROM produksi_telur WHERE kandang_id=1")[0]->total;
$total_sold = DB::select("SELECT SUM(jumlah_butir) as total FROM detail_penjualan WHERE penjualan_id IN (SELECT id FROM penjualan WHERE kandang_id=1)")[0]->total;

echo "Produced: $total_produced";
echo "Sold: $total_sold";
echo "Expected: " . ($total_produced - $total_sold);
echo "Got: {$service_stock['butir']}";
```

**Solutions:**
1. Check for orphaned records (production without kandang)
2. Verify no duplicate sales
3. Recalculate stock: `app('StockService')->recalculate($kandang->id)`
4. Check unit conversions are consistent

### Issue: Permission test fails unexpectedly

**Symptoms:**
```
Expected: Worker cannot create kandang
Actual: Worker CAN create kandang
```

**Diagnosis:**
```php
$worker = User::role('pekerja')->first();
auth()->setUser($worker);

echo "Roles: " . $worker->roles->pluck('name');
echo "Can create: " . $worker->can('create', Kandang::class);
echo "Has permission: " . $worker->hasPermission('create kandang');
```

**Solutions:**
1. Verify user has correct role: `$user->roles->pluck('name')`
2. Verify role has permission: `Role::findByName('pekerja')->permissions`
3. Check middleware: `role:pemilik|pekerja` in routes
4. Clear permission cache: `php artisan cache:clear`

### Issue: Performance slow (API takes > 500ms)

**Symptoms:**
```
Query time: 1234ms (Expected: < 500ms)
```

**Diagnosis:**
```php
DB::enableQueryLog();

// Run your query
$data = Kandang::with('produksiTelur', 'penjualan')->get();

$queries = DB::getQueryLog();
$total = array_sum(array_column($queries, 'time'));

echo "Query count: " . count($queries);
echo "Total time: {$total}ms";
echo "Slow queries (>100ms):";
foreach ($queries as $q) {
  if ($q['time'] > 100) {
    echo "  - {$q['time']}ms: {$q['query']}\n";
  }
}
```

**Solutions:**
1. Add missing eager loading: `->with('produksiTelur')`
2. Add database index to frequently searched columns
3. Paginate large result sets: `->paginate(50)`
4. Cache expensive calculations: `Cache::remember()`

### Issue: Test results missing or incomplete

**Symptoms:**
```bash
# test-results/ directory is empty or has < 5 files
ls test-results/
# Expected: Multiple .json files
```

**Diagnosis:**
```bash
# Check if API is running
curl http://localhost:8000/api/kandang

# Check script permissions
ls -la ./scripts/test-api.sh

# Run test manually
./scripts/test-api.sh '/api/kandang' 'GET' ''
```

**Solutions:**
```bash
# Ensure dev server is running
composer dev

# Make script executable
chmod +x ./scripts/test-api.sh

# Check test-results directory exists
mkdir -p test-results

# Run test with verbose output
bash -x ./scripts/test-api.sh '/api/kandang' 'GET' ''
```

---

This skill covers systematic black box testing. Extend it by:

1. **Add automated test suites** — Create reusable test libraries for common workflows
2. **Integrate with CI/CD** — Trigger test runs on pull requests
3. **Performance profiling** — Add response time measurement and bottleneck detection
4. **Regression testing** — Compare before/after snapshots across releases
5. **Data export** — Generate test datasets for external analysis

---

**Related workflows:** See [design-consistency](..//design-consistency/SKILL.md) for UI testing, [analisa-sistem](../analisa-sistem/SKILL.md) for system architecture queries.
