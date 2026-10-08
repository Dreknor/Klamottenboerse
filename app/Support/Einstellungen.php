<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Globale Einstellungen ohne Börsenbezug, gespeichert in der Tabelle "einstellungen".
 */
class Einstellungen
{
    public const STANDARD = [
        'vereinsname' => 'Klamottenbörse des Ev. Kinderhauses Radebeul',
        'empfaenger_spende' => 'Förderverein des Ev. Kinderhauses',
        'absender_name' => 'Klamottenbörse',
        'mail_max_pro_stunde' => 55,
        'erinnerung_aufgaben_tage' => 2,
        // Reputation: Punkte aus Vermerken der letzten X Monate
        'reputation_warnung_ab' => 2,
        'reputation_sperre_ab' => 5,
        'reputation_zeitraum_monate' => 24,
    ];

    /** Pflichtangaben für Impressum und Datenschutzerklärung (Platzhalter => Beschreibung). */
    public const BETREIBER = [
        'betreiber_name' => 'Name des Trägers bzw. Vereins',
        'betreiber_anschrift' => 'Anschrift',
        'betreiber_vertreten' => 'vertreten durch (Name)',
        'kontakt_email' => 'E-Mail-Adresse',
        'kontakt_telefon' => 'Telefonnummer',
        'register' => 'Registereintrag (z. B. Vereinsregister, Nummer) – falls vorhanden',
        'hoster' => 'Hosting-Anbieter mit Anschrift',
        'datenschutz_kontakt' => 'Ansprechperson für Datenschutz',
    ];

    private const CACHE_KEY = 'einstellungen.alle';

    public static function get(string $schluessel, mixed $standard = null): mixed
    {
        return self::alle()[$schluessel] ?? $standard ?? (self::STANDARD[$schluessel] ?? null);
    }

    public static function set(string $schluessel, mixed $wert): void
    {
        DB::table('einstellungen')->updateOrInsert(
            ['schluessel' => $schluessel],
            ['wert' => json_encode($wert), 'updated_at' => now(), 'created_at' => now()],
        );
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, mixed> */
    public static function alle(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $gespeichert = DB::table('einstellungen')->pluck('wert', 'schluessel')
                ->map(fn ($wert) => json_decode($wert, true))
                ->all();

            return array_merge(self::STANDARD, $gespeichert);
        });
    }
}
