<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_an_audit_log_entry(): void
    {
        $log = AuditLogger::log('vknummer.ueberschreiben', null, [
            'vknummer' => 250,
            'vorherige_vergabe' => 12,
            'neue_vergabe' => 34,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'vknummer.ueberschreiben',
        ]);

        $this->assertSame(250, $log->changes['vknummer']);
    }

    public function test_it_can_be_queried_from_the_audit_log_table(): void
    {
        AuditLogger::log('kasse.stornierung', null, ['summe' => 12.5]);
        AuditLogger::log('kasse.stornierung', null, ['summe' => 5]);

        $this->assertSame(2, AuditLog::where('action', 'kasse.stornierung')->count());
    }
}
