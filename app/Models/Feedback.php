<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['beantwortet_at' => 'datetime'];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function antworten(): HasMany
    {
        return $this->hasMany(FeedbackAntwort::class);
    }
}
