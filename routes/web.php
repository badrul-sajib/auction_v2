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
use App\Http\Controllers\Inventory\OrderLinkController;
use App\Http\Controllers\Inventory\OrderController;
use App\Http\Controllers\Inventory\PaymentMethodController;
use App\Http\Controllers\Inventory\PaymentController;
use App\Http\Controllers\PublicOrderController;

// public order pages (tokenized, no auth)
Route::get('/order/{token}', [PublicOrderController::class, 'show'])->name('order.show');
Route::post('/order/{token}', [PublicOrderController::class, 'store'])->name('order.store');
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\StockTransferController;

// inventory
Route::middleware('auth')->prefix('inventory')->name('inventory.')->group(function () {
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::resource('merchants', MerchantController::class)->except('show');

    Route::get('stock', [StockController::class, 'index'])->name('stock.index');

    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::post('products/{product}/order-links', [OrderLinkController::class, 'store'])->name('order-links.store');
    Route::delete('order-links/{orderLink}', [OrderLinkController::class, 'destroy'])->name('order-links.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::resource('payment-methods', PaymentMethodController::class)->except('show');

    Route::get('adjustments', [StockAdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');

    Route::get('transfers', [StockTransferController::class, 'index'])->name('transfers.index');
    Route::get('transfers/create', [StockTransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers', [StockTransferController::class, 'store'])->name('transfers.store');
});

// dashboard pages
Route::get('/', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
})->middleware('auth')->name('dashboard');

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->middleware('auth')->name('profile');

Route::put('/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('signin');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');






















