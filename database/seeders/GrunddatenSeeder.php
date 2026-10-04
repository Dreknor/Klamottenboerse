<?php

namespace Database\Seeders;

use App\Models\Checklistenvorlage;
use App\Models\Kategorie;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/** Rollen, Kategorien und die Standard-Checkliste. Kann gefahrlos mehrfach laufen. */
class GrunddatenSeeder extends Seeder
{
    public const ROLLEN = [
        'admin' => 'Admin – Rechte, Einstellungen, Datenschutz',
        'orga' => 'Orga-Team – Börsen, Nummern, Mails, Abrechnung',
        'kasse' => 'Kasse – kassieren am Verkaufstag',
        'annahme' => 'Annahme, Rückpacken und Ausgabe (Tablet)',
    ];

    /** Titel, Phase, Tage relativ zum Verkaufstag */
    public const CHECKLISTE = [
        ['Termine, Ort und Kapazität festlegen', 'Planung', -90],
        ['Saal reservieren und bestätigen lassen', 'Planung', -84],
        ['Fest reservierte Nummern prüfen', 'Planung', -42],
        ['Mailplan und Texte prüfen', 'Planung', -40],
        ['Schichtplan anlegen und Helfer aufrufen', 'Anmeldung', -35],
        ['Anmeldestand und Blockausgleich prüfen', 'Anmeldung', -21],
        ['Unterbesetzte Schichten nachbesetzen', 'Vorbereitung', -10],
        ['Wechselgeld bei der Bank bestellen (Auszahlungsplan der letzten Börse)', 'Vorbereitung', -7],
        ['Listen und Notfall-Kassenliste drucken', 'Vorbereitung', -2],
        ['Kassengeräte laden und testen', 'Vorbereitung', -1],
        ['Café-Erlöse erfassen', 'Verkauf', 0],
        ['Abrechnung berechnen und prüfen', 'Abrechnung', 0],
        ['Ergebnis für Verkäufer freigeben', 'Abrechnung', 0],
        ['Spendenbetrag an den Förderverein melden', 'Nachbereitung', 7],
        ['Feedback auswerten und Nachbesprechung', 'Nachbereitung', 14],
    ];

    public const KATEGORIEN = [
        'Oberteile', 'Hosen und Röcke', 'Kleider', 'Jacken und Matschkleidung', 'Schuhe',
        'Spielzeug', 'Bücher', 'Fahrzeuge', 'Kinderwagen und Sitze', 'Möbel und Betten', 'Sonstiges',
    ];

    public function run(): void
    {
        foreach (array_keys(self::ROLLEN) as $rolle) {
            Role::findOrCreate($rolle, 'web');
        }

        foreach (self::KATEGORIEN as $i => $name) {
            Kategorie::firstOrCreate(['name' => $name], ['sortierung' => $i]);
        }

        if (Checklistenvorlage::query()->doesntExist()) {
            $vorlage = Checklistenvorlage::create(['name' => 'Standard-Checkliste je Börse', 'fuer_neue_boersen' => true]);
            foreach (self::CHECKLISTE as $i => [$titel, $phase, $tage]) {
                $vorlage->eintraege()->create(['titel' => $titel, 'phase' => $phase, 'versatz_tage' => $tage, 'sortierung' => $i]);
            }
        }
    }
}
