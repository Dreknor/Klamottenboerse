<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bonposition extends Model
{
    protected $table = 'bonpositionen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['storniert_at' => 'datetime'];
    }

    public function bon(): BelongsTo
    {
        return $this->belongsTo(Bon::class);
    }

    public function teilnahme(): BelongsTo
    {
        return $this->belongsTo(Teilnahme::class);
    }

    public function artikel(): BelongsTo
    {
        return $this->belongsTo(Artikel::class);
    }
}
