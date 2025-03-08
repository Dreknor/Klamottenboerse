<?php

namespace App\Model;

use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class verkaufteartikel extends Model
{
    //
    public $table = "verkaufteartikel";
    public $fillable = ["verkauf","vknummer", "artikelnummer", "betrag", 'klamottenboersen_id'];

    public function verkauf(){
        return $this->belongsTo(verkaeufe::class, 'verkauf');
    }

    public function verkaeufernummer(){
        return $this->belongsTo(VKnummer::class, 'vknummer', 'vknummer');
    }

    public function klamottenboerse(){
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboerse_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('klamottenboerse', function (Builder $builder) {

            $klamottenboerse = \Cache::remember('klamottenboerse_aktuell', 5, function () {
                $repository = new KlamottenboersenRepository();
                return $repository->aktuelleKlamottenboerse();
            });

            $builder->where('klamottenboerse_id', $klamottenboerse->id);
        });
    }
}
