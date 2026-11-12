<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Tiny helper so security-relevant events are written to audit_logs consistently.
 */
class Audit
{
    public static function record(string $eventType, ?Request $request = null, array $payload = []): void
    {
        $request ??= request();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'event_type' => $eventType,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'payload' => $payload,
        ]);
    }
}
