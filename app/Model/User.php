<?php

namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    public const ACCESS_KASSE = 'access-kasse';
    public const ACCESS_VERWALTUNG = 'access-verwaltung';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'verwaltung', 'kasse'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function canAccessKasse(): bool
    {
        return (bool) $this->kasse;
    }

    public function canAccessVerwaltung(): bool
    {
        return (bool) $this->verwaltung;
    }
}
