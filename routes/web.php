<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CourseThumbnailController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\ReviewController as InstructorReviewController;
use App\Http\Controllers\Instructor\SalesController as InstructorSalesController;
use App\Http\Controllers\Instructor\LessonController as InstructorLessonController;
use App\Http\Controllers\Instructor\SectionController as InstructorSectionController;
use App\Http\Controllers\LessonStreamController;
use App\Http\Controllers\Student\CatalogController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\ReviewController as StudentReviewController;
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
Route::get('/courses/{course}/thumbnail', [CourseThumbnailController::class, 'show'])
    ->whereNumber('course')
    ->name('course.thumbnail');

// Secure private streaming (CoursePolicy@viewContent, chunked + HTTP Range, no physical path leak)
Route::get('/courses/{course}/stream/{lesson}', [LessonStreamController::class, 'show'])
    ->middleware('auth')
    ->whereNumber(['course', 'lesson'])
    ->name('lesson.stream');
Route::get('/courses/{slug}/lessons/{lesson}/stream', [LessonStreamController::class, 'showBySlug'])
    ->middleware('auth')
    ->whereNumber('lesson')
    ->name('catalog.lesson.stream');

// 2. Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    // Dedicated instructor portal (separate from the student login)
    Route::get('/instructor/login', [AuthController::class, 'showInstructorLogin'])->name('instructor.login');
    Route::post('/instructor/login', [AuthController::class, 'loginInstructor'])->middleware('throttle:6,1');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Separate registration entry point for instructors (role assigned server-side)
    Route::get('/register/instructor', [AuthController::class, 'showRegisterInstructor'])->name('register.instructor');
    Route::post('/register/instructor', [AuthController::class, 'registerInstructor'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 3. Student Authenticated Routes (Jobdesk 3) - RBAC: student only (instructors/admins use their own workspaces)
Route::middleware(['auth', 'role:student'])->group(function () {
    // Wallet Management & Simulation
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');

    // Secure Checkout Engine
    Route::get('/checkout/{course}', [CheckoutController::class, 'showCheckout'])->name('checkout.confirm');
    Route::post('/checkout/{course}', [CheckoutController::class, 'processCheckout'])->name('checkout.process');

    // Transactions History & IDOR-Protected Invoices
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Course reviews (enrolled students only; enforced again inside the controller)
    Route::post('/courses/{course}/reviews', [StudentReviewController::class, 'store'])
        ->whereNumber('course')->middleware('throttle:10,1')->name('reviews.store');
    Route::delete('/courses/{course}/reviews', [StudentReviewController::class, 'destroy'])
        ->whereNumber('course')->name('reviews.destroy');
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

// 5. Instructor Module (Jobdesk 2 & 4) - RBAC: instructor only; every record is additionally checked by CoursePolicy
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/', fn () => redirect()->route('instructor.dashboard'));
    Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/students', [InstructorSalesController::class, 'index'])->name('sales.index');
    Route::get('/reviews', [InstructorReviewController::class, 'index'])->name('reviews.index');

    Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [InstructorCourseController::class, 'store'])->middleware('throttle:20,1')->name('courses.store');
    Route::get('/courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [InstructorCourseController::class, 'update'])->middleware('throttle:20,1')->name('courses.update');
    Route::post('/courses/{course}/submit', [InstructorCourseController::class, 'submit'])->name('courses.submit');
    Route::delete('/courses/{course}', [InstructorCourseController::class, 'destroy'])->name('courses.destroy');

    Route::post('/courses/{course}/sections', [InstructorSectionController::class, 'store'])->name('sections.store');
    Route::put('/sections/{section}', [InstructorSectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [InstructorSectionController::class, 'destroy'])->name('sections.destroy');

    Route::post('/courses/{course}/lessons', [InstructorLessonController::class, 'store'])->middleware('throttle:30,1')->name('lessons.store');
    Route::delete('/lessons/{lesson}', [InstructorLessonController::class, 'destroy'])->name('lessons.destroy');

    // Direct upload endpoint for automated testing
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
