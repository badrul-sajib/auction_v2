<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\MerchantController;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Inventory\PurchaseController;
use App\Http\Controllers\Inventory\AuctionController;
use App\Http\Controllers\Inventory\OrderController;
use App\Http\Controllers\Inventory\PaymentMethodController;
use App\Http\Controllers\Inventory\PaymentController;
use App\Http\Controllers\Inventory\WithdrawalController;
use App\Http\Controllers\Inventory\AllowedIpController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\StockTransferController;

// public order pages (tokenized, no auth) — restricted by IP whitelist + rate limited
Route::middleware(['ip.whitelist', 'throttle:30,1'])->group(function () {
    Route::get('/order/{token}', [PublicOrderController::class, 'show'])->name('order.show');
    Route::post('/order/{token}', [PublicOrderController::class, 'store'])->name('order.store');
});

// inventory
Route::middleware('auth')->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('products/import/sample', [ProductController::class, 'sampleImport'])->name('products.import.sample');
    Route::post('products/import/preview', [ProductController::class, 'previewImport'])->name('products.import.preview');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::get('merchants/import/sample', [MerchantController::class, 'sampleImport'])->name('merchants.import.sample');
    Route::post('merchants/import/preview', [MerchantController::class, 'previewImport'])->name('merchants.import.preview');
    Route::post('merchants/import', [MerchantController::class, 'import'])->name('merchants.import');
    Route::resource('merchants', MerchantController::class)->except('show');

    Route::get('stock', [StockController::class, 'index'])->name('stock.index');

    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::get('products/{product}/auctions', [AuctionController::class, 'index'])->name('auctions.index');
    Route::get('products/{product}/auctions/create', [AuctionController::class, 'create'])->name('auctions.create');
    Route::post('products/{product}/auctions', [AuctionController::class, 'store'])->name('auctions.store');
    Route::get('auctions/{auction}', [AuctionController::class, 'show'])->name('auctions.show');
    Route::patch('auctions/{auction}/toggle', [AuctionController::class, 'toggle'])->name('auctions.toggle');
    Route::delete('auctions/{auction}', [AuctionController::class, 'destroy'])->name('auctions.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('withdrawals/{withdrawal}/invoice', [WithdrawalController::class, 'invoice'])->name('withdrawals.invoice');
    Route::resource('payment-methods', PaymentMethodController::class)->except('show');

    Route::get('ip-whitelist', [AllowedIpController::class, 'index'])->name('allowed-ips.index');
    Route::patch('ip-whitelist/feature/toggle', [AllowedIpController::class, 'toggleFeature'])->name('allowed-ips.feature-toggle');
    Route::post('ip-whitelist', [AllowedIpController::class, 'store'])->name('allowed-ips.store');
    Route::patch('ip-whitelist/{allowedIp}', [AllowedIpController::class, 'update'])->name('allowed-ips.update');
    Route::patch('ip-whitelist/{allowedIp}/toggle', [AllowedIpController::class, 'toggle'])->name('allowed-ips.toggle');
    Route::delete('ip-whitelist/{allowedIp}', [AllowedIpController::class, 'destroy'])->name('allowed-ips.destroy');

    Route::get('adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');

    Route::get('transfers', [StockTransferController::class, 'index'])->name('transfers.index');
    Route::get('transfers/create', [StockTransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [StockTransferController::class, 'store'])->name('transfers.store');
});

// dashboard pages
Route::get('/', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->middleware('auth')->name('profile');

Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->middleware('guest')->name('signin');

Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:6,1'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');






















