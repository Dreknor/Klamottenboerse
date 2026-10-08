<?php

namespace App\Domain\System;

use App\Models\Person;
use App\Models\SystemUpdate;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Updates aus der Weboberfläche – dieselben Schritte wie früher deploy.sh:
 * Wartungsmodus → git pull → composer install (nur wenn nötig) → migrate → Caches leeren → online.
 * Die fertig gebauten CSS/JS-Dateien liegen im Repository (public/build), auf dem Server
 * braucht es also kein Node.
 */
class Update
{
    public static function verfuegbar(): bool
    {
        $gesperrt = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return function_exists('proc_open') && ! in_array('proc_open', $gesperrt, true) && file_exists(base_path('.git'));
    }

    /** @return array{commit: ?string, datum: ?string, branch: ?string, text: ?string} */
    public function version(): array
    {
        $log = $this->git(['log', '-1', '--format=%h|%cI|%s']);
        [$commit, $datum, $text] = array_pad(explode('|', trim($log->output()), 3), 3, null);

        return [
            'commit' => $log->successful() ? $commit : null,
            'datum' => $log->successful() ? $datum : null,
            'branch' => trim($this->git(['rev-parse', '--abbrev-ref', 'HEAD'])->output()) ?: null,
            'text' => $text,
        ];
    }

    /**
     * Holt den Stand vom Server und listet neue Änderungen.
     *
     * @return array{fehler: ?string, commits: list<array{commit: string, datum: string, text: string}>}
     */
    public function pruefen(): array
    {
        $fetch = $this->git(['fetch', '--quiet', 'origin']);
        if ($fetch->failed()) {
            return ['fehler' => 'Verbindung zum Code-Server fehlgeschlagen: '.trim($fetch->errorOutput()), 'commits' => []];
        }

        if ($this->git(['rev-parse', '--verify', '--quiet', $this->ziel()])->failed()) {
            return ['fehler' => 'Für den Zweig „'.$this->zweig().'“ gibt es auf dem Code-Server keinen Stand. Bitte UPDATE_BRANCH in der .env setzen.', 'commits' => []];
        }

        $log = $this->git(['log', 'HEAD..'.$this->ziel(), '--format=%h|%cI|%s']);
        if ($log->failed()) {
            return ['fehler' => trim($log->errorOutput()), 'commits' => []];
        }

        $commits = collect(explode("\n", trim($log->output())))->filter()->map(function ($zeile) {
            [$commit, $datum, $text] = array_pad(explode('|', $zeile, 3), 3, '');

            return compact('commit', 'datum', 'text');
        })->values()->all();

        Cache::put('system.update.pruefung', ['zeit' => now()->toIso8601String(), 'commits' => $commits], now()->addDay());

        return ['fehler' => null, 'commits' => $commits];
    }

    public function ausfuehren(Person $person): SystemUpdate
    {
        $sperre = Cache::lock('system.update', 900);
        if (! $sperre->get()) {
            throw new RuntimeException('Es läuft bereits ein Update.');
        }

        @set_time_limit(900);
        ignore_user_abort(true);

        $vorher = trim($this->git(['rev-parse', 'HEAD'])->output());
        $update = SystemUpdate::create(['person_id' => $person->id, 'von_version' => Str::limit($vorher, 40, ''), 'status' => 'laeuft', 'ausgabe' => '']);
        $wartung = false;

        try {
            $geaendert = $this->git(['status', '--porcelain', '--untracked-files=no']);
            $this->protokoll($update, 'git status', $geaendert);
            $this->erzeugteDateienZuruecksetzen($update, $this->geaenderteDateien($geaendert->output()));

            Artisan::call('down', ['--retry' => 30, '--refresh' => 15]);
            $wartung = true;
            $this->notiz($update, 'Wartungsmodus an');

            $this->schritt($update, 'git pull', $this->git(['pull', '--ff-only', 'origin', $this->zweig()]));
            $nachher = trim($this->git(['rev-parse', 'HEAD'])->output());

            $dateien = $vorher === $nachher ? '' : $this->git(['diff', '--name-only', $vorher, $nachher])->output();
            if (Str::contains($dateien, 'composer.lock') || ! is_dir(base_path('vendor'))) {
                $this->schritt($update, 'composer install', Process::path(base_path())->timeout(600)
                    ->env(['COMPOSER_HOME' => storage_path('app/composer'), 'HOME' => storage_path('app/composer')])
                    ->run([...$this->befehl(config('system.composer')), 'install', '--no-dev', '--no-interaction', '--prefer-dist', '--optimize-autoloader']));
            } else {
                $this->notiz($update, 'composer install übersprungen (keine neuen Pakete)');
            }

            // Als eigener Prozess, damit der neue Code verwendet wird
            $this->schritt($update, 'migrate', $this->artisan(['migrate', '--force']));
            $this->schritt($update, 'optimize:clear', $this->artisan(['optimize:clear']));

            $update->update(['status' => 'erfolgreich', 'auf_version' => Str::limit($nachher, 40, '')]);
        } catch (Throwable $e) {
            $this->notiz($update, 'FEHLER: '.$e->getMessage());
            $update->update(['status' => 'fehlgeschlagen']);
            report($e);
        } finally {
            if ($wartung) {
                Artisan::call('up');
                $this->notiz($update, 'Wartungsmodus aus');
            }
            $sperre->release();
            Cache::forget('system.update.pruefung');
        }

        return $update->refresh();
    }

    private function schritt(SystemUpdate $update, string $titel, ProcessResult $ergebnis): void
    {
        $this->protokoll($update, $titel, $ergebnis);
        if ($ergebnis->failed()) {
            throw new RuntimeException("Schritt „{$titel}“ ist fehlgeschlagen.");
        }
    }

    private function protokoll(SystemUpdate $update, string $titel, ProcessResult $ergebnis): void
    {
        $this->notiz($update, "$ {$titel}\n".trim($ergebnis->output()."\n".$ergebnis->errorOutput()));
    }

    private function notiz(SystemUpdate $update, string $text): void
    {
        $update->update(['ausgabe' => ltrim($update->ausgabe."\n\n[".now()->format('H:i:s').'] '.$text)]);
    }

    /**
     * Dateien, die nur durch „npm install“/„npm run build“ auf dem Server entstehen, kommen ohnehin
     * fertig aus dem Repository – sie werden zurückgesetzt statt das Update abzubrechen.
     * Alles andere könnte eine echte Änderung sein: dann lieber abbrechen und erklären.
     *
     * @param  list<string>  $dateien
     */
    private function erzeugteDateienZuruecksetzen(SystemUpdate $update, array $dateien): void
    {
        if ($dateien === []) {
            return;
        }

        $andere = array_values(array_filter($dateien, fn ($datei) => ! self::istErzeugt($datei)));
        if ($andere !== []) {
            throw new RuntimeException('Auf dem Server wurden Dateien direkt geändert: '.implode(', ', $andere).'. '
                .'Update abgebrochen, damit nichts verloren geht. Werden die Änderungen nicht gebraucht, '
                .'per SSH im Projektordner „git checkout -- '.implode(' ', $andere).'“ ausführen und das Update erneut starten.');
        }

        $this->schritt($update, 'git checkout (erzeugte Dateien zurücksetzen)', $this->git(['checkout', '--', ...$dateien]));
        // neu gebaute, nicht versionierte Assets würden sonst „git pull“ blockieren
        $this->schritt($update, 'git clean public/build', $this->git(['clean', '-f', '--', 'public/build']));
    }

    public static function istErzeugt(string $datei): bool
    {
        return $datei === 'package-lock.json' || str_starts_with($datei, 'public/build/');
    }

    /** @return list<string> Pfade aus „git status --porcelain“ */
    private function geaenderteDateien(string $ausgabe): array
    {
        return collect(preg_split('/\R/', $ausgabe))
            ->filter(fn ($zeile) => strlen(trim($zeile)) > 3)
            ->map(fn ($zeile) => trim(str_contains($zeile, ' -> ') ? Str::after($zeile, ' -> ') : substr($zeile, 3), ' "'))
            ->values()->all();
    }

    private function git(array $argumente): ProcessResult
    {
        return Process::path(base_path())->timeout(120)->env(['GIT_TERMINAL_PROMPT' => '0'])->run([config('system.git'), ...$argumente]);
    }

    private function artisan(array $argumente): ProcessResult
    {
        return Process::path(base_path())->timeout(300)->run([...$this->befehl(config('system.php')), 'artisan', ...$argumente]);
    }

    /** „php composer.phar“ → ['php', 'composer.phar'] */
    private function befehl(string $text): array
    {
        return array_values(array_filter(explode(' ', $text)));
    }

    private function zweig(): string
    {
        return config('system.branch') ?: trim($this->git(['rev-parse', '--abbrev-ref', 'HEAD'])->output());
    }

    private function ziel(): string
    {
        return 'origin/'.$this->zweig();
    }
}
