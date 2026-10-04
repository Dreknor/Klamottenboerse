<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNachricht extends Model
{
    protected $table = 'push_nachrichten';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['versendet_at' => 'datetime'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
