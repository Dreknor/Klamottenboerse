<?php

namespace App\Services;

use App\Model\Angebotskategorie;
use App\Model\Verkaufsartikel;

/**
 * Hilfsfunktionen rund um die (vom Orga-Team pflegbaren) Angebotskategorien:
 * Validierung der Auswahl und Zuordnung erfasster Artikel zu einer Kategorie.
 */
class Angebotskategorien
{
    /**
     * Schlüssel der aktuell auswählbaren Kategorien.
     */
    public static function keys(): array
    {
        return Angebotskategorie::aktive()->keys()->all();
    }

    /**
     * Ordnet einen im Verkäufer-Portal erfassten Artikel einer Kategorie zu:
     * explizit gewählte Kategorie hat Vorrang, sonst wird anhand der
     * (ersten) Zahl in der Größe die passende Größen-Kategorie gesucht.
     */
    public static function fuerArtikel(Verkaufsartikel $artikel): ?string
    {
        $alle = Angebotskategorie::alle();

        if ($artikel->kategorie && $alle->has($artikel->kategorie)) {
            return $artikel->kategorie;
        }

        if ($artikel->groesse && preg_match('/\d+/', $artikel->groesse, $treffer)) {
            $groesse = (int) $treffer[0];

            $kategorie = Angebotskategorie::aktive()->first(fn (Angebotskategorie $k) => $k->hatGroessenbereich()
                && $groesse >= $k->groesse_von
                && $groesse <= $k->groesse_bis);

            return optional($kategorie)->schluessel;
        }

        return null;
    }
}
