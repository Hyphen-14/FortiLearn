<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\CatalogController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\TransactionController;
use App\Http\Controllers\Student\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - FortiLearn (Secure E-Learning)
|--------------------------------------------------------------------------
*/

// 1. Public Catalog Routes
Route::get('/', function () {
    return redirect()->route('catalog.index');
});

Route::get('/courses', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/courses/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// 2. Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 3. Student Authenticated Routes (Jobdesk 3)
Route::middleware(['auth'])->group(function () {
    // Wallet Management & Simulation
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');

    // Secure Checkout Engine
    Route::get('/checkout/{course}', [CheckoutController::class, 'showCheckout'])->name('checkout.confirm');
    Route::post('/checkout/{course}', [CheckoutController::class, 'processCheckout'])->name('checkout.process');

    // Transactions History & IDOR-Protected Invoices
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
});
