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

    protected $fillable = ['uuid', 'vorname', 'nachname', 'mail', 'telefon', 'anrede', 'mitarbeiter', 'kinderhaus', 'handy', 'user_id', 'email_verified_at', 'registration_source', 'deletion_requested_at', 'nur_manuelle_vergabe', 'angebotskategorien'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deletion_requested_at' => 'datetime',
        'nur_manuelle_vergabe' => 'boolean',
        'angebotskategorien' => 'array',
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

    public function reservierteNummern()
    {
        return $this->hasMany(VKnummer::class, 'reserviert_fuer');
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

    public function vermerke()
    {
        return $this->hasMany(VerkaeuferVermerk::class, 'interessent_id');
    }

    /**
     * Summe der Reputations-Punkte innerhalb des Verjährungszeitraums.
     * Nutzt ein vorab geladenes "reputation_punkte" (withSum), falls vorhanden.
     */
    public function reputationPunkte(): int
    {
        if (array_key_exists('reputation_punkte', $this->attributes)) {
            return (int) $this->attributes['reputation_punkte'];
        }

        return (int) $this->vermerke()->wirksam()->sum('punkte');
    }

    public function scopeWithReputationPunkte($query)
    {
        return $query->withSum(['vermerke as reputation_punkte' => function ($q) {
            $q->wirksam();
        }], 'punkte');
    }

    /**
     * true, wenn keine automatische Nummernvergabe (Warteliste-Nachrücken)
     * mehr erfolgen darf. Eine manuelle Festlegung durch das Orga-Team
     * (nur_manuelle_vergabe = true/false) hat Vorrang vor der Punkteschwelle.
     */
    public function istAutomatischeVergabeGesperrt(): bool
    {
        if ($this->nur_manuelle_vergabe !== null) {
            return (bool) $this->nur_manuelle_vergabe;
        }

        return $this->reputationPunkte() >= Einstellung::zahl('reputation_sperre_ab');
    }

    /**
     * "gut", "warnung" oder "gesperrt" – für Badges in der Oberfläche.
     */
    public function reputationStatus(): string
    {
        if ($this->istAutomatischeVergabeGesperrt()) {
            return 'gesperrt';
        }

        if ($this->reputationPunkte() >= Einstellung::zahl('reputation_warnung_ab')) {
            return 'warnung';
        }

        return 'gut';
    }

    public function angebotskategorienLabels(): array
    {
        $alle = Angebotskategorie::alle();

        return collect($this->angebotskategorien ?? [])
            ->filter(fn ($key) => $alle->has($key))
            ->map(fn ($key) => $alle[$key]->label)
            ->values()
            ->all();
    }
}
