# IPOS - Agent Guidelines

## Stack
- Laravel 10, PHP 8.2+, MySQL, Blade + SB Admin layout
- Frontend: Vite (build), jQuery + Bootstrap 4 in views
- Payments: Midtrans (production/sandbox via `.env`)

## Key Commands
```bash
php artisan serve              # dev server on :8000
php artisan migrate            # run migrations (117 files)
php artisan db:seed            # only seeds: users, categories, transactions
php artisan db:seed --class=PermissionSeeder  # must run separately (NOT in DatabaseSeeder)
composer install && npm install && npm run build  # full setup
```

## Permission System (per-user, not per-role)
- 6 roles: `super_admin`, `store_manager`, `finance`, `admin`, `sales`, `branch`
- Models: `Permission` (slug-based), `User` many-to-many via `permission_user`
- **`User::hasPermission($slug)`** — super_admin always returns `true`
- **`User::canEdit($slug)`** — blocks Finance from write access on non-Finance groups
- Permission checks are **inline in controllers** (`auth()->user()->hasPermission(...)`), no Gates/Policies
- `CheckFinanceWriteAccess` middleware (global web) blocks POST/PUT/PATCH/DELETE for Finance outside `/admin/finance/*`
- `RoleMiddleware` checks role against allowed list on route groups
- Assign permissions via `admin/manage-master/users/{id}/edit` UI or raw DB insert into `permission_user`
- **PermissionSeeder is not auto-run** — must run manually

## Architecture Notes
- **Entrypoints**: `/admin` (super_admin/store_manager/finance/admin/sales), `/branch` (branch role only), guest routes (public)
- **`access_sales_pusat`** is a broad permission that gates all Sales sub-modules (invoices, delivery notes, receipts)
- **Delivery Note (Surat Jalan)** methods `edit/update/create/store/destroy` lack permission checks — rely on route middleware + sidebar visibility. Edit/delete buttons hidden from Finance via `isFinance()` in DataTable
- **ProductController** uses constructor middleware with `canEdit()` for write actions — reference pattern for adding permission guards
- **Services layer**: `InvoiceService`, `PricingService`, `StockService`, `TransactionCodeService`
- **Exports**: `TransactionExport`, `SettlementExport`, `CustomerTemplateExport` (Laravel Excel)

## Key Routings
- `routes/web.php` — admin routes under `admin/` prefix, branch under `branch/`
- `app/Http/Controllers/Admin/` — main controllers organized by module
- `resources/views/admin/` — views mirror controller structure
- `resources/views/layout/sb_admin.blade.php` — main sidebar (menus gated by `hasPermission`)

## DB & Testing
- Database: `pos_beautylatory` (MySQL), no `.env.example` file
- PHPUnit in `tests/`, config at `phpunit.xml` (DB driver defaults to array, not SQLite)
- 117 migration files — always run fresh migrations for clean state

## Quirks
- `app/Helpers/custom.php` is autoloaded but empty
- No CI workflows, no pre-commit hooks, no code style checker configured
- `package.json` has both Vite and laravel-mix as dev deps — Vite is the active bundler
