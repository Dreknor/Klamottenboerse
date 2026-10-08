<?php

namespace App\Enums;

enum TeilnahmeStatus: string
{
    case Angefragt = 'angefragt';
    case Warteliste = 'warteliste';
    case Angeboten = 'angeboten';
    case Zugeteilt = 'zugeteilt';
    case Angeliefert = 'angeliefert';
    case Abgerechnet = 'abgerechnet';
    case Ausgezahlt = 'ausgezahlt';
    case Abgesagt = 'abgesagt';

    public function label(): string
    {
        return match ($this) {
            self::Angefragt => 'Nummer angefragt',
            self::Warteliste => 'Warteliste',
            self::Angeboten => 'Nummer angeboten',
            self::Zugeteilt => 'Nummer zugeteilt',
            self::Angeliefert => 'Angeliefert',
            self::Abgerechnet => 'Abgerechnet',
            self::Ausgezahlt => 'Ausgezahlt',
            self::Abgesagt => 'Abgesagt',
        };
    }

    public function farbe(): string
    {
        return match ($this) {
            self::Angefragt, self::Warteliste, self::Angeboten => 'amber',
            self::Zugeteilt => 'sky',
            self::Angeliefert => 'indigo',
            self::Abgerechnet, self::Ausgezahlt => 'emerald',
            self::Abgesagt => 'stone',
        };
    }

    /** Status, in denen die Teilnahme eine Nummer belegt. */
    public static function mitNummer(): array
    {
        return [self::Angeboten, self::Zugeteilt, self::Angeliefert, self::Abgerechnet, self::Ausgezahlt];
    }
}
