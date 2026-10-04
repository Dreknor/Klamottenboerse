<?php

namespace App\Logging;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * Schreibt Warnungen und Fehler in die Tabelle fehlerprotokoll, damit das Team
 * sie im Backend sieht (System → Fehlerprotokoll) – ohne SSH-Zugang zum Server.
 * Wiederholt sich ein offener Fehler, wird nur hochgezählt.
 */
class DatenbankHandler extends AbstractProcessingHandler
{
    private static bool $schreibt = false;

    public function __construct(Level $level = Level::Warning)
    {
        parent::__construct($level);
    }

    protected function write(LogRecord $record): void
    {
        if (self::$schreibt) {
            return; // ein Fehler beim Protokollieren darf keine Endlosschleife auslösen
        }
        self::$schreibt = true;

        try {
            $fehler = $record->context['exception'] ?? null;
            $fehler = $fehler instanceof Throwable ? $fehler : null;
            $stufe = strtolower($record->level->getName());
            $datei = $fehler ? $this->relativ($fehler->getFile()) : null;
            $zeile = $fehler?->getLine();
            $klasse = $fehler ? $fehler::class : null;

            // Zahlen/IDs aus der Meldung herausnehmen, damit gleiche Fehler zusammenfallen
            $hash = sha1(implode('|', [$stufe, $klasse, $datei, $zeile, preg_replace('/\d+/', '#', Str::limit($record->message, 300))]));
            $jetzt = now();
            [$url, $methode] = $this->anfrage();

            $offen = DB::table('fehlerprotokoll')->where('hash', $hash)->whereNull('erledigt_at')->value('id');
            if ($offen) {
                DB::table('fehlerprotokoll')->where('id', $offen)->update([
                    'anzahl' => DB::raw('anzahl + 1'), 'zuletzt_at' => $jetzt, 'url' => $url, 'methode' => $methode, 'updated_at' => $jetzt,
                ]);

                return;
            }

            $kontext = collect($record->context)->except('exception')->all();
            DB::table('fehlerprotokoll')->insert([
                'stufe' => $stufe,
                'nachricht' => Str::limit($record->message, 5000),
                'klasse' => $klasse,
                'datei' => $datei,
                'zeile' => $zeile,
                'trace' => $fehler ? Str::limit($this->trace($fehler), 60000) : null,
                'url' => $url,
                'methode' => $methode,
                'person_id' => $this->personId(),
                'kontext' => $kontext ? json_encode($kontext, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE) : null,
                'hash' => $hash,
                'anzahl' => 1,
                'zuletzt_at' => $jetzt,
                'created_at' => $jetzt,
                'updated_at' => $jetzt,
            ]);
        } catch (Throwable) {
            // Datenbank nicht erreichbar o. ä. – die Datei-Logs haben den Fehler trotzdem
        } finally {
            self::$schreibt = false;
        }
    }

    /** @return array{0: ?string, 1: ?string} */
    private function anfrage(): array
    {
        if (app()->runningInConsole()) {
            return [Str::limit('artisan '.implode(' ', array_slice($_SERVER['argv'] ?? [], 1)), 490), 'CLI'];
        }
        $request = request();

        // ohne Query-String: dort können Tokens aus Login-Links stehen
        return [Str::limit($request->url(), 490), $request->method()];
    }

    private function personId(): ?int
    {
        try {
            return app()->runningInConsole() ? null : auth()->id();
        } catch (Throwable) {
            return null;
        }
    }

    private function trace(Throwable $fehler): string
    {
        $text = '';
        for ($e = $fehler; $e; $e = $e->getPrevious()) {
            $text .= ($text ? "\n\nAusgelöst durch: " : '').$e::class.': '.$e->getMessage()
                ."\n".$this->relativ($e->getFile()).':'.$e->getLine()."\n".str_replace(base_path().DIRECTORY_SEPARATOR, '', $e->getTraceAsString());
        }

        return $text;
    }

    private function relativ(string $pfad): string
    {
        return ltrim(str_replace([base_path(), '\\'], ['', '/'], $pfad), '/');
    }
}
