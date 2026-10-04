<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Abrechnung extends Model
{
    protected $table = 'abrechnungen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['berechnet_at' => 'datetime', 'ausgezahlt_at' => 'datetime'];
    }

    public function teilnahme(): BelongsTo
    {
        return $this->belongsTo(Teilnahme::class);
    }
}
