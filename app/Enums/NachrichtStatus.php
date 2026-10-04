<?php

namespace App\Enums;

enum NachrichtStatus: string
{
    case Wartend = 'wartend';
    case Versendet = 'versendet';
    case Fehler = 'fehler';
}
