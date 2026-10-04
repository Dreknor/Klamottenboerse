<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Termin extends Model
{
    protected $table = 'termine';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['beginn' => 'datetime', 'ende' => 'datetime'];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }
}
