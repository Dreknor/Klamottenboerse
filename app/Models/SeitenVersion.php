<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeitenVersion extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'seiten_versionen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['bloecke' => 'array', 'created_at' => 'datetime'];
    }

    public function seite(): BelongsTo
    {
        return $this->belongsTo(Seite::class);
    }

    public function ersteller(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'erstellt_von');
    }
}
