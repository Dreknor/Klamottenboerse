<?php

namespace App\Models;

use App\Domain\Kommunikation\Platzhalter;
use App\Support\Einstellungen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Bearbeitbare Website-Seite. Text in Markdown, Platzhalter wie {betreiber_name} werden ersetzt. */
class Seite extends Model
{
    protected $table = 'seiten';

    protected $guarded = ['id'];

    public function bearbeitetVon(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'bearbeitet_von');
    }

    public function html(): string
    {
        $werte = collect(Einstellungen::BETREIBER)
            ->mapWithKeys(fn ($beschreibung, $schluessel) => [$schluessel => (string) (Einstellungen::get($schluessel) ?: '[bitte ergänzen: '.$beschreibung.']')])
            ->all();

        return Str::markdown(Platzhalter::ersetzen($this->inhalt, $werte + Platzhalter::fuer(null, null)), [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    /** Fehlen Pflichtangaben? Dann zeigt das Backend einen Hinweis. */
    public static function betreiberUnvollstaendig(): bool
    {
        return collect(['betreiber_name', 'betreiber_anschrift', 'kontakt_email'])->contains(fn ($s) => blank(Einstellungen::get($s)));
    }
}
