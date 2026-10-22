<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['account' => 'Akun dinonaktifkan oleh administrator.']);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses sumber daya ini.');
        }

        return $next($request);
    }
}

