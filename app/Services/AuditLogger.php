<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuditLogger
{
    /**
     * Record a sensitive action in the audit log.
     *
     * @param  string  $action  A short, stable identifier for the action (e.g. "kasse.stornierung").
     * @param  \Illuminate\Database\Eloquent\Model|null  $subject  The entity affected by the action.
     * @param  array  $changes  Additional context data to persist alongside the log entry.
     */
    public static function log(string $action, $subject = null, array $changes = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'changes' => $changes,
            'ip_address' => RequestFacade::ip(),
        ]);
    }
}
