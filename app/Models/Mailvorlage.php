<?php

namespace App\Models;

use Database\Seeders\MailvorlagenSeeder;
use Illuminate\Database\Eloquent\Model;

class Mailvorlage extends Model
{
    protected $table = 'mailvorlagen';

    protected $guarded = ['id'];

    /** Standardvorlagen verschickt das System selbst (über den Schlüssel) – sie dürfen nicht gelöscht werden. */
    public function istStandard(): bool
    {
        return array_key_exists($this->schluessel, MailvorlagenSeeder::VORLAGEN);
    }
}
