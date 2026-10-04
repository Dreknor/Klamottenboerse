<?php

namespace App\Domain\Statistik;

use App\Enums\EinteilungStatus;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
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

        $nachBlock = [];
        foreach ((clone $positionen)->where('teilnahmen.ist_kinderhaus', false)->get(['teilnahmen.nummer', 'bonpositionen.preis_cent']) as $p) {
            $block = $boerse->blockVon((int) $p->nummer);
            $nachBlock[$block] = ($nachBlock[$block] ?? 0) + (int) $p->preis_cent;
        }
        ksort($nachBlock);

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
            'feedback_schnitt' => $boerse->feedback()->whereNotNull('bewertung')->avg('bewertung'),
            'nach_stunde' => $nachStunde->all(),
            'nach_block' => $nachBlock,
        ];
    }
}
