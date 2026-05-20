# Quick Command Reference

Fast lookup for common black box testing commands and workflows.

## Start Development Environment

```bash
# Start all services (Laravel server, queue, logs, Vite)
composer dev

# Or individually:
php artisan serve                    # Port 8000
php artisan queue:listen            # Queue processing
php artisan pail                     # Log viewer
npm run dev                          # Asset compilation
```

## Testing Commands

### API Testing

```bash
# Test basic endpoint
./scripts/test-api.sh '/api/kandang' 'GET' ''

# Create resource
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "New Coop",
  "lokasi": "Area A",
  "kapasitas": 1000,
  "pic_id": 1
}'

# Update resource
./scripts/test-api.sh '/api/kandang/1' 'PUT' '{
  "kapasitas": 1200
}'

# Delete resource
./scripts/test-api.sh '/api/kandang/1' 'DELETE' ''
```

### Database Testing (via Tinker)

```bash
# Enter interactive shell
php artisan tinker

# In Tinker shell:

# Query kandang
Kandang::all()
Kandang::find(1)
Kandang::where('pic_id', 1)->get()

# Count records
Kandang::count()
ProduksiTelur::where('kandang_id', 1)->count()

# Check stock
app('StockService')->calculateAvailableStock(1)

# List users with roles
User::with('roles')->get()

# Verify permissions
$user->hasRole('pemilik')
$user->hasPermission('create kandang')

# Raw SQL
DB::select('SELECT * FROM kandang')
DB::select('SELECT * FROM stok_telur WHERE kandang_id = ?', [1])
```

### Data Seeding (via Tinker)

```bash
# Load test data helpers
include 'scripts/seed-test-data.php'

# Seed full stock testing scenario
seedStockTesting()

# Seed permission testing scenario
seedPermissionTesting()

# Create single resources
User::factory()->create(['email' => 'test@local'])
Kandang::factory()->create(['nama_kandang' => 'Test'])
ProduksiTelur::factory()->create(['kandang_id' => 1])

# Reset database
resetDb()

# Clean test data
cleanupTest()
```

### Response Validation (via Tinker)

```bash
# Load response validator
include 'scripts/validate-response.php'

# Validate response
$response = ['id' => 1, 'nama_kandang' => 'A', 'kapasitas' => 500];
validateResponse($response, 'kandang')

# Available schemas:
# - kandang            (single coop record)
# - kandang_list       (array of coops)
# - produksi_telur     (production record)
# - stok_telur         (stock record)
# - penjualan          (sales transaction)
# - user               (user account)
```

## Report Generation

```bash
# Generate Markdown report (human-readable)
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results/api_test_20260422_103000.json \
  --output=test-report.md

# Generate JSON report (CI/CD integration)
php scripts/generate-report.php \
  --format=json \
  --input=test-results/api_test_20260422_103000.json \
  --output=test-results-final.json

# Generate HTML report (visual dashboard)
php scripts/generate-report.php \
  --format=html \
  --input=test-results/api_test_20260422_103000.json \
  --output=test-report.html
```

## Database Management

```bash
# Reset to clean state
php artisan migrate:fresh

# Reset with seed data
php artisan migrate:fresh --seed

# Run specific migration
php artisan migrate --path=database/migrations/2026_04_22_create_kandang_table.php

# Rollback last migration
php artisan migrate:rollback

# See migration status
php artisan migrate:status
```

## Logging & Debugging

```bash
# Stream live logs
php artisan pail

# View recent logs
tail -f storage/logs/laravel.log

# Clear all logs
php artisan cache:clear

# Debug mode on/off
# Edit .env: APP_DEBUG=true (development) / false (production)
```

## Common Workflows

### Test Full Stock Lifecycle

```bash
# 1. Start server
composer dev

# 2. In terminal 2 (Tinker)
php artisan tinker
> include 'scripts/seed-test-data.php'
> $test = seedStockTesting()

# 3. In terminal 3 (API testing)
./scripts/test-api.sh '/api/produksi' 'POST' '{...}'
./scripts/test-api.sh '/api/penjualan' 'POST' '{...}'

# 4. Back to Tinker (verify)
> $stock = app('StockService')->calculateAvailableStock(1)
> dd($stock)

# 5. Generate report
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=report.md
```

### Verify Role Permissions

```bash
php artisan tinker

# Load helpers
include 'scripts/seed-test-data.php'
$test = seedPermissionTesting()

# Test owner access
auth()->setUser($test['owner'])
$test['owner']->hasRole('pemilik')              # true
$test['owner']->can('create', 'kandang')        # true

# Test worker access
auth()->setUser($test['worker'])
$test['worker']->hasRole('pekerja')             # true
$test['worker']->can('create', 'kandang')       # false
$test['worker']->can('view', 'kandang')         # true
```

### Debug Stock Calculation Error

```bash
php artisan tinker

# 1. Get kandang
$kandang = Kandang::find(1)

# 2. Show all related data
$kandang->produksiTelur()->sum('jumlah_butir')   # Total produced
$kandang->penjualan()->sum('jumlah_butir')       # Total sold
$kandang->stokTelur->jumlah_butir                # Stored stock

# 3. Calculate what it should be
$expected = $kandang->produksiTelur()->sum('jumlah_butir')
          - $kandang->penjualan()->sum('jumlah_butir')

$actual = $kandang->stokTelur->jumlah_butir

echo "Expected: $expected, Actual: $actual, Match: " . ($expected === $actual ? 'YES' : 'NO')

# 4. If mismatch, check for orphaned records
DB::select("SELECT * FROM produksi_telur WHERE kandang_id NOT IN (SELECT id FROM kandang)")
```

### Manual HTTP Request (cURL)

```bash
# Simple GET
curl http://localhost:8000/api/kandang

# POST with data
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"nama_kandang": "Test", "kapasitas": 500, "pic_id": 1}' \
  http://localhost:8000/api/kandang

# PUT with data
curl -X PUT \
  -H "Content-Type: application/json" \
  -d '{"kapasitas": 600}' \
  http://localhost:8000/api/kandang/1

# DELETE
curl -X DELETE http://localhost:8000/api/kandang/1

# With authentication (Bearer token)
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/kandang
```

## Useful Artisan Commands

```bash
php artisan tinker                   # Interactive shell
php artisan migrate                  # Run migrations
php artisan migrate:fresh           # Reset DB
php artisan seed:run                # Run seeders
php artisan test                    # Run test suite
php artisan cache:clear             # Clear cache
php artisan queue:listen            # Process queued jobs
php artisan pail                    # Stream logs
php artisan make:model Model        # Create model
php artisan make:migration table     # Create migration
php artisan make:controller Name    # Create controller
php artisan make:test TestName      # Create test
php artisan optimize                # Production optimization
```

## File Locations

| Path | Purpose |
|------|---------|
| `database/migrations/` | Database schema files |
| `app/Models/` | Eloquent models |
| `app/Http/Controllers/` | API controllers |
| `app/Services/` | Business logic |
| `routes/api.php` | API endpoint definitions |
| `routes/web.php` | Web route definitions |
| `storage/logs/` | Application logs |
| `test-results/` | Test output files |

## Environment Setup (.env)

```bash
# Copy example
cp .env.example .env

# Generate app key
php artisan key:generate

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=poultry
DB_USERNAME=root
DB_PASSWORD=

# Debug mode
APP_DEBUG=true  # development
APP_DEBUG=false # production

# App URL
APP_URL=http://localhost:8000
```

## Troubleshooting Quick Fixes

```bash
# 500 error? Check logs
tail -f storage/logs/laravel.log

# Database connection failed?
php artisan migrate:status

# Models not loading?
php artisan ide-helper:generate

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Permissions issue?
chmod -R 777 storage bootstrap/cache

# Composer issues?
composer dump-autoload
composer update
```

---

**All test results saved to:** `test-results/` directory  
**All reports generated to:** Current directory (specify with --output flag)  
**Log file location:** `storage/logs/laravel.log`
