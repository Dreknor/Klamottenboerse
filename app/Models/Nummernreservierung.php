<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nummernreservierung extends Model
{
    protected $table = 'nummernreservierungen';

    protected $guarded = ['id'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    /** Börsen, für die diese Reservierung ausgesetzt ist. */
    public function freigaben(): BelongsToMany
    {
        return $this->belongsToMany(Boerse::class, 'nummernreservierung_freigaben', 'nummernreservierung_id', 'boerse_id')
            ->withPivot('grund')->withTimestamps();
    }

    /**
     * Reservierungen, die für die Börse gelten: dauerhafte und solche nur für diese Börse –
     * ohne die, die für diese Börse freigegeben wurden.
     */
    public function scopeGueltigFuer(Builder $query, Boerse $boerse): void
    {
        $query->where(fn ($q) => $q->whereNull('boerse_id')->orWhere('boerse_id', $boerse->id))
            ->whereDoesntHave('freigaben', fn ($q) => $q->where('boersen.id', $boerse->id));
    }

    public function istFreigegebenFuer(Boerse $boerse): bool
    {
        return $this->freigaben->contains('id', $boerse->id);
    }

    /** Gibt die Nummer für diese eine Börse frei; eine Reservierung nur für diese Börse wird gelöscht. */
    public function freigebenFuer(Boerse $boerse, string $grund): void
    {
        if ($this->boerse_id === $boerse->id) {
            $this->delete();
        } else {
            $this->freigaben()->syncWithoutDetaching([$boerse->id => ['grund' => $grund]]);
        }

        activity()->performedOn($this->person)
            ->withProperties(['nummer' => $this->nummer, 'boerse' => $boerse->titel, 'grund' => $grund])
            ->log('Reservierung für eine Börse freigegeben');
    }
}
