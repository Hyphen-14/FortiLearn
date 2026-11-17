<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Tiny helper so security-relevant events are written to audit_logs consistently.
 */
class Audit
{
    /**
     * @param  int|null  $userId  set when the event concerns a user who is not logged in (e.g. password reset)
     */
    public static function record(string $eventType, ?Request $request = null, array $payload = [], ?int $userId = null): void
    {
        $request ??= request();

        AuditLog::create([
            'user_id' => $userId ?? $request->user()?->id,
            'event_type' => $eventType,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'payload' => $payload,
        ]);
    }
}
