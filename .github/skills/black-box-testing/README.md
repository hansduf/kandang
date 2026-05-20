# Black Box Testing Skill

Complete framework for testing the poultry management system without going through the UI. This skill provides automated tools, test scenario documentation, and report generation.

## 📦 What's Included

```
.github/skills/black-box-testing/
├── SKILL.md                    # Main skill documentation
├── README.md                   # This file
├── scripts/                    # Executable helper scripts
│   ├── test-api.sh            # Run API tests
│   ├── generate-report.php    # Generate Markdown/JSON/HTML reports
│   ├── seed-test-data.php     # Create test fixtures
│   └── validate-response.php  # Schema validation
└── references/                # Reference documentation
    ├── TEST_SCENARIOS.md      # Detailed test cases & examples
    └── QUICK_COMMANDS.md      # Command reference
```

## 🚀 Quick Start

### 1. Start Development Server
```bash
composer dev
```
Starts all services: Laravel server, queue, logs, Vite.

### 2. Test an API Endpoint
```bash
./scripts/test-api.sh '/api/kandang' 'GET' ''
```

### 3. Seed Test Data
```bash
php artisan tinker
> include 'scripts/seed-test-data.php'
> seedStockTesting()
```

### 4. Verify with Database Query
```bash
php artisan tinker
> Kandang::with('produksiTelur')->first()
> app('StockService')->calculateAvailableStock(1)
```

### 5. Generate Report
```bash
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=test-report.md
```

## 🎯 Three Testing Levels

### Level 1: API Testing (cURL)
Test HTTP endpoints directly without browser UI.

**When to use:** Verify endpoint availability, response structure, status codes.

```bash
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "New Coop",
  "kapasitas": 1000,
  "pic_id": 1
}'
```

### Level 2: Database Testing (SQL/Tinker)
Query database directly to verify data integrity.

**When to use:** Check record constraints, relationships, stock calculations.

```php
php artisan tinker
> Kandang::with('produksiTelur')->find(1)
> DB::select('SELECT * FROM stok_telur WHERE kandang_id = ?', [1])
```

### Level 3: Business Logic Testing (PHP)
Execute service layer functions to validate workflows.

**When to use:** Test complex calculations, role permissions, state transitions.

```php
> app('StockService')->calculateAvailableStock(1)
> $user->hasPermission('create kandang')
```

## 📋 Common Test Scenarios

See [TEST_SCENARIOS.md](./references/TEST_SCENARIOS.md) for detailed test cases:

1. **Kandang Lifecycle** — CRUD operations
2. **Stock Calculation** — Production + Sales accuracy
3. **Permission & Roles** — RBAC enforcement
4. **Pricing History** — Sales snapshot integrity
5. **Data Validation** — Constraint enforcement
6. **Report Generation** — Document test results

## 📊 Report Formats

### Markdown Report
Human-readable documentation with summary, details, assertions.

```bash
php scripts/generate-report.php \
  --format=markdown \
  --input=test-results.json \
  --output=report.md
```

### JSON Report
Machine-parseable for CI/CD integration and archiving.

```bash
php scripts/generate-report.php \
  --format=json \
  --input=test-results.json \
  --output=results.json
```

### HTML Report
Interactive dashboard with charts and test breakdowns.

```bash
php scripts/generate-report.php \
  --format=html \
  --input=test-results.json \
  --output=report.html
```

## 🔧 Helper Scripts

### test-api.sh
Execute HTTP requests and capture results.

```bash
./scripts/test-api.sh '<endpoint>' '<method>' '<json_data>'
# Results saved to test-results/ directory
```

### generate-report.php
Convert test results to various formats.

```bash
php scripts/generate-report.php \
  --format=<markdown|json|html> \
  --input=<file> \
  --output=<file>
```

### seed-test-data.php
Load test fixtures into database.

```php
php artisan tinker
> include 'scripts/seed-test-data.php'
> seedStockTesting()          # Full test scenario
> seedPermissionTesting()     # Permission testing
> resetDb()                   # Reset database
> cleanupTest()               # Clean test data
```

### validate-response.php
Validate API responses match expected schema.

```php
php artisan tinker
> include 'scripts/validate-response.php'
> validateResponse($response, 'kandang')
# Checks required fields, types, forbidden fields
```

## 📚 Documentation Structure

| Document | Purpose |
|----------|---------|
| [SKILL.md](./SKILL.md) | Complete testing guide with procedures |
| [TEST_SCENARIOS.md](./references/TEST_SCENARIOS.md) | Detailed test cases with expected results |
| [QUICK_COMMANDS.md](./references/QUICK_COMMANDS.md) | Command reference & troubleshooting |
| [README.md](./README.md) | This file — overview & quick start |

## 💡 Typical Workflow

```
1. Start services
   └─ composer dev

2. Prepare test scenario
   └─ php artisan tinker > seedStockTesting()

3. Run API tests
   └─ ./scripts/test-api.sh '/api/kandang' 'GET' ''

4. Verify database state
   └─ php artisan tinker > Kandang::count()

5. Test business logic
   └─ php artisan tinker > app('StockService')->calculateAvailableStock(1)

6. Collect results
   └─ ls test-results/

7. Generate report
   └─ php scripts/generate-report.php --format=markdown --input=test-results.json --output=test-report.md

8. Review findings
   └─ cat test-report.md
```

## 🔍 Key Features

✅ **No UI Required** — Pure API, database, and service-layer testing  
✅ **Comprehensive** — API, database, business logic coverage  
✅ **Reproducible** — Seed fixtures for consistent starting state  
✅ **Documented** — Detailed test scenarios with expected results  
✅ **Automated** — Scripts and report generation  
✅ **Multi-Format** — Markdown, JSON, HTML reports  
✅ **Project-Specific** — Tailored for poultry management system  

## 🛠️ Tools Used

- **cURL** — HTTP requests
- **PHP Tinker** — Interactive shell & queries
- **Laravel Artisan** — CLI commands
- **JSON** — Data formats
- **bash/PHP** — Scripting

## 📖 Examples

See [references/TEST_SCENARIOS.md](./references/TEST_SCENARIOS.md) for complete examples:

### Example 1: Create & Verify Kandang
```bash
# Create
./scripts/test-api.sh '/api/kandang' 'POST' '{
  "nama_kandang": "Test Kandang",
  "lokasi": "Area 1",
  "kapasitas": 500,
  "pic_id": 1
}'

# Verify (Tinker)
Kandang::where('nama_kandang', 'Test Kandang')->first()
```

### Example 2: Test Stock Calculation
```bash
# Add production
./scripts/test-api.sh '/api/produksi' 'POST' '{...}'

# Verify stock (Tinker)
$stock = app('StockService')->calculateAvailableStock(1)
assert($stock['butir'] === 500)
```

### Example 3: Check Permissions
```bash
# Load helpers (Tinker)
include 'scripts/seed-test-data.php'
$test = seedPermissionTesting()

# Verify
auth()->setUser($test['worker'])
$test['worker']->can('create', 'kandang')  # false
```

## 🚨 Troubleshooting

| Problem | Solution |
|---------|----------|
| `composer dev` doesn't start | Run `composer install` first |
| 404 on API endpoint | Check `routes/api.php` for endpoint definition |
| Database connection failed | Verify `.env` database settings, run migrations |
| Permission denied | Check user roles via `User::find(id)->roles` |
| Stock mismatch | Run stock recalculation, check for orphaned records |
| Report won't generate | Verify `jq` is installed for JSON parsing |

## 📞 Getting Help

- Check [QUICK_COMMANDS.md](./references/QUICK_COMMANDS.md) for command reference
- Review [TEST_SCENARIOS.md](./references/TEST_SCENARIOS.md) for detailed examples
- Check logs: `tail -f storage/logs/laravel.log`
- Use Tinker for interactive debugging: `php artisan tinker`

## 🎓 Next Steps

1. **Run the quick start workflow** above
2. **Explore test scenarios** in references
3. **Create custom test cases** using provided tools
4. **Integrate with CI/CD** using JSON report format
5. **Build regression test suite** with documented cases

---

**Skill Name:** black-box-testing  
**Version:** 1.0  
**Created:** April 2026  
**For:** Hans Jaya Poultry Management System
