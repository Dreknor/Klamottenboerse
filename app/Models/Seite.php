<?php

namespace App\Models;

use App\Domain\Kommunikation\Platzhalter;
use App\Domain\Website\Bausteine;
use App\Support\Einstellungen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Bearbeitbare Website-Seite. Entweder aus Bausteinen (bloecke) oder – bei Impressum und
 * Datenschutz – als Markdown-Text (inhalt). Platzhalter wie {datum} werden ersetzt.
 */
class Seite extends Model implements HasMedia
{
    use InteractsWithMedia;

    /** Adressen, die nicht als Seiten-Slug verwendet werden dürfen. */
    public const RESERVIERT = ['admin', 'portal', 'kasse', 'tablet', 'login', 'logout', 'anmeldung', 'helfen', 'feedback',
        'teilnahme', 'info-mails', 'passwort-vergessen', 'passwort-neu', 'build', 'images', 'storage', 'up', 'impressum', 'datenschutz'];

    protected $table = 'seiten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'bloecke' => 'array',
            'entwurf' => 'array',
            'im_menue' => 'boolean',
            'veroeffentlicht_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('bilder')->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('web')->fit(Fit::Max, 1600, 1600)->nonQueued();
        $this->addMediaConversion('klein')->fit(Fit::Max, 640, 640)->nonQueued();
    }

    public function bearbeitetVon(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'bearbeitet_von');
    }

    public function versionen(): HasMany
    {
        return $this->hasMany(SeitenVersion::class)->latest('created_at');
    }

    public function scopeVeroeffentlicht(Builder $query): void
    {
        $query->whereNotNull('veroeffentlicht_at');
    }

    public function istBausteinSeite(): bool
    {
        return $this->bloecke !== null || $this->entwurf !== null;
    }

    public function hatEntwurf(): bool
    {
        return $this->entwurf !== null;
    }

    /** Text-Seiten (Impressum, Datenschutz): Markdown mit Betreiber-Angaben. */
    public function html(): string
    {
        $werte = collect(Einstellungen::BETREIBER)
            ->mapWithKeys(fn ($beschreibung, $schluessel) => [$schluessel => (string) (Einstellungen::get($schluessel) ?: '[bitte ergänzen: '.$beschreibung.']')])
            ->all();

        return Str::markdown(Platzhalter::ersetzen((string) $this->inhalt, $werte + Platzhalter::fuer(null, null)), [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }

    /** Baustein-Seiten: Entwurf übernehmen, alte Fassung als Version sichern. */
    public function veroeffentlichen(Person $person): void
    {
        $entwurf = $this->entwurf ?? ['titel' => $this->titel, 'bloecke' => $this->bloecke ?? []];

        $this->versionen()->create([
            'titel' => $entwurf['titel'],
            'bloecke' => Bausteine::bereinigen($entwurf['bloecke']),
            'erstellt_von' => $person->id,
            'created_at' => now(),
        ]);

        $this->update([
            'titel' => $entwurf['titel'],
            'bloecke' => Bausteine::bereinigen($entwurf['bloecke']),
            'entwurf' => null,
            'veroeffentlicht_at' => $this->veroeffentlicht_at ?? now(),
            'bearbeitet_von' => $person->id,
        ]);
    }

    /** Fehlen Pflichtangaben? Dann zeigt das Backend einen Hinweis. */
    public static function betreiberUnvollstaendig(): bool
    {
        return collect(['betreiber_name', 'betreiber_anschrift', 'kontakt_email'])->contains(fn ($s) => blank(Einstellungen::get($s)));
    }
}
