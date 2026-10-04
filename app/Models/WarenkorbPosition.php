<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ein gescannter Artikel im offenen Einkauf eines Kassen-Kontos. */
class WarenkorbPosition extends Model
{
    protected $table = 'warenkorb_positionen';

    protected $guarded = ['id'];

    /** @return array{uuid:string, nummer:int, artikel:int, preis_cent:int} */
    public function alsArray(): array
    {
        return ['uuid' => $this->uuid, 'nummer' => $this->nummer, 'artikel' => $this->artikel, 'preis_cent' => $this->preis_cent];
    }
}
