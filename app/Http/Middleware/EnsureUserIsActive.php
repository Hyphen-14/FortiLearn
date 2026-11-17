<?php

namespace App\Http\Middleware;

use App\Support\Audit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends the session of a user an admin has deactivated, on every web request.
 * RoleMiddleware only guards role-gated routes, so without this a blocked student
 * could keep using plain `auth` routes (wallet, checkout) until the session expires.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Audit::record('auth.inactive_session_terminated', $request, ['path' => $request->path()]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Akun dinonaktifkan oleh administrator.']);
        }

        return $next($request);
    }
}
