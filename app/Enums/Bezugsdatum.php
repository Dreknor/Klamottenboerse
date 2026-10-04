<?php

namespace App\Enums;

enum Bezugsdatum: string
{
    case AnmeldungKinderhaus = 'anmeldung_kinderhaus_ab';
    case Anmeldung = 'anmeldung_ab';
    case Anlieferung = 'anlieferung_beginn';
    case Verkaufstag = 'verkaufstag';

    public function label(): string
    {
        return match ($this) {
            self::AnmeldungKinderhaus => 'Anmeldestart Kinderhaus',
            self::Anmeldung => 'Anmeldestart alle',
            self::Anlieferung => 'Anlieferung',
            self::Verkaufstag => 'Verkaufstag',
        };
    }
}
