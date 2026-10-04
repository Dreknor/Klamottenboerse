<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ein über die Weboberfläche gestartetes Update mit vollständiger Ausgabe. */
class SystemUpdate extends Model
{
    protected $table = 'updates';

    protected $guarded = [];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
