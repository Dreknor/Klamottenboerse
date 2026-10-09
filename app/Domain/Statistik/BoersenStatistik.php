<?php

namespace App\Domain\Statistik;

use App\Enums\EinteilungStatus;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\FeedbackFrage;
use App\Models\Kategorie;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Kennzahlen einer Börse – Grundlage für Statistik, Vergleich und Spendenbericht. */
class BoersenStatistik
{
    /** @return array<string, int|float|null|array> */
    public static function fuer(Boerse $boerse): array
    {
        $positionen = DB::table('bonpositionen')
            ->join('bons', 'bons.id', '=', 'bonpositionen.bon_id')
            ->join('teilnahmen', 'teilnahmen.id', '=', 'bonpositionen.teilnahme_id')
            ->where('bons.boerse_id', $boerse->id)
            ->whereNull('bons.storniert_at')
            ->whereNull('bonpositionen.storniert_at');

        $umsatz = (int) (clone $positionen)->sum('bonpositionen.preis_cent');
        $artikelVerkauft = (clone $positionen)->count();
        $kinderhausUmsatz = (int) (clone $positionen)->where('teilnahmen.ist_kinderhaus', true)->sum('bonpositionen.preis_cent');

        $bons = $boerse->bons()->whereNull('storniert_at');
        $anzahlBons = $bons->count();

        $verkaeufer = $boerse->teilnahmen()->where('ist_kinderhaus', false)
            ->whereIn('status', [TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Angeliefert, TeilnahmeStatus::Abgerechnet, TeilnahmeStatus::Ausgezahlt])
            ->count();
        $mitVerkauf = (clone $positionen)->where('teilnahmen.ist_kinderhaus', false)->distinct()->count('bonpositionen.teilnahme_id');

        $artikelErfasst = DB::table('artikel')->join('teilnahmen', 'teilnahmen.id', '=', 'artikel.teilnahme_id')
            ->where('teilnahmen.boerse_id', $boerse->id)->whereNull('artikel.deleted_at')->count();
        $erfasstVerkauft = (clone $positionen)->whereNotNull('bonpositionen.artikel_id')->count();

        $spende = (int) DB::table('abrechnungen')->join('teilnahmen', 'teilnahmen.id', '=', 'abrechnungen.teilnahme_id')
            ->where('teilnahmen.boerse_id', $boerse->id)->sum('abrechnungen.spende_cent');

        $nachStunde = (clone $positionen)->get(['bons.erstellt_am_geraet', 'bonpositionen.preis_cent'])
            ->groupBy(fn ($p) => (int) substr((string) $p->erstellt_am_geraet, 11, 2))
            ->map(fn ($liste) => (int) $liste->sum('preis_cent'))
            ->sortKeys();

        return [
            'verkaeufer' => $verkaeufer,
            'verkaeufer_mit_verkauf' => $mitVerkauf,
            'warteliste' => $boerse->teilnahmen()->where('status', TeilnahmeStatus::Warteliste)->count(),
            'absagen' => $boerse->teilnahmen()->where('status', TeilnahmeStatus::Abgesagt)->count(),
            'artikel_verkauft' => $artikelVerkauft,
            'artikel_erfasst' => $artikelErfasst,
            'verkaufsquote' => $artikelErfasst > 0 ? round($erfasstVerkauft / $artikelErfasst * 100, 1) : null,
            'umsatz' => $umsatz,
            'spende' => $spende,
            'kinderhaus_umsatz' => $kinderhausUmsatz,
            'cafe' => (int) $boerse->sonstigeEinnahmen()->sum('betrag_cent'),
            'bons' => $anzahlBons,
            'durchschnitt_bon' => $anzahlBons ? intdiv($umsatz, $anzahlBons) : 0,
            'durchschnitt_verkaeufer' => $mitVerkauf ? intdiv($umsatz - $kinderhausUmsatz, $mitVerkauf) : 0,
            'helfer' => DB::table('einteilungen')->join('schichten', 'schichten.id', '=', 'einteilungen.schicht_id')
                ->where('schichten.boerse_id', $boerse->id)->where('einteilungen.status', EinteilungStatus::Zugesagt->value)
                ->distinct()->count('einteilungen.person_id'),
            'feedback_anzahl' => $boerse->feedback()->whereNotNull('beantwortet_at')->count(),
            'feedback_schnitt' => FeedbackFrage::schnittFuer($boerse),
            'nach_stunde' => $nachStunde->all(),
            'nach_kategorie' => self::nachKategorie($boerse, clone $positionen),
            'nach_groesse' => self::nachGroesse(clone $positionen),
        ];
    }

    /**
     * Umsatz, verkaufte Artikel und Verkaufsquote je Kategorie (in der Reihenfolge der Kategorienliste).
     * Positionen ohne erfasstes Etikett (Nummer/Preis von Hand in der Kasse) stehen am Ende.
     *
     * @return list<array{name:string, gruppe:?string, umsatz:int, verkauft:int, erfasst:int, quote:?float}>
     */
    private static function nachKategorie(Boerse $boerse, Builder $positionen): array
    {
        $verkauft = $positionen->leftJoin('artikel', 'artikel.id', '=', 'bonpositionen.artikel_id')
            ->selectRaw("CASE WHEN bonpositionen.artikel_id IS NULL THEN 'ohne' ELSE COALESCE(artikel.kategorie_id, 0) END AS schluessel")
            ->selectRaw('SUM(bonpositionen.preis_cent) AS umsatz, COUNT(*) AS anzahl')
            ->groupBy('schluessel')->get()->keyBy('schluessel');

        $erfasst = DB::table('artikel')->join('teilnahmen', 'teilnahmen.id', '=', 'artikel.teilnahme_id')
            ->where('teilnahmen.boerse_id', $boerse->id)->whereNull('artikel.deleted_at')
            ->selectRaw('COALESCE(artikel.kategorie_id, 0) AS schluessel, COUNT(*) AS anzahl')
            ->groupBy('schluessel')->pluck('anzahl', 'schluessel');

        $zeilen = [];
        $zeile = function (string $schluessel, string $name, ?string $gruppe) use ($verkauft, $erfasst, &$zeilen) {
            $v = $verkauft->get($schluessel);
            $anzahlErfasst = (int) ($erfasst[$schluessel] ?? 0);
            if (! $v && ! $anzahlErfasst) {
                return;
            }
            $anzahlVerkauft = (int) ($v->anzahl ?? 0);
            $zeilen[] = [
                'name' => $name,
                'gruppe' => $gruppe,
                'umsatz' => (int) ($v->umsatz ?? 0),
                'verkauft' => $anzahlVerkauft,
                'erfasst' => $anzahlErfasst,
                'quote' => $anzahlErfasst > 0 ? round(min($anzahlVerkauft, $anzahlErfasst) / $anzahlErfasst * 100, 1) : null,
            ];
        };

        foreach (Kategorie::query()->sortiert()->get() as $kategorie) {
            $zeile((string) $kategorie->id, $kategorie->name, $kategorie->gruppe);
        }
        $zeile('0', 'Ohne Kategorie', null);
        $zeile('ohne', 'Ohne erfasstes Etikett', null);

        return $zeilen;
    }

    /**
     * Umsatz und verkaufte Artikel je Größenangabe. Die Angaben sind Freitext, daher vereinheitlicht
     * („Gr. 86 / 92“ → „86/92“) und nach der ersten Zahl sortiert; Angaben ohne Zahl (S, M, L …) folgen.
     *
     * @return array<string, array{umsatz:int, verkauft:int}>
     */
    private static function nachGroesse(Builder $positionen): array
    {
        $liste = $positionen->join('artikel', 'artikel.id', '=', 'bonpositionen.artikel_id')
            ->whereNotNull('artikel.groesse')->where('artikel.groesse', '!=', '')
            ->get(['artikel.groesse', 'bonpositionen.preis_cent']);

        $nachGroesse = [];
        foreach ($liste as $p) {
            $groesse = self::groesseVereinheitlichen($p->groesse);
            if ($groesse === '') {
                continue;
            }
            $nachGroesse[$groesse]['umsatz'] = ($nachGroesse[$groesse]['umsatz'] ?? 0) + (int) $p->preis_cent;
            $nachGroesse[$groesse]['verkauft'] = ($nachGroesse[$groesse]['verkauft'] ?? 0) + 1;
        }

        uksort($nachGroesse, function ($a, $b) {
            $zahlA = preg_match('/\d+/', $a, $m) ? (int) $m[0] : PHP_INT_MAX;
            $zahlB = preg_match('/\d+/', $b, $m) ? (int) $m[0] : PHP_INT_MAX;

            return [$zahlA, strnatcasecmp($a, $b)] <=> [$zahlB, 0];
        });

        return $nachGroesse;
    }

    public static function groesseVereinheitlichen(string $groesse): string
    {
        $groesse = preg_replace('/^\s*(gr(ö|oe)(ß|ss)e|gr\.?)\s*/iu', '', $groesse);
        $groesse = preg_replace('/\s*[\/\-–]\s*/u', '/', $groesse);

        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $groesse)));
    }
}
