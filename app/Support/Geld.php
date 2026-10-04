<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Alle Beträge werden als ganze Cent (int) gespeichert und gerechnet.
 * Diese Klasse bündelt Umrechnung, Rundung und Formatierung.
 */
final class Geld
{
    /** Scheine und Münzen, die bei der Auszahlung verwendet werden (in Cent). */
    public const STUECKELUNG = [5000, 2000, 1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];

    public static function format(int $cent): string
    {
        return number_format($cent / 100, 2, ',', '.').' €';
    }

    /** Liest Eingaben wie "4,50", "4.50", "4" oder "4,5" in Cent ein. */
    public static function parse(string|int|float|null $wert): int
    {
        if ($wert === null || $wert === '') {
            return 0;
        }

        if (is_int($wert)) {
            return $wert * 100;
        }

        $text = str_replace([' ', '€'], '', (string) $wert);
        // "1.234,50" → "1234.50"
        if (str_contains($text, ',')) {
            $text = str_replace('.', '', $text);
            $text = str_replace(',', '.', $text);
        }

        if (! is_numeric($text)) {
            throw new InvalidArgumentException("Kein gültiger Betrag: {$wert}");
        }

        return (int) round(((float) $text) * 100);
    }

    /** Anteil in Promille, kaufmännisch gerundet auf ganze Cent. */
    public static function promille(int $cent, int $promille): int
    {
        return intdiv($cent * $promille + 500, 1000);
    }

    /** Rundet ab auf das nächste Vielfache von $schritt Cent (z. B. 10). */
    public static function abrunden(int $cent, int $schritt): int
    {
        if ($schritt <= 1) {
            return $cent;
        }

        return intdiv($cent, $schritt) * $schritt;
    }

    /**
     * Zerlegt einen Betrag in möglichst wenige Scheine und Münzen.
     *
     * @return array<int, int> Wert in Cent => Anzahl
     */
    public static function stueckelung(int $cent, array $werte = self::STUECKELUNG): array
    {
        $ergebnis = [];
        foreach ($werte as $wert) {
            $anzahl = intdiv($cent, $wert);
            if ($anzahl > 0) {
                $ergebnis[$wert] = $anzahl;
                $cent -= $anzahl * $wert;
            }
        }

        return $ergebnis;
    }

    public static function stueckBezeichnung(int $cent): string
    {
        return $cent >= 500
            ? ($cent / 100).' €-Schein'
            : ($cent >= 100 ? ($cent / 100).' €-Münze' : $cent.' Cent');
    }
}
