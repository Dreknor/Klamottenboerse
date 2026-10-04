<?php

namespace App\Models;

use App\Enums\KinderhausBezug;
use App\Enums\TeilnahmeStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class Person extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'personen';

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'kinderhaus_bezug' => KinderhausBezug::class,
            'email_verified_at' => 'datetime',
            'info_mails_erlaubt_at' => 'datetime',
            'letzte_aktivitaet_at' => 'datetime',
            'loeschung_angefragt_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Person $person) {
            $person->uuid ??= (string) Str::uuid();
            $person->kinderhaus_bezug ??= KinderhausBezug::Keiner;
        });
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->vorname} {$this->nachname}"));
    }

    public function teilnahmen(): HasMany
    {
        return $this->hasMany(Teilnahme::class);
    }

    public function reservierungen(): HasMany
    {
        return $this->hasMany(Nummernreservierung::class);
    }

    public function einteilungen(): HasMany
    {
        return $this->hasMany(Einteilung::class);
    }

    public function notizen(): MorphMany
    {
        return $this->morphMany(Notiz::class, 'notizbar')->latest();
    }

    /** Die zuletzt bei einer Börse genutzte Verkäufernummer. */
    public function letzteNummer(?Boerse $vor = null): ?int
    {
        return $this->teilnahmen()
            ->join('boersen', 'boersen.id', '=', 'teilnahmen.boerse_id')
            ->whereNotNull('teilnahmen.nummer')
            ->whereIn('teilnahmen.status', array_map(fn ($s) => $s->value, [
                TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Angeliefert,
                TeilnahmeStatus::Abgerechnet, TeilnahmeStatus::Ausgezahlt,
            ]))
            ->when($vor, fn ($q) => $q->where('boersen.verkaufstag', '<', $vor->verkaufstag))
            ->orderByDesc('boersen.verkaufstag')
            ->value('teilnahmen.nummer');
    }

    public function istOrga(): bool
    {
        return $this->hasAnyRole(['admin', 'orga']);
    }
}
