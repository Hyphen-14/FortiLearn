<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\CatalogController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\TransactionController;
use App\Http\Controllers\Student\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - FortiLearn (E-Learning Marketplace)
|--------------------------------------------------------------------------
*/

// 1. Public & Discovery Routes (Dedicated Pages for Sidebar Navigation)
Route::get('/', function () {
    return redirect()->route('catalog.index');
});

Route::get('/courses', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/categories', [CatalogController::class, 'categories'])->name('catalog.categories');
Route::get('/my-courses', [CatalogController::class, 'myCourses'])->name('catalog.my-courses');
Route::get('/wishlist', [CatalogController::class, 'wishlist'])->name('catalog.wishlist');
Route::get('/help', [CatalogController::class, 'help'])->name('catalog.help');
Route::get('/courses/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/courses/{slug}/lessons/{lesson}/stream', [CatalogController::class, 'streamLesson'])
    ->middleware('auth')
    ->name('catalog.lesson.stream');

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

// 4. Admin Protected Routes (Role: admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['status' => 'success', 'message' => 'Admin Dashboard Access Granted']);
    })->name('dashboard');
    Route::get('/users', function () {
        return response()->json(['status' => 'success', 'message' => 'User Management']);
    })->name('users.index');
    Route::get('/courses', function () {
        return response()->json(['status' => 'success', 'message' => 'Course Moderation']);
    })->name('courses.index');
});

// 5. Instructor Protected Routes (Role: instructor)
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::post('/courses/{course}/lessons/upload', function (\Illuminate\Http\Request $request, \App\Models\Course $course, \App\Services\FileUploadService $fileUploadService) {
        $request->validate([
            'file' => ['required', 'file'],
            'type' => ['required', 'in:video,pdf'],
        ]);
        try {
            $result = $fileUploadService->storeSecureLessonFile($request->file('file'), $request->input('type'));
            return response()->json(['status' => 'success', 'file' => $result]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    })->name('lessons.upload');
});
