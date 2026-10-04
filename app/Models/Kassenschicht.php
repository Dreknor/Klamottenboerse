<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kassenschicht extends Model
{
    protected $table = 'kassenschichten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['beginn' => 'datetime', 'ende' => 'datetime'];
    }

    public function kasse(): BelongsTo
    {
        return $this->belongsTo(Kasse::class);
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function bons(): HasMany
    {
        return $this->hasMany(Bon::class);
    }
}
