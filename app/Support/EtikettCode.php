<?php

namespace App\Support;

/**
 * Inhalt des QR-Codes auf den Artikeletiketten: 11 Ziffern = Nummer (3) + Artikel (3) + Preis in Cent (5).
 * Beispiel: 21500700450 → Verkäufer 215, Artikel 7, 4,50 €.
 */
final class EtikettCode
{
    public static function kodieren(int $nummer, int $artikel, int $preisCent): string
    {
        return sprintf('%03d%03d%05d', $nummer, $artikel, $preisCent);
    }

    /** @return array{nummer:int, artikel:int, preis_cent:int}|null */
    public static function lesen(string $code): ?array
    {
        $code = trim($code);
        if (! preg_match('/^\d{11}$/', $code)) {
            return null;
        }

        return [
            'nummer' => (int) substr($code, 0, 3),
            'artikel' => (int) substr($code, 3, 3),
            'preis_cent' => (int) substr($code, 6, 5),
        ];
    }
}
