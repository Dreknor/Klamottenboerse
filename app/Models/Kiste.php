<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Kiste extends Model
{
    protected $table = 'kisten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['angenommen_at' => 'datetime', 'ausgegeben_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (Kiste $kiste) => $kiste->qr_token ??= Str::random(32));
    }

    public function teilnahme(): BelongsTo
    {
        return $this->belongsTo(Teilnahme::class);
    }
}
