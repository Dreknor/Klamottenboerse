<?php

namespace App\Console\Commands;

use App\Model\Interessenten;
use App\Services\AuditLogger;
use Illuminate\Console\Command;

class PurgeDeletedInteressenten extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interessenten:purge-deleted {--days=30 : Karenzzeit in Tagen vor der endgültigen Löschung}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Löscht selbst-gelöschte Interessenten nach Ablauf der Karenzzeit endgültig (Soft-Delete-Schutz).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $candidates = Interessenten::onlyTrashed()
            ->whereNotNull('deletion_requested_at')
            ->where('deletion_requested_at', '<=', now()->subDays($days))
            ->get();

        foreach ($candidates as $interessent) {
            AuditLogger::log('interessent.endgueltig_geloescht', $interessent, [
                'mail' => $interessent->mail,
                'deletion_requested_at' => optional($interessent->deletion_requested_at)->toDateTimeString(),
            ]);

            $interessent->forceDelete();
        }

        $this->info("Endgültig gelöscht: {$candidates->count()} Interessent(en).");

        return self::SUCCESS;
    }
}
