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
     * Show login form (students / general users).
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Show the dedicated instructor login form.
     */
    public function showInstructorLogin(): View
    {
        return view('auth.instructor-login');
    }

    /**
     * Student / general login. Instructor accounts must use the instructor portal.
     */
    public function login(Request $request): RedirectResponse
    {
        return $this->authenticate($request, 'student');
    }

    /**
     * Instructor-only login. Non-instructor credentials get the same generic error as a wrong password
     * (no role enumeration).
     */
    public function loginInstructor(Request $request): RedirectResponse
    {
        return $this->authenticate($request, 'instructor');
    }

    /**
     * Shared authentication flow with session fixation prevention.
     * $portal is chosen by the route (server side), never by the client.
     */
    private function authenticate(Request $request, string $portal): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Audit::record('auth.lockout', $request, ['email' => $credentials['email'], 'retry_after' => $seconds]);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $failed = back()->withErrors([
            'email' => 'Kombinasi email atau password tidak sesuai.',
        ])->onlyInput('email');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
            Audit::record('auth.login_failed', $request, ['email' => $credentials['email']]);

            return $failed;
        }

        $user = Auth::user();

        $reject = function (string $message) use ($request) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => $message])->onlyInput('email');
        };

        if ($portal === 'instructor' && ! $user->isInstructor()) {
            RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
            Audit::record('auth.login_failed', $request, ['email' => $credentials['email']]);

            return $reject('Kombinasi email atau password tidak sesuai.');
        }

        if ($portal === 'student' && $user->isInstructor()) {
            return $reject('Akun instruktur masuk melalui halaman Login Instruktur.');
        }

        if (! $user->is_active) {
            Audit::record('auth.login_blocked_inactive', $request);

            return $reject('Akun dinonaktifkan oleh administrator.');
        }

        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);
        Audit::record('auth.login_success', $request);

        $home = $user->isInstructor() ? route('instructor.dashboard') : route('catalog.index');

        return redirect()->intended($home)
            ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
    }

    private function loginThrottleKey(Request $request): string
    {
        return 'login:'.Str::transliterate(Str::lower((string) $request->input('email'))).'|'.$request->ip();
    }

    /**
     * Show student registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register', ['accountType' => 'student']);
    }

    /**
     * Show instructor registration form.
     */
    public function showRegisterInstructor(): View
    {
        return view('auth.register', ['accountType' => 'instructor']);
    }

    /**
     * Register a new student account.
     */
    public function register(Request $request): RedirectResponse
    {
        return $this->createAccount($request, 'student');
    }

    /**
     * Register a new instructor account (separate endpoint; role is decided here, never by the client).
     */
    public function registerInstructor(Request $request): RedirectResponse
    {
        return $this->createAccount($request, 'instructor');
    }

    /**
     * Shared registration flow. $role is a server-side constant chosen by the route:
     * 'role' / 'balance' / 'is_active' in the request body are ignored (anti mass-assignment & privilege escalation).
     * Only 'student' and 'instructor' can ever be self-registered; admin accounts are never created here.
     */
    private function createAccount(Request $request, string $role): RedirectResponse
    {
        abort_unless(in_array($role, ['student', 'instructor'], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // max:72 because bcrypt silently ignores every byte after the 72nd
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->role = $role;
        $user->free_course_credits = $role === 'student' ? 1 : 0;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();
        Audit::record('auth.register', $request);

        if ($role === 'instructor') {
            return redirect()->route('instructor.dashboard')
                ->with('success', 'Akun instruktur berhasil dibuat! Mulai dengan membuat kursus pertama Anda.');
        }

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

