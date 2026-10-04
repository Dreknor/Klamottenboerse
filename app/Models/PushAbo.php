<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ein Gerät, das Push-Nachrichten für eine Person empfängt. */
class PushAbo extends Model
{
    protected $table = 'push_abos';

    protected $guarded = [];

    protected $hidden = ['p256dh', 'auth'];

    protected function casts(): array
    {
        return ['zuletzt_genutzt_at' => 'datetime'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
