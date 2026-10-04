<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bon extends Model
{
    protected $table = 'bons';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['erstellt_am_geraet' => 'datetime', 'storniert_at' => 'datetime'];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function kassenschicht(): BelongsTo
    {
        return $this->belongsTo(Kassenschicht::class);
    }

    public function positionen(): HasMany
    {
        return $this->hasMany(Bonposition::class);
    }
}
