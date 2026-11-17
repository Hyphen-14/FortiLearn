<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $eventTypes = AuditLog::query()->distinct()->orderBy('event_type')->pluck('event_type');

        // Filter values must be event types that actually exist: no free text reaches the query.
        $filters = $request->validate([
            'event' => ['nullable', Rule::in($eventTypes->all())],
            'security' => ['nullable', 'boolean'],
        ]);

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($filters['event'] ?? null, fn ($query, string $event) => $query->where('event_type', $event))
            ->when($filters['security'] ?? false, fn ($query) => $query->whereIn('event_type', DashboardController::SECURITY_EVENTS))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'eventTypes' => $eventTypes,
            'filters' => $filters,
        ]);
    }
}
