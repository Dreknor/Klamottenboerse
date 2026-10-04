<?php

namespace App\Enums;

enum Zielgruppe: string
{
    case InteressentenKinderhaus = 'interessenten_kinderhaus';
    case Interessenten = 'interessenten';
    case Verkaeufer = 'verkaeufer';
    case Warteliste = 'warteliste';
    case Helfer = 'helfer';
    case VerkaeuferUndHelfer = 'verkaeufer_und_helfer';
    case Team = 'team';

    public function label(): string
    {
        return match ($this) {
            self::InteressentenKinderhaus => 'Interessenten mit Kinderhaus-Bezug',
            self::Interessenten => 'Alle Interessenten (mit Einwilligung)',
            self::Verkaeufer => 'Verkäufer dieser Börse',
            self::Warteliste => 'Warteliste dieser Börse',
            self::Helfer => 'Helfer dieser Börse',
            self::VerkaeuferUndHelfer => 'Verkäufer und Helfer dieser Börse',
            self::Team => 'Alle Team-Mitglieder',
        };
    }
}
