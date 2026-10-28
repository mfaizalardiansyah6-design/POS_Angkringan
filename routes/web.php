<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

// TEMP-PREVIEW: local visual verification only. Remove after audit.
Route::get('/__preview/pos', function () {
    \Illuminate\Support\Facades\Auth::loginUsingId(1);

    return redirect()->route('pos.index');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard.redirect');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    Route::get('/transaksi', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/transaksi/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/transaksi/{sale}/struk', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::post('/transaksi/{sale}/konfirmasi-qris', [SaleController::class, 'confirmQris'])->name('sales.confirm');
    Route::post('/transaksi/{sale}/batalkan', [SaleController::class, 'void'])->name('sales.void');

    Route::get('/shift', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shift/buka', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shift/{shift}/tutup', [ShiftController::class, 'close'])->name('shifts.close');

    Route::middleware('owner')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show', 'destroy']);
        Route::patch('/categories/{category}/status', [CategoryController::class, 'toggle'])->name('categories.toggle');

        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
        Route::patch('/products/{product}/status', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::post('/products/{product}/stok', [ProductController::class, 'adjustStock'])->name('products.stock');

        Route::get('/inventaris', [InventoryController::class, 'index'])->name('inventory.index');

        Route::get('/pengeluaran', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/pengeluaran', [ExpenseController::class, 'store'])->name('expenses.store');

        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');

        Route::resource('pengguna', UserController::class)->parameters(['pengguna' => 'user'])->except(['show', 'destroy'])->names('users');
        Route::patch('/pengguna/{user}/status', [UserController::class, 'toggle'])->name('users.toggle');

        Route::get('/audit', [ActivityLogController::class, 'index'])->name('activity.index');
    });
});
