<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Events that point at an attack or a blocked action, shown first to the admin. */
    public const SECURITY_EVENTS = [
        'auth.lockout',
        'auth.login_failed',
        'auth.login_blocked_inactive',
        'auth.inactive_session_terminated',
        'auth.password_reset_failed',
        'access.role_denied',
        'access.stream_denied',
        'file.upload_rejected',
    ];

    public function __invoke(): View
    {
        $pendingQuery = Course::where('status', 'pending_approval');

        return view('admin.dashboard', [
            'pendingCount' => (clone $pendingQuery)->count(),
            'pendingCourses' => $pendingQuery->with('instructor:id,name')->withCount('lessons')
                ->oldest('updated_at')->limit(5)->get(),
            'inactiveUsers' => User::where('is_active', false)->count(),
            'securityEvents24h' => AuditLog::whereIn('event_type', self::SECURITY_EVENTS)
                ->where('created_at', '>=', now()->subDay())->count(),
            'recentEvents' => AuditLog::whereIn('event_type', self::SECURITY_EVENTS)
                ->with('user:id,email')->latest('id')->limit(8)->get(),
        ]);
    }
}
