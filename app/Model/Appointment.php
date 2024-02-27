<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = ['klamottenboerse_id', 'beschreibung', 'date_start', 'date_end', 'helfer_id'];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function helfer()
    {
        return $this->belongsTo(Helfer::class);
    }


    public function klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class);
    }


}
