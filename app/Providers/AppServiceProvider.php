<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\WalletTransaction;
use App\Policies\CoursePolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit Security Policy Registration (Anti-IDOR)
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(WalletTransaction::class, TransactionPolicy::class);
    }
}
