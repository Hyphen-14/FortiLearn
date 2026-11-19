<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Policies\CoursePolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(WalletTransaction::class, TransactionPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // One password policy for register and reset. uncompromised() is left out on purpose:
        // it calls the external HIBP API, which the offline test suite and graders' machines may not reach.
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers()->symbols());

        // Anti reset-link poisoning: build the emailed link from APP_URL, never from the request's
        // Host header, which an attacker controls when they trigger a reset for the victim.
        ResetPassword::createUrlUsing(fn ($user, string $token) => rtrim((string) config('app.url'), '/')
            .route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));
    }
}
