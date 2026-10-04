<?php

namespace App\Domain\Website;

use App\Domain\Kommunikation\Platzhalter;
use App\Models\Boerse;
use App\Models\Seite;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/** Daten, die Bausteine zum Anzeigen brauchen: nächste Börse, Platzhalter, freie Schichten, Bilder. */
class SeitenKontext
{
    public readonly ?Boerse $boerse;

    /** @var array<string, string> */
    public readonly array $infos;

    public readonly int $freieSchichten;

    /** @var Collection<int, Media> */
    private Collection $bilder;

    public function __construct(?Seite $seite = null)
    {
        $this->boerse = Boerse::query()->offen()->where('verkaufstag', '>=', today())->orderBy('verkaufstag')->with('ort')->first();
        $this->infos = Platzhalter::fuer(null, $this->boerse);
        $this->freieSchichten = $this->boerse
            ? $this->boerse->schichten()->withCount('zusagen')->get()->sum(fn ($s) => max(0, $s->soll - $s->zusagen_count))
            : 0;
        $this->bilder = $seite ? $seite->getMedia('bilder')->keyBy('id') : collect();
    }

    /** Markdown mit Platzhaltern ({datum}, {ort} …) in sicheres HTML. */
    public function text(?string $markdown): string
    {
        return Str::markdown(Platzhalter::ersetzen((string) $markdown, $this->infos), [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    public function zeile(?string $text): string
    {
        return Platzhalter::ersetzen((string) $text, $this->infos);
    }

    public function bild(?int $id): ?Media
    {
        return $id ? $this->bilder->get($id) : null;
    }

    public function knopfZiel(array $block): string
    {
        return match ($block['ziel'] ?? 'anmeldung') {
            'helfer' => route('helfer.index'),
            'portal' => route('portal.index'),
            'url' => $block['url'] ?: route('start'),
            default => route('anmeldung.create'),
        };
    }
}
