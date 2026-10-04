<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SonstigeEinnahme extends Model
{
    public const ARTEN = ['kuchen' => 'Kuchen', 'kaffee' => 'Kaffee und Getränke', 'sonstiges' => 'Sonstiges'];

    protected $table = 'sonstige_einnahmen';

    protected $guarded = ['id'];

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }
}
