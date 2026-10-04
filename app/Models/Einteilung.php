<?php

namespace App\Models;

use App\Enums\EinteilungStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Einteilung extends Model
{
    protected $table = 'einteilungen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => EinteilungStatus::class, 'erinnert_at' => 'datetime'];
    }

    public function schicht(): BelongsTo
    {
        return $this->belongsTo(Schicht::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
