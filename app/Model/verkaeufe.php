<?php

namespace App\Model;


use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class verkaeufe extends Model
{
    //
    public $table = "verkaeufe";
    public $fillable = ["user_id","summe", 'klamottenboerse_id' ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];


    public function artikel(){
        return $this->hasMany(verkaufteartikel::class, 'verkauf', 'id');
    }

    public function klamottenboerse(){
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboersen_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
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
