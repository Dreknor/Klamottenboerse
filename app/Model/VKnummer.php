<?php

namespace App\Model;

use App\Model\Interessenten;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VKnummer extends Model
{
    public $table = 'vknummern';

    protected $fillable = ['vknummer', 'klamottenboersen_id', 'reserviert_fuer', 'vergeben_an', 'umsatz'];

    protected $visible = ['vknummer', 'klamottenboersen_id', 'reserviert_fuer', 'vergeben_an', 'umsatz'];

    public function reserviert_fuer_Interessent()
    {
        return $this->belongsTo(\App\Model\Interessenten::class, 'reserviert_fuer');
    }

    public function vergeben_an_Interessent()
    {
        return $this->belongsTo(Interessenten::class, 'vergeben_an', 'id', 'vergeben_an');
    }

    public function Klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboersen_id');
    }

    public function bisherigeVerkaeufer()
    {
        return $this->hasMany(self::class, 'vknummer', 'vknummer')
            ->whereNotNull('vergeben_an')->orderBy('klamottenboersen_id', 'DESC')->with('vergeben_an_Interessent');
    }

    public function aktuelleKlamottenboerse()
    {
        return $this->hasOne(self::class, 'vknummer', 'vknummer')
            ->where('klamottenboersen_id', DB::raw('(select max(`id`) from klamottenboerse)'));
    }

    public function verkaufteArtikel()
    {
        return $this->hasMany(verkaufteartikel::class, 'vknummer', 'vknummer')
            ->orderBy('artikelnummer', 'ASC');
    }

    public function scopeAktuelleKlamottenboerse()
    {
        return $this->where('klamottenboersen_id', DB::raw('(select max(`id`) from klamottenboerse)'));
    }


}
