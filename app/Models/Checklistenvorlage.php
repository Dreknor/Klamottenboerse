<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklistenvorlage extends Model
{
    protected $table = 'checklistenvorlagen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fuer_neue_boersen' => 'boolean'];
    }

    public function eintraege(): HasMany
    {
        return $this->hasMany(ChecklistenvorlageEintrag::class)->orderBy('versatz_tage')->orderBy('sortierung');
    }
}
