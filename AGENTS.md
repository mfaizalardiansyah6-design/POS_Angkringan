# AGENTS.md — Angkringan POS

## Repo state (verified)
- **Stack**: Laravel 13.38 / PHP ^8.3, Blade + Tailwind CSS 4 (CSS-first, no `tailwind.config.js`) via `@tailwindcss/vite`, Vite 8, vanilla JS. Web only (`routes/web.php`).
- **Sources of truth**: `PRD.md` (business). Ignore `README.md` (stock) and `CLAUDE.md` (stale). `opencode.json` enables Laravel Boost MCP (`php artisan boost:mcp`).
- **Auth/RBAC**: Username auth, `owner`/`cashier` roles. Owner routes protected by `owner` middleware; general routes use `auth` + `active`.
- **Modules**: Dashboards, POS/checkout, sales/receipt, shifts. Owner: categories/products/inventory/expenses/reports/users/settings/activity (controllers/routes/views present).
- **Tests**: Placeholders (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`). `php artisan test` currently fails 1 test (`/` redirects guests to login → 302). PHPUnit uses SQLite `:memory:` (`phpunit.xml`). No CI/static analysis.
- **DB/env**: `.env` uses MySQL `Pos_db` @ `127.0.0.1:3306` (needed for migrate/pages). `.env.example` defaults to SQLite. **Session gotcha**: `.env.example` sets `SESSION_DRIVER=database` but no sessions table migration exists — if using DB sessions run `php artisan session:table`. Demo users `owner`/`kasir` `password` via `db:seed` (not run by `composer setup`). `php artisan storage:link` may be needed (`public/storage` missing).

## Commands
- **Test**: `composer test` = `@php artisan config:clear --ansi` + `@php artisan test`. Run single: `php artisan test --filter=Name` or `php artisan test tests/Feature/X.php`.
- **Format**: `vendor/bin/pint` / `vendor/bin/pint --test` (format/check only; no lint/typecheck configured).
- **Assets**: `npm run dev` / `npm run build` (Vite only; `composer dev` runs `@php artisan dev`).
- **Setup**: `composer setup` installs deps, copies `.env` if missing, generates key, runs `migrate --force`, `npm install --ignore-scripts`, builds. **Do not run against DB with data**.
- **Migrations**: Check `php artisan migrate:status` before migrating. Avoid `migrate:fresh`/destructive ops without permission.

## Stack conventions
- **UI**: Use existing Blade `x-*` components (`x-page-header`, `x-icon`, `x-badge`, `x-empty-state`, `x-form-errors`, `x-field-error`), CSS tokens in `resources/css/app.css`. Tailwind 4 + custom CSS (ignore Bootstrap references in PRD).
- **Models/enums/money**: PHP attributes `#[Fillable]`/`#[Hidden]`, string-backed enums in `app/Enums`. UI copy Bahasa Indonesia, timezone `Asia/Jakarta`, `rupiah()` helper, money via `App\Support\Money` (brick/math) — **never floats for persisted values**.
- **Services**: Business logic in `app/Services` — `CheckoutService` (checkout/confirm QRIS/void), `ShiftService` (open/close/summary), `ActivityLogger` (audit). Keep controllers thin; reuse services.
- **Factories**: `User`/`Category`/`Product`/`CashierShift` exist; **no `Sale` factory** — create sales via `CheckoutService`.

## Non-negotiable domain rules
- **Checkout (single DB transaction)**: Lock shift/product rows, unique `idempotency_key`, re-check stock, snapshot `sale_items` (`product_name`, `unit_price`, `unit_cost`, `line_total`), decrement tracked stock once via `inventory_movements.source_key`, never negative. `track_stock=false` must never deduct/move stock. Client sends IDs/quantities only; server computes prices/totals.
- **Payments**: Cash completes immediately. QRIS starts `pending_payment`/`pending` and must **not** count revenue or move stock until confirmed. One payment method per sale; no split payment.
- **Schema**: `sale_items.line_total` (not `subtotal`). Receipt template and `ReportController` use `line_total`.
- **Shifts**: Must be `open` to sell. Expected cash = opening cash + completed cash sales − cash expenses − cash refunds (± recorded cash movements). QRIS is never drawer cash.
- **Reports/voids**: Reports include only `completed` sales; voids/refunds reported separately. Profit is estimate using snapshot cost. No hard deletes of users/products/sales; deactivate with `is_active`. Void/refund records reason/actor/time/reversal, at most once; only owners void completed sales.
- **Routes**: Category/Product resource routes expose DELETE but controllers have no `destroy` — do **not** add hard-delete behavior.
- **Out of scope** unless owner confirms (PRD §15.3): discounts, split payment, offline mode, QRIS gateway/webhook, recipe/raw-material stock, multi-location, numbered tables.

## Verification
- For checkout/payment/stock/shift changes add feature tests with `RefreshDatabase` covering role access, double-submit/idempotency, pending QRIS, no negative stock, shift reconciliation, void/refund.
- Run `vendor/bin/pint` and `php artisan test` before declaring done; report exactly what ran. SQLite tests cannot prove MySQL row-locking/constraints.
