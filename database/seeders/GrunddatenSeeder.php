<?php

namespace Database\Seeders;

use App\Models\Checklistenvorlage;
use App\Models\FeedbackFrage;
use App\Models\Kategorie;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/** Rollen, Kategorien, Feedback-Fragen und die Standard-Checkliste. Kann gefahrlos mehrfach laufen. */
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

    /** Standardfragen im Feedback nach der Börse: [Text, Typ] */
    public const FEEDBACK_FRAGEN = [
        ['Wie zufrieden warst du insgesamt?', 'sterne'],
        ['Was hat dir gut gefallen?', 'text'],
        ['Was können wir besser machen?', 'text'],
    ];

    /** Was Verkäufer überwiegend mitbringen: [Name, Gruppe, Größe von, Größe bis] – wie in V1. */
    public const KATEGORIEN = [
        ['Kleidung Gr. 50–68', 'Kleidung', 0, 68],
        ['Kleidung Gr. 74–92', 'Kleidung', 69, 92],
        ['Kleidung Gr. 98–116', 'Kleidung', 93, 116],
        ['Kleidung Gr. 122–140', 'Kleidung', 117, 140],
        ['Kleidung Gr. 146–176', 'Kleidung', 141, 999],
        ['Umstandsmode', 'Kleidung', null, null],
        ['Schuhe', 'Weiteres', null, null],
        ['Spielzeug & Spiele', 'Weiteres', null, null],
        ['Bücher & Medien', 'Weiteres', null, null],
        ['Babyausstattung', 'Weiteres', null, null],
        ['Kinderwagen, Fahrzeuge & Großteile', 'Weiteres', null, null],
        ['Sport & Outdoor', 'Weiteres', null, null],
        ['Sonstiges', 'Weiteres', null, null],
    ];

    public function run(): void
    {
        foreach (array_keys(self::ROLLEN) as $rolle) {
            Role::findOrCreate($rolle, 'web');
        }

        foreach (self::KATEGORIEN as $i => [$name, $gruppe, $von, $bis]) {
            Kategorie::firstOrCreate(['name' => $name], [
                'gruppe' => $gruppe, 'groesse_von' => $von, 'groesse_bis' => $bis, 'sortierung' => ($i + 1) * 10,
            ]);
        }

        if (FeedbackFrage::query()->doesntExist()) {
            foreach (self::FEEDBACK_FRAGEN as $i => [$text, $typ]) {
                FeedbackFrage::create(['text' => $text, 'typ' => $typ, 'sortierung' => ($i + 1) * 10]);
            }
        }

        if (Checklistenvorlage::query()->doesntExist()) {
            $vorlage = Checklistenvorlage::create(['name' => 'Standard-Checkliste je Börse', 'fuer_neue_boersen' => true]);
            foreach (self::CHECKLISTE as $i => [$titel, $phase, $tage]) {
                $vorlage->eintraege()->create(['titel' => $titel, 'phase' => $phase, 'versatz_tage' => $tage, 'sortierung' => $i]);
            }
        }
    }
}
