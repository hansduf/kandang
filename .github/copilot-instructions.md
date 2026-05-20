---
name: Hans Jaya Poultry - Development Guidelines
description: "Workspace instructions for the poultry management system. Covers architecture, conventions, build commands, and common workflows."
---

# Hans Jaya Poultry - AI Assistant Guidelines

**Last updated:** April 2026  
**Project:** Larvel-based poultry production & inventory management system  
**Stack:** Laravel 12 (PHP 8.2+), Vite, Tailwind CSS, Alpine.js, MySQL

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL or SQLite

### Initial Setup
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Create database and run migrations
php artisan migrate

# Start development server (all services concurrently)
composer dev
```

This single command runs:
- PHP application server (`:8000`)
- Queue listener
- Log viewer (Pail)
- Vite asset compiler

### Build for Production
```bash
npm run build        # Optimize frontend assets
php artisan optimize # Cache routes, config, etc.
```

---

## 📋 Key Commands

| Command | Purpose |
|---------|---------|
| `php artisan serve` | Start Laravel dev server |
| `php artisan migrate` | Run database migrations |
| `php artisan tinker` | Interactive shell (REPL) |
| `php artisan queue:listen` | Process queued jobs |
| `npm run dev` | Watch and compile Vite assets |
| `npm run build` | Minify/optimize for production |
| `php artisan test` | Run PHPUnit tests |
| `php artisan pail` | Stream application logs |

---

## 🏗️ Architecture Overview

### Core Layers

**Models** (`app/Models/`)
- **Kandang** (Coops): Physical enclosures with capacity & supervisor (pic_id)
- **ProduksiTelur** (Production): Daily egg output + health metrics (HDP, HHP, Mortality)
- **Penjualan** (Sales): Transaction headers; aggregates DetailPenjualan items
- **DetailPenjualan**: Sales line items with pricing snapshot
- **HargaTelur** (Pricing): Price catalog with temporal versioning
- **StokTelur** (Inventory): Tracked via cumulative accounting (opening + production - sales)
- **User**: RBAC with roles (pemilik=owner, pekerja=worker)

**Services** (`app/Services/`)
- **StockService**: Cumulative accounting for inventory (calculateAvailableStock, unit conversions)
- Centralizes business logic away from controllers

**Controllers** (`app/Http/Controllers/`)
- **Lean pattern**: validation → service call → response
- Eager loading (`->with()`) to prevent N+1 queries
- Pagination with 50 items/page default

**Middleware** (`app/Http/Middleware/`)
- `auth`: Ensures user is authenticated
- `verified`: Email verification check
- `role:pemilik|pekerja`: Role-based access control (see RBAC below)

---

## 👥 Role-Based Access Control (RBAC)

Uses **Spatie Laravel Permission** package. Two primary roles:

| Role | Capabilities |
|------|--------------|
| **pemilik** (Owner) | Full CRUD: coops, pricing, sales, reports, user management |
| **pekerja** (Worker) | Production data entry & view reports (read-only on other records) |

Route protection applied via middleware:
```blade
Route::middleware(['auth', 'verified', 'role:pemilik|pekerja'])->group(...)
```

---

## 💾 Database Conventions

### Key Naming Patterns
- **Table names**: Snake_case plural (e.g., `kandang`, `produksi_telur`, `detail_penjualan`)
- **Columns**: Snake_case (e.g., `jumlah_ayam`, `tanggal_produksi`)
- **Primary keys**: `id` (auto-increment bigint)
- **Foreign keys**: `{table_singular}_id` (e.g., `kandang_id`, `user_id`)

### Unit Handling
All entities with measurements stored **in both units** (butir/kg):
- `jumlah_butir` (eggs, integer)
- `jumlah_kg` (kilograms, decimal)
- Conversion factor stored in `pengaturan` table (default: 16 butir per kg)

### Timestamps & Soft Deletes
All models automatically include `created_at` and `updated_at`.  
Soft deletes available but not currently used (implement via `SoftDeletes` trait).

---

## 🔄 Development Workflow

### 1. Create a New Feature

**Step 1:** Create migration
```bash
php artisan make:migration create_table_name
php artisan migrate
```

**Step 2:** Generate model + relationships
```bash
php artisan make:model ModelName
```

**Step 3:** Build controller + routes
```bash
php artisan make:controller ModelNameController --resource
# Add routes to routes/web.php with role middleware
```

**Step 4:** Add service layer if complex logic required
```bash
php artisan make:class Services/YourService
```

**Step 5:** Create views (Blade templates in `resources/views/`)
- Use Tailwind CSS classes + Alpine.js for interactivity
- Follow existing component patterns from [dashboard/](resources/views/dashboard)

**Step 6:** Test via feature/unit tests
```bash
php artisan make:test YourFeatureTest --feature
php artisan test
```

### 2. Modify Existing Feature

1. **Understand the flow**: Model → Service → Controller → View
2. **Make changes incrementally**: validation → logic → storage → response
3. **Test edge cases**: empty inputs, decimal precision, role permissions
4. **Run full test suite** before committing

### 3. Database Transitions

Always create **incremental migrations** (never edit old ones once run):
```bash
php artisan make:migration add_field_to_table
# Edit migration, then:
php artisan migrate
```

---

## ✅ Testing

Tests located in `tests/` with two subdirectories:

- **Feature/** — Integration tests (request → response logic)
- **Unit/** — Isolated unit tests for services/models

```bash
php artisan test                    # Run all tests
php artisan test --filter=ExampleTest
php artisan test --stop-on-failure  # Halt on first failure
```

**Base TestCase** (`tests/TestCase.php`) includes:
- Database transactions (auto-rollback between tests)
- Factories for seeding test data

---

## 🚨 Common Pitfalls

### Stock Calculation
- **Issue:** Stok kalkulasi stale if production/sales updated without service call
- **Fix:** Always use `StockService::calculateAvailableStock()` for queries
- Also see: [memori stok kalkulasi](SYSTEM_ARCHITECTURE.md#stock-calculation)

### Unit Conversions
- **Issue:** Storing only one unit (kg or butir) risks precision loss
- **Fix:** Persist both `jumlah_butir` and `jumlah_kg` on all models
- Conversion factor in `pengaturan` table is read-only reference

### N+1 Queries
- **Issue:** Querying HasMany relationships in loops
- **Fix:** Use eager loading: `Kandang::with('produksi_telur')->get()`

### Role Permissions
- **Issue:** Controller not checking worker vs owner permissions
- **Fix:** Apply `role:pemilik|pekerja` middleware to routes; service checks role if needed
- See: [routes/web.php](routes/web.php) for patterns

### Pricing History
- **Issue:** Changing price breaks historical sales records
- **Fix:** `DetailPenjualan` stores `harga_telur_id` (snapshot), not live price lookup
- Never delete `HargaTelur` records; mark `status = 'nonaktif'` instead

---

## 📚 Important Documentation

| Document | Purpose |
|----------|---------|
| [SYSTEM_ARCHITECTURE.md](../SYSTEM_ARCHITECTURE.md) | Complete domain model, entity relationships, workflows |
| [ANALISIS_SISTEM_COMPREHENSIVE.md](../diskusi/ANALISIS_SISTEM_COMPREHENSIVE.md) | Use cases, actors, data flows, system behavior |
| [database/migrations/](../database/migrations) | Schema versioning; start here for column/relationship changes |
| [class_diagram.md](../uml/class_diagram.md) | Entity relationship visuals |
| [app/Services/StockService.php](../app/Services/StockService.php) | Complex inventory accounting logic |

---

## 🔧 Useful Tips

### Quick Model Relations
```php
// One-to-many
$kandang->produksiTelur; // Access related production records

// Inverse relationship
$produksi->kandang;

// Eager load to prevent N+1
Kandang::with('produksiTelur', 'penjualan')->get();
```

### Seeding Test Data
```php
// In tests, factories auto-available:
$user = User::factory()->create(['role' => 'pemilik']);
$kandang = Kandang::factory()->create(['pic_id' => $user->id]);
```

### Accessing Current User Role
```blade
@role('pemilik')
  <!-- Only visible to owners -->
@endrole

@role('pekerja')
  <!-- Only visible to workers -->
@endrole
```

### Database Debugging
```bash
# Open interactive shell to test queries
php artisan tinker
>>> $kandang = Kandang::first();
>>> $kandang->produksiTelur()->count();
```

---

## 🎯 For Copilot: Context & Preferences

When working on this codebase:

1. **Check roles before modifying data**: Verify `auth()->user()->hasRole()` where applicable
2. **Use Services for business logic**: Don't compute stock/conversions in controllers
3. **Eager load relationships**: Always use `->with()` to avoid N+1 queries
4. **Preserve price history**: Never delete pricing records; archive instead
5. **Store dual units**: If adding a measured field, persist both butir and kg
6. **Run `composer dev`** to develop (not separate `artisan serve` + `npm run dev`)
7. **Run tests before committing**: `php artisan test`
8. **Reference [SYSTEM_ARCHITECTURE.md](../SYSTEM_ARCHITECTURE.md)** for data model questions

---

## 📞 Getting Help

For system-wide questions, consult the [analisa-sistem skill](../.github/skills/analisa-sistem/SKILL.md):
```
/skill analisa-sistem
```

This guides deep-dive analysis of components, actors, use cases, and data flows.
