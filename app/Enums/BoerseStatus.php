<?php

namespace App\Enums;

enum BoerseStatus: string
{
    case Planung = 'planung';
    case Anmeldung = 'anmeldung';
    case Vorbereitung = 'vorbereitung';
    case Verkauf = 'verkauf';
    case Abrechnung = 'abrechnung';
    case Abgeschlossen = 'abgeschlossen';

    public function label(): string
    {
        return match ($this) {
            self::Planung => 'Planung',
            self::Anmeldung => 'Anmeldung läuft',
            self::Vorbereitung => 'Vorbereitung',
            self::Verkauf => 'Verkauf',
            self::Abrechnung => 'Abrechnung',
            self::Abgeschlossen => 'Abgeschlossen',
        };
    }
}
