<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistenvorlageEintrag extends Model
{
    protected $table = 'checklistenvorlage_eintraege';

    protected $guarded = ['id'];

    public function vorlage(): BelongsTo
    {
        return $this->belongsTo(Checklistenvorlage::class, 'checklistenvorlage_id');
    }
}
