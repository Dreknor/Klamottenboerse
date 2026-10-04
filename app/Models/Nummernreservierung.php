<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** Reservierungen, die für die Börse gelten: dauerhafte und solche nur für diese Börse. */
    public function scopeGueltigFuer(Builder $query, Boerse $boerse): void
    {
        $query->where(fn ($q) => $q->whereNull('boerse_id')->orWhere('boerse_id', $boerse->id));
    }
}
