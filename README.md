# Angkringan POS

Aplikasi POS (Point of Sale) berbasis web untuk warung/angkringan dengan manajemen kasir, stok, shift, pengeluaran, dan laporan penjualan.

## Tech Stack

- **Backend**: Laravel 13.38 (PHP ^8.3)
- **Frontend**: Blade, Tailwind CSS 4 (CSS-first), Vite 8, vanilla JS
- **Auth/RBAC**: Username auth, peran `owner` dan `cashier`
- **Database**: MySQL (dev) / SQLite :memory: (testing)
- **Keuangan**: Menggunakan `App\Support\Money` (brick/math), nilai uang disimpan tanpa float

## Fitur Utama

- **POS/Kasir**: Keranjang, metode tunai/QRIS, validasi stok, checkout dalam transaksi DB
- **Shift Kasir**: Buka/tutup shift, perhitungan kas terduga, rekonsiliasi
- **Manajemen Produk/Kategori**: CRUD produk, stok terpantau, status aktif
- **Inventaris**: Pergerakan stok (`inventory_movements`)
- **Pengeluaran**: Pencatatan pengeluaran kas per shift
- **Penjualan & Struk**: Daftar penjualan, struk, void/refund (khusus owner)
- **Laporan**: Omzet, laba kotor/bersih estimasi, trend harian, produk terlaris, stok menipis
- **Pengaturan & Aktivitas**: Pengaturan toko/QRIS, log aktivitas

## Persyaratan

- PHP >= 8.3
- Composer
- Node.js & npm
- MySQL (untuk pengembangan lokal)

## Setup

```bash
# Install dependencies, setup env, migrate, build assets
composer setup

# Atur koneksi DB di .env (default MySQL Pos_db)
# Jalankan migrasi jika belum
php artisan migrate

# (Opsional) Seed user demo
php artisan db:seed

# Buat symlink storage
php artisan storage:link
```

**Catatan:**
- Jika menggunakan `SESSION_DRIVER=database`, buat tabel session: `php artisan session:table`
- `composer setup` tidak menjalankan seeder
- Hindari `migrate:fresh` jika ada data penting

## Menjalankan Aplikasi

```bash
# Development (server + queue + vite)
composer dev

# Atau terpisah
php artisan serve
npm run dev
```

Akses: `http://localhost:8000`

User demo (jika di-seed): `owner` / `kasir` dengan password `password`

## Testing & Formatting

```bash
# Jalankan semua test
composer test

# Test spesifik
php artisan test --filter=NamaTest
php artisan test tests/Feature/NamaTest.php

# Format kode
vendor/bin/pint
vendor/bin/pint --test
```

## Aturan Bisnis Penting

- **Checkout**: Dalam 1 transaksi DB, lock baris shift/produk, `idempotency_key` unik, snapshot `sale_items` (`product_name`, `unit_price`, `unit_cost`, `line_total`), kurangi stok hanya sekali via `inventory_movements.source_key`. `track_stock=false` tidak mengurangi stok.
- **Pembayaran**: Tunai langsung selesai. QRIS berstatus `pending`/`pending_payment` dan **tidak** dihitung sebagai pendapatan/stok sampai dikonfirmasi.
- **Shift**: Harus dalam status `open` untuk menjual. Kas terduga = kas awal + penjualan tunai selesai − pengeluaran tunai − refund tunai ± mutasi kas.
- **Laporan**: Hanya penjualan `completed` yang dihitung. Void/refund dilaporkan terpisah. Laba estimasi pakai snapshot cost.
- **Penghapusan**: Tidak ada hard delete untuk user/produk/penjualan. Nonaktifkan via `is_active`. Hanya owner bisa void penjualan selesai.

## Struktur Terkait

- **Routes**: `routes/web.php` (web only)
- **Services**: `app/Services/` (`CheckoutService`, `ShiftService`, `ActivityLogger`)
- **Components**: Blade `x-*` di `resources/views/components/`
- **Style**: Tailwind 4 + CSS kustom di `resources/css/`
- **Sumber bisnis**: `PRD.md` (pedoman bisnis). Abaikan `README.md` default & `CLAUDE.md` jika ada perbedaan.
