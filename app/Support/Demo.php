<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/** Demo-Installation zum gefahrlosen Ausprobieren. */
class Demo
{
    /** Anmelden per Klick: Rolle => [E-Mail, Titel, Beschreibung] */
    public const ZUGAENGE = [
        'admin' => ['demo-admin@example.org', 'Admin', 'darf alles, auch Team & Rechte und System'],
        'orga' => ['demo-orga@example.org', 'Orga-Team', 'Börse, Nummern, Helfer, Mails, Abrechnung'],
        'kasse' => ['demo-kasse@example.org', 'Kasse', 'kassieren am Verkaufstag (auch am Handy)'],
        'annahme' => ['demo-annahme@example.org', 'Annahme', 'Kistenannahme, Rückpacken und Ausgabe am Tablet'],
        'verkaeufer' => ['demo-verkaeufer@example.org', 'Verkäuferin', 'Portal mit Nummer, Artikeln und Etiketten'],
    ];

    /** Reservierte Test-Domains (RFC 2606) – solche Adressen haben nur die erfundenen Beispielpersonen. */
    public static function istBeispieladresse(?string $email): bool
    {
        $domain = strtolower((string) substr(strrchr((string) $email, '@') ?: '', 1));

        return $domain === ''
            || in_array($domain, ['example.org', 'example.com', 'example.net'], true)
            || (bool) preg_match('/(^|\.)(example|test|invalid|localhost)$/', $domain);
    }

    public static function aktiv(): bool
    {
        return (bool) config('demo.aktiv');
    }

    /** Alles neu: leere Datenbank mit Beispieldaten, hochgeladene Dateien weg, Caches leer. */
    public static function zuruecksetzen(): void
    {
        if (! self::aktiv()) {
            throw new \RuntimeException('Zurücksetzen ist nur im Demo-Modus erlaubt.');
        }

        // Erst die hochgeladenen Dateien weg (Ordner je Medien-ID), dann neu befüllen –
        // sonst würden die Bilder der frischen Beispieldaten gleich wieder gelöscht.
        foreach (['public', 'local'] as $disk) {
            foreach (Storage::disk($disk)->directories() as $ordner) {
                if (ctype_digit(basename($ordner))) {
                    Storage::disk($disk)->deleteDirectory($ordner);
                }
            }
        }
        Cache::flush();

        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);

        File::cleanDirectory(storage_path('framework/views'));
        Cache::forever('demo.zurueckgesetzt', now()->toIso8601String());
    }
}
