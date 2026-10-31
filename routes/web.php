<?php

use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\PackageController as VendorPackageController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// Publik
Route::view('/', 'home')->name('home');
Route::get('/paket', [PackageController::class, 'index'])->name('packages.index');
Route::get('/paket/{package}', [PackageController::class, 'show'])->name('packages.show');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login']);
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register']);
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer (pemilik properti / agen)
Route::middleware(['auth', EnsureRole::class . ':customer'])->group(function () {
    Route::get('/paket/{package}/pesan', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/paket/{package}/pesan', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/bayar', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{order}/ulasan', [ReviewController::class, 'store'])->name('reviews.store');
});

// Vendor
Route::prefix('vendor')->name('vendor.')->middleware(['auth', EnsureRole::class . ':vendor'])->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::resource('packages', VendorPackageController::class)->except(['show']);
    Route::get('/pesanan', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::post('/pesanan/{order}/terima', [VendorOrderController::class, 'accept'])->name('orders.accept');
    Route::post('/pesanan/{order}/tolak', [VendorOrderController::class, 'reject'])->name('orders.reject');
    Route::post('/pesanan/{order}/pasang', [VendorOrderController::class, 'installed'])->name('orders.installed');
    Route::post('/pesanan/{order}/selesai', [VendorOrderController::class, 'complete'])->name('orders.complete');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureRole::class . ':admin'])->group(function () {
    Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::post('/vendors/{vendor}/verifikasi', [AdminVendorController::class, 'toggle'])->name('vendors.toggle');
});
