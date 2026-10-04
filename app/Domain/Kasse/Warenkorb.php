<?php

namespace App\Domain\Kasse;

use App\Models\Boerse;
use App\Models\Bon;
use App\Models\Bonposition;
use App\Models\Kassenschicht;
use App\Models\Person;
use App\Models\WarenkorbPosition;
use App\Support\Geld;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Der offene Einkauf eines Kassen-Kontos – in der Datenbank, damit Handy und PC
 * mit demselben Konto denselben Warenkorb sehen (z. B. am Handy scannen, am PC kassieren).
 */
class Warenkorb
{
    public function __construct(private readonly Person $person, private readonly Boerse $boerse) {}

    /** @return Collection<int, WarenkorbPosition> */
    public function positionen(): Collection
    {
        return WarenkorbPosition::query()
            ->where('person_id', $this->person->id)
            ->where('boerse_id', $this->boerse->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Fügt einen Artikel hinzu. Gleiche UUID = schon vorhanden (z. B. nach Verbindungsabbruch erneut gesendet).
     *
     * @return array{position: WarenkorbPosition, warnungen: list<string>}
     */
    public function hinzufuegen(string $uuid, int $nummer, int $artikel, int $preisCent): array
    {
        $vorhanden = WarenkorbPosition::query()->where('uuid', $uuid)->first();
        if ($vorhanden) {
            return ['position' => $vorhanden, 'warnungen' => []];
        }

        $teilnahme = $this->boerse->teilnahmen()->mitNummer()->where('nummer', $nummer)->first();
        if (! $teilnahme) {
            throw new DomainException("Verkäufernummer {$nummer} ist nicht vergeben. Etikett prüfen!");
        }
        if ($preisCent <= 0 || $preisCent > 99999) {
            throw new DomainException('Bitte einen gültigen Preis eingeben.');
        }
        if ($this->positionen()->contains(fn ($p) => $p->nummer === $nummer && $p->artikel === $artikel)) {
            throw new DomainException("Artikel {$nummer}-{$artikel} ist schon in diesem Einkauf.");
        }

        $warnungen = [];
        $schonVerkauft = Bonposition::query()
            ->where('teilnahme_id', $teilnahme->id)->where('artikelnummer', $artikel)->whereNull('storniert_at')
            ->whereHas('bon', fn ($q) => $q->whereNull('storniert_at'))
            ->exists();
        if ($schonVerkauft) {
            $warnungen[] = "Achtung: Artikel {$nummer}-{$artikel} wurde schon verkauft.";
        }
        $erfasst = $teilnahme->artikel()->where('laufnummer', $artikel)->value('preis_cent');
        if ($erfasst !== null && (int) $erfasst !== $preisCent) {
            $warnungen[] = 'Preis weicht vom erfassten Preis ab ('.Geld::format((int) $erfasst).').';
        }

        $position = WarenkorbPosition::create([
            'uuid' => $uuid, 'person_id' => $this->person->id, 'boerse_id' => $this->boerse->id,
            'nummer' => $nummer, 'artikel' => $artikel, 'preis_cent' => $preisCent,
        ]);

        return ['position' => $position, 'warnungen' => $warnungen];
    }

    public function entfernen(string $uuid): void
    {
        WarenkorbPosition::query()->where('uuid', $uuid)->where('person_id', $this->person->id)->delete();
    }

    public function leeren(): void
    {
        WarenkorbPosition::query()->where('person_id', $this->person->id)->where('boerse_id', $this->boerse->id)->delete();
    }

    /** Kassiert den Warenkorb ab: erzeugt den Bon und leert den Warenkorb. */
    public function abschliessen(?Kassenschicht $schicht, ?string $bonUuid = null): Bon
    {
        return DB::transaction(function () use ($schicht, $bonUuid) {
            $positionen = $this->positionen();
            if ($positionen->isEmpty()) {
                throw new DomainException('Der Warenkorb ist leer.');
            }

            return app(BonErfassen::class)($this->boerse, $schicht, [
                'uuid' => $bonUuid ?? (string) Str::uuid(),
                'erstellt_am' => now()->toIso8601String(),
                'positionen' => $positionen->reverse()->map->alsArray()->values()->all(),
            ]);
        });
    }

    /**
     * Einkauf korrigieren: Der Bon wird storniert und seine Artikel kommen zurück in den Warenkorb.
     * Dort lassen sie sich bearbeiten; danach wird ganz normal neu abkassiert.
     */
    public function bonZurueckholen(Bon $bon): void
    {
        if ($bon->boerse_id !== $this->boerse->id || $bon->storniert_at) {
            throw new DomainException('Dieser Einkauf kann nicht mehr bearbeitet werden.');
        }
        if ($this->positionen()->isNotEmpty()) {
            throw new DomainException('Bitte erst den aktuellen Einkauf abschließen oder leeren.');
        }

        DB::transaction(function () use ($bon) {
            $positionen = $bon->positionen()->whereNull('storniert_at')->with('teilnahme:id,nummer')->get();
            app(Stornieren::class)->bon($bon, 'zur Korrektur in den Warenkorb zurückgeholt');

            foreach ($positionen as $p) {
                WarenkorbPosition::create([
                    'uuid' => (string) Str::uuid(), 'person_id' => $this->person->id, 'boerse_id' => $this->boerse->id,
                    'nummer' => $p->teilnahme->nummer, 'artikel' => $p->artikelnummer, 'preis_cent' => $p->preis_cent,
                ]);
            }
        });
    }

    /** Der letzte Bon dieses Kontos (für die Anzeige auf allen Geräten). */
    public function letzterBon(): ?Bon
    {
        return Bon::query()->where('boerse_id', $this->boerse->id)
            ->whereHas('kassenschicht', fn ($q) => $q->where('person_id', $this->person->id))
            ->whereNull('storniert_at')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->latest('id')->first();
    }
}
