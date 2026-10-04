<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Eine per IMAP abgerufene Mail. */
class Posteingang extends Model
{
    protected $table = 'posteingang';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'anhaenge' => 'array',
            'empfangen_at' => 'datetime',
            'gelesen_at' => 'datetime',
            'beantwortet_at' => 'datetime',
            'erledigt_at' => 'datetime',
            'spam' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function scopeOffen(Builder $query): void
    {
        $query->whereNull('erledigt_at')->where('spam', false);
    }
}
