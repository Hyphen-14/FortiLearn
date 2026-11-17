<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 60;

    /**
     * Show login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate user with rate limiting and session fixation prevention.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $throttleKey = $this->loginThrottleKey($request);

        // Anti brute-force: 5 failures per (email, IP) per minute. Checked before the password
        // so a locked-out attacker gets no signal about whether a guess was right.
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Audit::record('auth.lockout', $request, ['email' => $credentials['email'], 'retry_after' => $seconds]);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $remember)) {
            // Anti-Session Fixation: Always regenerate session ID upon privilege change/login
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            if (! Auth::user()->is_active) {
                Audit::record('auth.login_blocked_inactive', $request);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Akun dinonaktifkan oleh administrator.']);
            }

            Audit::record('auth.login_success', $request);

            return redirect()->intended(route('catalog.index'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
        Audit::record('auth.login_failed', $request, ['email' => $credentials['email']]);

        return back()->withErrors([
            'email' => 'Kombinasi email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    private function loginThrottleKey(Request $request): string
    {
        return 'login:'.Str::transliterate(Str::lower((string) $request->input('email'))).'|'.$request->ip();
    }

    /**
     * Show registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Register new student account with strict mass assignment protection.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // max:72 because bcrypt silently ignores every byte after the 72nd
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::defaults()],
        ]);

        // Security: role and balance are NEVER accepted from client request
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        Audit::record('auth.register', $request);

        return redirect()->route('catalog.index')
            ->with('success', 'Pendaftaran akun berhasil! Selamat datang di FortiLearn.');
    }

    /**
     * Log user out and invalidate session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Audit::record('auth.logout', $request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index')
            ->with('info', 'Anda telah berhasil logout.');
    }
}

