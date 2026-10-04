<?php

namespace App\Domain\Kommunikation;

use App\Models\Boerse;
use App\Models\Person;
use App\Support\Einstellungen;
use Illuminate\Support\Facades\URL;

/**
 * Platzhalter wie {vorname} oder {datum} – gleich in Mails, auf der Website und in PDFs.
 */
class Platzhalter
{
    /** Für die Hilfe in der Vorlagen-Bearbeitung. */
    public const BESCHREIBUNG = [
        'vorname' => 'Vorname der Person',
        'nachname' => 'Nachname der Person',
        'nummer' => 'Verkäufernummer bei dieser Börse',
        'datum' => 'Verkaufstag, z. B. Samstag, 3. Oktober 2026',
        'verkauf' => 'Verkaufszeit, z. B. 9:00–12:00 Uhr',
        'ort' => 'Name und Adresse des Veranstaltungsortes',
        'anlieferung' => 'Annahme der Kisten (Tag und Uhrzeit)',
        'abholung' => 'Abholung von Restware und Erlös',
        'anmeldung_ab' => 'Anmeldestart für alle',
        'anmeldung_kinderhaus_ab' => 'Anmeldestart für Kinderhaus-Familien',
        'max_teile' => 'Maximale Teile je Verkäufer',
        'provision' => 'Spendenanteil in Prozent',
        'portal_link' => 'Persönlicher Link zum Verkäuferportal',
        'abbestellen_link' => 'Link, um künftige Info-Mails abzubestellen',
        'anmelde_link' => 'Link zum Anmeldeformular',
        'helfer_link' => 'Link zur Helferliste',
        'verein' => 'Name der Klamottenbörse',
    ];

    /**
     * @param  array<string, string|int|null>  $daten
     * @return array<string, string>
     */
    public static function fuer(?Person $person, ?Boerse $boerse, array $daten = []): array
    {
        $werte = [
            'verein' => Einstellungen::get('vereinsname'),
            'anmelde_link' => route('anmeldung.create'),
            'helfer_link' => route('helfer.index'),
        ];

        if ($person) {
            $werte += [
                'vorname' => $person->vorname,
                'nachname' => $person->nachname,
                'portal_link' => URL::temporarySignedRoute('portal.login', now()->addDays(60), ['person' => $person->uuid]),
                'abbestellen_link' => URL::signedRoute('infomails.abbestellen', ['person' => $person->uuid]),
            ];
        }

        if ($boerse) {
            $teilnahme = $person ? $boerse->teilnahmen()->where('person_id', $person->id)->first() : null;
            $werte += [
                'nummer' => (string) ($teilnahme?->nummer ?? ''),
                'datum' => $boerse->verkaufstag->locale('de')->isoFormat('dddd, D. MMMM YYYY'),
                'verkauf' => self::zeitraum($boerse->verkauf_beginn, $boerse->verkauf_ende, false),
                'ort' => trim(($boerse->ort?->name ?? '').', '.($boerse->ort?->adresse ?? ''), ', '),
                'anlieferung' => self::zeitraum($boerse->anlieferung_beginn, $boerse->anlieferung_ende),
                'abholung' => self::zeitraum($boerse->abholung_beginn, $boerse->abholung_ende),
                'anmeldung_ab' => self::zeitpunkt($boerse->anmeldung_ab),
                'anmeldung_kinderhaus_ab' => self::zeitpunkt($boerse->anmeldung_kinderhaus_ab),
                'max_teile' => (string) ($boerse->max_teile ?? ''),
                'provision' => rtrim(rtrim(number_format($boerse->provision_promille / 10, 1, ',', ''), '0'), ','),
            ];
        }

        return array_map(fn ($wert) => (string) $wert, array_merge($werte, $daten));
    }

    /** @param  array<string, string>  $werte */
    public static function ersetzen(string $text, array $werte): string
    {
        return preg_replace_callback('/\{([a-z_]+)\}/', fn ($m) => $werte[$m[1]] ?? $m[0], $text);
    }

    /** "18. Februar 2027, 18:00 Uhr" – ohne Uhrzeit, wenn keine angegeben ist (00:00). */
    public static function zeitpunkt($wert): string
    {
        if (! $wert) {
            return '';
        }

        return $wert->locale('de')->isoFormat($wert->format('H:i') === '00:00' ? 'D. MMMM YYYY' : 'D. MMMM YYYY, H:mm [Uhr]');
    }

    private static function zeitraum($von, $bis, bool $mitTag = true): string
    {
        if (! $von) {
            return '';
        }

        $text = $mitTag ? $von->locale('de')->isoFormat('dddd, D. MMMM, H:mm') : $von->format('G:i');

        return $text.($bis ? '–'.$bis->format('G:i') : '').' Uhr';
    }
}
