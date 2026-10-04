<?php

namespace App\Models;

use App\Support\Barcode;
use App\Support\Geld;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artikel extends Model
{
    use SoftDeletes;

    protected $table = 'artikel';

    protected $guarded = ['id'];

    public function teilnahme(): BelongsTo
    {
        return $this->belongsTo(Teilnahme::class);
    }

    public function kategorie(): BelongsTo
    {
        return $this->belongsTo(Kategorie::class);
    }

    /**
     * Inhalt des Barcodes: Nummer (3) + Artikel (3) + Preis in Cent (5), z. B. 21500700450.
     * So liest die Kasse Nummer, Artikel und Preis auch ohne Netz direkt vom Etikett.
     */
    public function barcode(): string
    {
        return Barcode::kodieren($this->teilnahme->nummer, $this->laufnummer, $this->preis_cent);
    }

    public function preis(): string
    {
        return Geld::format($this->preis_cent);
    }
}
