<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Helfer extends Model
{

    protected $table = 'helfer';
    protected $fillable = ['klamottenboerse_id', 'name', 'mail', 'telefon', 'bereich'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function aktuelleAppointments()
    {
        return $this->appointments()->where('date_start', '>=', now())->first();
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class);
    }
}
