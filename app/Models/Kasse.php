<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kasse extends Model
{
    protected $table = 'kassen';

    protected $guarded = ['id'];

    protected $hidden = ['geraete_token'];

    protected function casts(): array
    {
        return ['zuletzt_gesehen_at' => 'datetime', 'gesperrt_at' => 'datetime'];
    }

    public function schichten(): HasMany
    {
        return $this->hasMany(Kassenschicht::class);
    }
}
