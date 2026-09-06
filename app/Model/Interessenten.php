<?php

namespace App\Model;

use App\Model\Warteliste;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Interessenten extends Model
{
    use SoftDeletes;

    public $table = 'interessenten';

    protected $fillable = ['uuid', 'vorname', 'nachname', 'mail', 'telefon', 'anrede', 'mitarbeiter', 'kinderhaus', 'handy', 'user_id', 'email_verified_at', 'registration_source', 'deletion_requested_at'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deletion_requested_at' => 'datetime',
    ];

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }


    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getKinderhausAttribute($value)
    {
        if ($value == 1) {
            return 'ja';
        }

        return 'nein';
    }

    public function getMitarbeiterAttribute($value)
    {
        if ($value == 1) {
            return 'ja';
        }

        return 'nein';
    }

    public function vknummer_reserviert()
    {
        return $this->hasOne(VKnummer::class, 'reserviert_fuer')
            ->where('klamottenboersen_id', DB::raw('(select max(`id`) from klamottenboerse)'))
            ->orderBy('klamottenboersen_id', 'desc');
    }

    public function vknummern_vergeben()
    {
        return $this->hasOne(VKnummer::class, 'vergeben_an')
            ->where('klamottenboersen_id', DB::raw('(select max(`id`) from klamottenboerse)'))
            ->orderBy('klamottenboersen_id', 'desc');
    }

    public function bisherige_vknummen()
    {
        return $this->hasMany(VKnummer::class, 'vergeben_an')
            ->orderBy('klamottenboersen_id', 'desc');
    }

    public function warteliste()
    {
        return $this->hasOne(Warteliste::class);
    }

    public function isWarteliste()
    {
        return (bool) $this->warteliste()->first();
    }

    public function notiz()
    {
        return $this->hasOne(Notizen::class, 'interessenten_id');
    }
}
