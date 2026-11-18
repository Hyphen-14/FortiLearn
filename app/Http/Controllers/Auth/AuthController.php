<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
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

        $failed = back()->withErrors([
            'email' => 'Kombinasi email atau password tidak sesuai.',
        ])->onlyInput('email');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
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
            return $reject('Kombinasi email atau password tidak sesuai.');
        }

        if ($portal === 'student' && $user->isInstructor()) {
            return $reject('Akun instruktur masuk melalui halaman Login Instruktur.');
        }

        if (! $user->is_active) {
            return $reject('Akun dinonaktifkan oleh administrator.');
        }

        // Anti-Session Fixation: always regenerate session ID upon login
        $request->session()->regenerate();

        $home = $user->isInstructor() ? route('instructor.dashboard') : route('catalog.index');

        return redirect()->intended($home)
            ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
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
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->role = $role; // not mass-assignable by design
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('catalog.index')
            ->with('info', 'Anda telah berhasil logout.');
    }
}

