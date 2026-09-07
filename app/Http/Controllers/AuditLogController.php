<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('audit-log.index', [
            'logs' => $logs,
        ]);
    }
}
