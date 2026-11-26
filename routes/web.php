<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\ChangeRequestController as AdminChangeRequestController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PrototypeController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Vendor\AccountController as VendorAccountController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\PackageController as VendorPackageController;
use App\Http\Controllers\Vendor\PortfolioController as VendorPortfolioController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// Publik
Route::view('/', 'home')->name('home');
Route::get('/prototype/aplikasi', PrototypeController::class)->name('prototype.app');
Route::post('/midtrans/notifications', MidtransNotificationController::class)->name('midtrans.notifications');
Route::get('/paket', [PackageController::class, 'index'])->name('packages.index');
Route::get('/paket/{package}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/cari-vendor', [VendorPortfolioController::class, 'index'])->name('vendors.index');
Route::get('/vendor/{vendor}/profil', [VendorPortfolioController::class, 'show'])->name('vendors.profile.show');
Route::get('/vendor/{vendor}/portofolio', [VendorPortfolioController::class, 'show'])->name('vendors.portfolio.show');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login']);
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register']);

    // Lupa password (nama route password.* dipakai oleh email reset bawaan Laravel)
    Route::get('/lupa-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/pesanan/{order}/foto/{photoIndex}', [OrderController::class, 'photo'])
    ->middleware('auth')
    ->name('orders.photos.show');
Route::get('/sengketa/{dispute}/bukti/{evidenceIndex}', [DisputeController::class, 'evidence'])
    ->middleware('auth')
    ->name('disputes.evidence');
Route::post('/sengketa/{dispute}/pesan', [DisputeController::class, 'message'])
    ->middleware('auth')
    ->name('disputes.messages.store');

// Profil customer dan admin (perubahan langsung berlaku)
Route::middleware(['auth', EnsureRole::class . ':customer,admin'])->group(function () {
    Route::get('/akun', [AccountController::class, 'show'])->name('account.show');
    Route::put('/akun/profil', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/akun/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::delete('/akun', [AccountController::class, 'destroy'])->name('account.destroy');
});

// Customer (pemilik properti / agen)
Route::middleware(['auth', EnsureRole::class . ':customer'])->group(function () {
    Route::get('/paket/{package}/pesan', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/paket/{package}/pesan', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/bayar', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/pesanan/{order}/batal', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/pesanan/{order}/minta-pengambilan', [OrderController::class, 'requestPickup'])->name('orders.pickup.request');
    Route::post('/pesanan/{order}/respon', [OrderController::class, 'respond'])->name('orders.respond');
    Route::post('/pesanan/{order}/sengketa', [DisputeController::class, 'store'])->name('orders.disputes.store');
    Route::post('/pesanan/{order}/ulasan', [ReviewController::class, 'store'])->name('reviews.store');
});

// Vendor
Route::prefix('vendor')->name('vendor.')->middleware(['auth', EnsureRole::class . ':vendor'])->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::resource('packages', VendorPackageController::class)->except(['show']);
    Route::get('/pesanan', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::post('/pesanan/{order}/terima', [VendorOrderController::class, 'accept'])->name('orders.accept');
    Route::post('/pesanan/{order}/tolak', [VendorOrderController::class, 'reject'])->name('orders.reject');
    Route::post('/pesanan/{order}/minta-info', [VendorOrderController::class, 'requestInfo'])->name('orders.request-info');
    Route::post('/pesanan/{order}/sengketa', [DisputeController::class, 'store'])->name('orders.disputes.store');
    Route::post('/pesanan/{order}/pasang', [VendorOrderController::class, 'installed'])->name('orders.installed');
    Route::post('/pesanan/{order}/selesai', [VendorOrderController::class, 'complete'])->name('orders.complete');

    Route::get('/portofolio', [VendorPortfolioController::class, 'manage'])->name('portfolio.manage');
    Route::post('/portofolio/proyek', [VendorPortfolioController::class, 'storeProject'])->name('portfolio.projects.store');
    Route::put('/portofolio/proyek/{portfolioProject}', [VendorPortfolioController::class, 'updateProject'])->name('portfolio.projects.update');
    Route::delete('/portofolio/proyek/{portfolioProject}', [VendorPortfolioController::class, 'destroyProject'])->name('portfolio.projects.destroy');

    // Profil vendor: semua perubahan berupa permintaan ke admin
    Route::get('/akun', [VendorAccountController::class, 'show'])->name('account.show');
    Route::post('/akun/profil', [VendorAccountController::class, 'requestProfile'])->name('account.profile');
    Route::post('/akun/password', [VendorAccountController::class, 'requestPassword'])->name('account.password');
    Route::post('/akun/hapus', [VendorAccountController::class, 'requestDelete'])->name('account.delete');
    Route::post('/akun/permintaan/{changeRequest}/batal', [VendorAccountController::class, 'cancel'])->name('account.cancel');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureRole::class . ':admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/sengketa', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::get('/sengketa/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
    Route::post('/sengketa/{dispute}/selesaikan', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
    Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::post('/vendors/{vendor}/verifikasi', [AdminVendorController::class, 'toggle'])->name('vendors.toggle');

    Route::get('/permintaan', [AdminChangeRequestController::class, 'index'])->name('requests.index');
    Route::post('/permintaan/{changeRequest}/setujui', [AdminChangeRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/permintaan/{changeRequest}/tolak', [AdminChangeRequestController::class, 'reject'])->name('requests.reject');
});
