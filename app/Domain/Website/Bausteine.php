<?php

namespace App\Domain\Website;

/**
 * Die Bausteine, aus denen Website-Seiten bestehen. Jeder Baustein hat einen Typ und feste Felder;
 * freies HTML gibt es bewusst nicht, damit das Layout immer sauber bleibt.
 */
final class Bausteine
{
    /** Typ => [Bezeichnung, Standardwerte] */
    public const TYPEN = [
        'text' => ['Text', ['titel' => '', 'text' => '']],
        'bild' => ['Bild', ['media_id' => null, 'pfad' => '', 'alt' => '', 'beschriftung' => '', 'breite' => 'voll']],
        'hinweis' => ['Hinweisbox', ['titel' => '', 'text' => '', 'farbe' => 'orange']],
        'liste' => ['Aufzählung', ['titel' => '', 'eintraege' => []]],
        'zwei_spalten' => ['Zwei Spalten', ['links_titel' => '', 'links' => '', 'rechts_titel' => '', 'rechts' => '']],
        'faq' => ['Fragen & Antworten', ['titel' => 'Häufige Fragen', 'eintraege' => []]],
        'knopf' => ['Knopf', ['text' => 'Jetzt anmelden', 'ziel' => 'anmeldung', 'url' => '']],
        'galerie' => ['Bildergalerie', ['titel' => '', 'bilder' => []]],
        'termin' => ['Nächster Termin (automatisch)', ['titel' => 'Nächste Klamottenbörse']],
        'schichten' => ['Helfer-Aufruf mit freien Schichten (automatisch)', ['titel' => 'Du willst helfen?', 'text' => '']],
        'kopf' => ['Kopfbereich mit Logo', ['titel' => '', 'text' => '', 'logo' => true]],
    ];

    public const KNOPF_ZIELE = ['anmeldung' => 'Anmeldung', 'helfer' => 'Helferliste', 'portal' => 'Mein Portal', 'url' => 'Eigene Adresse'];

    /** @return array<string, string> */
    public static function auswahl(): array
    {
        return array_map(fn ($t) => $t[0], self::TYPEN);
    }

    /**
     * Nur bekannte Typen und Felder übernehmen, Texte begrenzen.
     *
     * @param  array<int, mixed>  $bloecke
     * @return list<array<string, mixed>>
     */
    public static function bereinigen(array $bloecke): array
    {
        $sauber = [];
        foreach ($bloecke as $block) {
            if (! is_array($block) || ! isset(self::TYPEN[$block['typ'] ?? null])) {
                continue;
            }
            $standard = self::TYPEN[$block['typ']][1];
            $daten = ['typ' => $block['typ']];
            foreach ($standard as $feld => $vorgabe) {
                $wert = $block[$feld] ?? $vorgabe;
                $daten[$feld] = match (true) {
                    is_bool($vorgabe) => (bool) $wert,
                    is_array($vorgabe) => self::liste($feld, is_array($wert) ? $wert : []),
                    $feld === 'media_id' => $wert ? (int) $wert : null,
                    default => mb_substr(trim((string) $wert), 0, 20000),
                };
            }
            $sauber[] = $daten;
        }

        return $sauber;
    }

    /** @return list<mixed> */
    private static function liste(string $feld, array $werte): array
    {
        return array_values(array_filter(array_map(function ($wert) use ($feld) {
            return match ($feld) {
                'eintraege' => is_array($wert)
                    ? ['frage' => mb_substr(trim((string) ($wert['frage'] ?? '')), 0, 500), 'antwort' => mb_substr(trim((string) ($wert['antwort'] ?? '')), 0, 5000)]
                    : mb_substr(trim((string) $wert), 0, 500),
                'bilder' => (int) $wert ?: null,
                default => $wert,
            };
        }, $werte), fn ($w) => is_array($w) ? filled($w['frage'] ?? null) : filled($w)));
    }
}
