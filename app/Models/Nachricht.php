<?php

namespace App\Models;

use App\Enums\NachrichtStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ausgehende Mail mit Versandstatus; versendet wird gedrosselt über "mails:versenden". */
class Nachricht extends Model
{
    protected $table = 'nachrichten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => NachrichtStatus::class, 'versendet_at' => 'datetime'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }
}
