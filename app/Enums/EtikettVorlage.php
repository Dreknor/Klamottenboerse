<?php

namespace App\Enums;

/**
 * Gängige A4-Etikettenbögen (Maße nach Avery Zweckform). Jede Vorlage geht auch auf Normalpapier
 * zum Ausschneiden – dafür sind feine Schnittlinien eingeblendet.
 */
enum EtikettVorlage: string
{
    case Z3474 = '3474';
    case Z3475 = '3475';
    case Z3652 = '3652';
    case Z3657 = '3657';
    case L7163 = 'L7163';
    case Z3484 = '3484';
    case Z3483 = '3483';

    public const STANDARD = self::Z3474;

    public function label(): string
    {
        [$spalten, $reihen, $breite, $hoehe] = $this->raster();
        $mm = fn (float $wert) => str_replace('.', ',', (string) $wert);
        $nummer = $this === self::L7163 ? 'Avery L7163' : 'Zweckform '.$this->value;

        return "{$mm($breite)} × {$mm($hoehe)} mm, ".($spalten * $reihen)." pro Bogen ({$nummer})";
    }

    /**
     * Spalten, Reihen, Breite, Höhe, Rand links, Rand oben, Abstand waagerecht, Abstand senkrecht (mm).
     *
     * @return array{0:int, 1:int, 2:float, 3:float, 4:float, 5:float, 6:float, 7:float}
     */
    public function raster(): array
    {
        return match ($this) {
            self::Z3474 => [3, 8, 70, 37, 0, 0.5, 0, 0],
            self::Z3475 => [3, 8, 70, 36, 0, 4.5, 0, 0],
            self::Z3652 => [3, 7, 70, 42.3, 0, 0.45, 0, 0],
            self::Z3657 => [4, 10, 48.5, 25.4, 8, 21.5, 0, 0],
            self::L7163 => [2, 7, 99.1, 38.1, 4.65, 15.15, 2.5, 0],
            self::Z3484 => [2, 8, 105, 37, 0, 0.5, 0, 0],
            self::Z3483 => [2, 4, 105, 74, 0, 0.5, 0, 0],
        };
    }

    public function proBogen(): int
    {
        return $this->raster()[0] * $this->raster()[1];
    }

    /** Position jedes Feldes auf dem Bogen (links, oben in mm), zeilenweise. */
    public function positionen(): array
    {
        [$spalten, $reihen, $breite, $hoehe, $links, $oben, $abstandX, $abstandY] = $this->raster();
        $positionen = [];
        for ($r = 0; $r < $reihen; $r++) {
            for ($s = 0; $s < $spalten; $s++) {
                $positionen[] = [$links + $s * ($breite + $abstandX), $oben + $r * ($hoehe + $abstandY)];
            }
        }

        return $positionen;
    }

    /**
     * Schriftgrößen und QR-Kantenlänge passend zur Etikettgröße.
     *
     * @return array{rand:float, qr:float, kopf:float, preis:float, text:float, textHoehe:float}
     */
    public function gestaltung(): array
    {
        [, , $breite, $hoehe] = $this->raster();
        $rand = $hoehe < 30 ? 1.5 : 2.5;
        $qr = min($hoehe - 2 * $rand, $breite * 0.42, 50);
        $kopf = round(min(26, max(10, $hoehe * 0.43)), 1);

        return [
            'rand' => $rand,
            'qr' => $qr,
            'kopf' => $kopf,
            'preis' => $kopf,
            'text' => $hoehe < 30 ? 6 : ($hoehe > 60 ? 10 : 7.5),
            // Platz für die Beschreibung: was unter Nummer und Preis noch übrig ist (1 pt ≈ 0,353 mm)
            'textHoehe' => max(0, $hoehe - 2 * $rand - 2 * $kopf * 0.353 * 1.15 - 3),
        ];
    }

    /** @return array<string, string> */
    public static function optionen(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $v) => [$v->value => $v->label()])->all();
    }
}
