<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode as Generator;
use chillerlan\QRCode\QROptions;

/** QR-Codes für Etiketten und Kistenzettel – als PNG-Daten-URI, damit sie direkt in PDFs eingebettet werden können. */
final class QrCode
{
    public static function png(string $inhalt, int $groesse = 6): string
    {
        $optionen = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'eccLevel' => EccLevel::M,
            'scale' => $groesse,
            'quietzoneSize' => 1,
        ]);

        return (new Generator($optionen))->render($inhalt);
    }
}
