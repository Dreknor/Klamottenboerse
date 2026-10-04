<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\ImapPostfach;
use App\Domain\System\Update;
use App\Enums\NachrichtStatus;
use App\Http\Controllers\Controller;
use App\Models\Fehler;
use App\Models\Nachricht;
use App\Models\SystemUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/** System: Zustand der Installation, Fehlerprotokoll-Übersicht und Updates – alles ohne SSH. */
class SystemController extends Controller
{
    public function index(Update $update): View
    {
        $herzschlag = Cache::get('system.scheduler');

        return view('admin.system.index', [
            'version' => Update::verfuegbar() ? $update->version() : null,
            'updateMoeglich' => Update::verfuegbar(),
            'pruefung' => Cache::get('system.update.pruefung'),
            'updates' => SystemUpdate::query()->with('person')->latest()->limit(10)->get(),
            'scheduler' => $herzschlag ? Carbon::parse($herzschlag) : null,
            'pruefungen' => $this->pruefungen(),
            'offeneFehler' => Fehler::query()->whereNull('erledigt_at')->count(),
            'neuesteFehler' => Fehler::query()->whereNull('erledigt_at')->latest('zuletzt_at')->limit(5)->get(),
        ]);
    }

    public function pruefen(Update $update): RedirectResponse
    {
        $ergebnis = $update->pruefen();
        if ($ergebnis['fehler']) {
            return back()->with('fehler', $ergebnis['fehler']);
        }

        return back()->with('erfolg', $ergebnis['commits'] ? count($ergebnis['commits']).' neue Änderung(en) verfügbar.' : 'Die Software ist auf dem neuesten Stand.');
    }

    public function update(Request $request, Update $update): RedirectResponse
    {
        $request->validate(['bestaetigung' => ['accepted']], ['bestaetigung.accepted' => 'Bitte bestätige, dass jetzt niemand kassiert.']);

        try {
            $ergebnis = $update->ausfuehren($request->user());
        } catch (RuntimeException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return $ergebnis->status === 'erfolgreich'
            ? back()->with('erfolg', 'Update installiert.')
            : back()->with('fehler', 'Das Update ist fehlgeschlagen – die Seite läuft mit dem bisherigen Stand weiter. Details stehen im Protokoll unten.');
    }

    /** @return list<array{titel: string, ok: bool|null, text: string}> */
    private function pruefungen(): array
    {
        $liste = [];

        try {
            DB::select('select 1');
            $liste[] = ['titel' => 'Datenbank', 'ok' => true, 'text' => config('database.default').' · '.DB::connection()->getDatabaseName()];
        } catch (Throwable $e) {
            $liste[] = ['titel' => 'Datenbank', 'ok' => false, 'text' => $e->getMessage()];
        }

        $herzschlag = Cache::get('system.scheduler');
        $liste[] = [
            'titel' => 'Automatische Abläufe (Cron)',
            'ok' => $herzschlag && Carbon::parse($herzschlag)->gt(now()->subMinutes(5)),
            'text' => $herzschlag
                ? 'zuletzt '.Carbon::parse($herzschlag)->diffForHumans()
                : 'noch nie gelaufen – Mails werden nicht verschickt! Cron-Eintrag siehe README.',
        ];

        $wartend = Nachricht::query()->where('status', NachrichtStatus::Wartend)->count();
        $fehler = Nachricht::query()->where('status', NachrichtStatus::Fehler)->where('created_at', '>', now()->subDays(7))->count();
        $liste[] = ['titel' => 'Mailversand', 'ok' => $fehler === 0, 'text' => config('mail.default')." · {$wartend} wartend · {$fehler} fehlgeschlagen (7 Tage)"];

        $liste[] = ['titel' => 'Postfach (IMAP)', 'ok' => ImapPostfach::istKonfiguriert() ? true : null, 'text' => ImapPostfach::istKonfiguriert() ? rtrim(config('imap.username').' @ '.config('imap.host'), ' @') : 'nicht eingerichtet'];

        $schreibbar = is_writable(storage_path('app')) && is_writable(storage_path('framework/cache'));
        $frei = @disk_free_space(storage_path());
        $liste[] = ['titel' => 'Speicher', 'ok' => $schreibbar && ($frei === false || $frei > 500 * 1024 * 1024),
            'text' => ($schreibbar ? 'beschreibbar' : 'storage/ NICHT beschreibbar').($frei !== false ? ' · '.number_format($frei / 1024 / 1024 / 1024, 1, ',', '.').' GB frei' : '')];

        $liste[] = ['titel' => 'Umgebung', 'ok' => ! (app()->isProduction() && config('app.debug')),
            'text' => 'PHP '.PHP_VERSION.' · Laravel '.app()->version().' · '.app()->environment().(config('app.debug') ? ' · Debug AN' : '')];

        return $liste;
    }
}
