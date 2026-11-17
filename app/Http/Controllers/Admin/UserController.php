<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['student', 'instructor', 'admin'])],
            'state' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, function ($query, string $q) {
                // Bound parameter (no SQL injection) and LIKE wildcards escaped so "%" or "_" match literally.
                // ESCAPE '!' is used because MySQL and SQLite disagree on the default escape character.
                $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';
                $query->where(fn ($w) => $w->whereRaw("name LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$term]));
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['state'] ?? null, fn ($query, string $state) => $query->where('is_active', $state === 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users, 'filters' => $filters]);
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('changeStatus', $user);

        if ($user->is_active) {
            $user->is_active = false;
            $user->setRememberToken(Str::random(60));
            $user->save();

            // Log the account out everywhere now; EnsureUserIsActive also catches any session this misses.
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            Audit::record('admin.user_deactivated', $request, ['target_id' => $user->id, 'target_email' => $user->email]);
        }

        return back()->with('success', "Akun {$user->email} dinonaktifkan dan semua sesinya diakhiri.");
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('changeStatus', $user);

        if (! $user->is_active) {
            $user->is_active = true;
            $user->save();

            Audit::record('admin.user_activated', $request, ['target_id' => $user->id, 'target_email' => $user->email]);
        }

        return back()->with('success', "Akun {$user->email} diaktifkan kembali.");
    }
}
