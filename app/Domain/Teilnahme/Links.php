<?php

namespace App\Domain\Teilnahme;

use App\Models\Einteilung;
use App\Models\Teilnahme;
use Illuminate\Support\Facades\URL;

/** Signierte Links für Selbstbedienung ohne Login (Absage, Angebot annehmen). */
class Links
{
    public static function absage(Teilnahme $teilnahme): string
    {
        return URL::signedRoute('teilnahme.absage', ['teilnahme' => $teilnahme->id]);
    }

    public static function angebot(Teilnahme $teilnahme): string
    {
        return URL::temporarySignedRoute('teilnahme.angebot', $teilnahme->angebot_bis ?? now()->addDays(2), ['teilnahme' => $teilnahme->id]);
    }

    public static function helferAbsage(Einteilung $einteilung): string
    {
        return URL::signedRoute('helfer.absage', ['einteilung' => $einteilung->id]);
    }
}
