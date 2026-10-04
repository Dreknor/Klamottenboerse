<?php

namespace App\Enums;

enum KinderhausBezug: string
{
    case Keiner = 'keiner';
    case Familie = 'familie';
    case Mitarbeiter = 'mitarbeiter';

    public function label(): string
    {
        return match ($this) {
            self::Keiner => 'kein Bezug',
            self::Familie => 'Familie im Kinderhaus',
            self::Mitarbeiter => 'Mitarbeiter/in',
        };
    }

    public function hatVorlauf(): bool
    {
        return $this !== self::Keiner;
    }
}
