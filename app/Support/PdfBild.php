<?php

namespace App\Support;

/**
 * Bilder für PDFs (dompdf) vorbereiten: auf weißen Hintergrund setzen und als einfaches RGB-PNG einbetten.
 *
 * Hintergrund: dompdf schickt PNGs mit Transparenz oder Farbpalette durch Imagick, sobald die
 * Erweiterung installiert ist – und viele Hoster verbieten das per ImageMagick-Sicherheitsrichtlinie
 * („not allowed by the security policy“). Einfache RGB-PNGs verarbeitet dompdf ohne Imagick.
 */
final class PdfBild
{
    /** @var array<string, string> */
    private static array $cache = [];

    /** Daten-URI des Bildes, oder null, wenn die Datei fehlt bzw. nicht lesbar ist (dann im PDF einfach weglassen). */
    public static function datenUri(string $pfad): ?string
    {
        if (isset(self::$cache[$pfad])) {
            return self::$cache[$pfad];
        }
        if (! is_file($pfad) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $quelle = @imagecreatefromstring((string) file_get_contents($pfad));
        if ($quelle === false) {
            return null;
        }

        $breite = imagesx($quelle);
        $hoehe = imagesy($quelle);
        $bild = imagecreatetruecolor($breite, $hoehe);
        imagefill($bild, 0, 0, imagecolorallocate($bild, 255, 255, 255));
        imagecopy($bild, $quelle, 0, 0, 0, 0, $breite, $hoehe);
        imagesavealpha($bild, false);

        ob_start();
        imagepng($bild);
        $png = (string) ob_get_clean();

        return self::$cache[$pfad] = 'data:image/png;base64,'.base64_encode($png);
    }
}
