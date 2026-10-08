<?php

namespace App\Domain\Reputation;

use App\Models\Person;
use App\Support\Einstellungen;

/**
 * Reputation eines Verkäufers aus den Punkten seiner wirksamen Vermerke.
 *
 * - gut:      unter der Warnschwelle
 * - warnung:  ab „reputation_warnung_ab“ Punkten – nur ein Hinweis fürs Team
 * - gesperrt: ab „reputation_sperre_ab“ Punkten – keine automatische Nummernvergabe mehr, nur Anfrage
 *
 * Eine Festlegung bei der Person (immer händisch / trotz Punkten frei) hat Vorrang.
 */
final class Reputation
{
    public const GUT = 'gut';

    public const WARNUNG = 'warnung';

    public const GESPERRT = 'gesperrt';

    public static function zeitraumMonate(): int
    {
        return max(1, (int) Einstellungen::get('reputation_zeitraum_monate'));
    }

    public static function warnungAb(): int
    {
        return max(1, (int) Einstellungen::get('reputation_warnung_ab'));
    }

    public static function sperreAb(): int
    {
        return max(1, (int) Einstellungen::get('reputation_sperre_ab'));
    }

    public static function punkte(Person $person): int
    {
        if (array_key_exists('reputation_punkte', $person->getAttributes())) {
            return (int) $person->getAttribute('reputation_punkte'); // per withSum vorgeladen
        }

        return (int) $person->vermerke()->wirksam()->sum('punkte');
    }

    public static function automatischGesperrt(Person $person): bool
    {
        return match ($person->nummernvergabe) {
            'haendisch' => true,
            'frei' => false,
            default => self::punkte($person) >= self::sperreAb(),
        };
    }

    public static function status(Person $person): string
    {
        if (self::automatischGesperrt($person)) {
            return self::GESPERRT;
        }

        return self::punkte($person) >= self::warnungAb() ? self::WARNUNG : self::GUT;
    }

    /** @return array{0: string, 1: string} [Text, Farbe] für ein Abzeichen */
    public static function anzeige(Person $person): array
    {
        return match (self::status($person)) {
            self::GESPERRT => ['nur händische Vergabe', 'red'],
            self::WARNUNG => [self::punkte($person).' Punkte', 'amber'],
            default => ['', 'stone'],
        };
    }
}
