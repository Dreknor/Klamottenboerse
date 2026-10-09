<?php

namespace App\Support;

/**
 * Adressen für Bilder aus public/ mit Versionsnummer (Änderungszeit der Datei).
 *
 * Logo und Icons behalten bei Updates ihren Dateinamen; ohne „?v=…“ zeigen Browser
 * sonst noch tagelang die alte Fassung aus ihrem Zwischenspeicher.
 */
final class Datei
{
    public static function url(string $pfad): string
    {
        $zeit = @filemtime(public_path($pfad));

        return asset($pfad).($zeit ? '?v='.$zeit : '');
    }
}
