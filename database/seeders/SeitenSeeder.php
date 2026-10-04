<?php

namespace Database\Seeders;

use App\Models\Seite;
use Illuminate\Database\Seeder;

/**
 * Entwürfe für Impressum und Datenschutzerklärung. Bestehende Seiten werden nicht überschrieben.
 * Die Texte müssen vor dem Livegang vom Träger geprüft werden.
 */
class SeitenSeeder extends Seeder
{
    public const SEITEN = [
        'impressum' => ['Impressum', <<<'MD'
## Angaben gemäß § 5 DDG

**{betreiber_name}**

{betreiber_anschrift}

**Vertreten durch:** {betreiber_vertreten}

## Kontakt

- E-Mail: {kontakt_email}
- Telefon: {kontakt_telefon}

## Registereintrag

{register}

## Verantwortlich für den Inhalt

{betreiber_vertreten}, Anschrift wie oben

## Hinweis

Die Klamottenbörse wird ehrenamtlich von Eltern des Ev. Kinderhauses organisiert. Wir bemühen uns um aktuelle und richtige Angaben, können für Inhalte externer Links aber keine Haftung übernehmen.
MD],
        'datenschutz' => ['Datenschutzerklärung', <<<'MD'
Wir nehmen den Schutz deiner Daten ernst und verarbeiten nur, was wir für die Organisation der Klamottenbörse wirklich brauchen.

## 1. Verantwortlich

{betreiber_name}, {betreiber_anschrift}, E-Mail: {kontakt_email}

Ansprechperson für Datenschutz: {datenschutz_kontakt}

## 2. Hosting und Server-Protokolle

Die Website läuft bei {hoster}. Beim Aufruf speichert der Server technisch notwendige Angaben (IP-Adresse, Zeitpunkt, aufgerufene Seite, Browser) in Protokolldateien, um den sicheren Betrieb zu gewährleisten (Art. 6 Abs. 1 lit. f DSGVO). Die Protokolle werden nach spätestens 14 Tagen gelöscht.

## 3. Cookies und lokaler Speicher

Wir verwenden **nur technisch notwendige Cookies**: ein Sitzungs-Cookie (damit du angemeldet bleibst und Formulare funktionieren) und ein Sicherheits-Cookie gegen gefälschte Formularanfragen. Im Speicher deines Browsers merken wir uns, dass du den Cookie-Hinweis gesehen hast. An der Kasse werden Verkäufe zusätzlich lokal zwischengespeichert, bis sie übertragen sind.

Wir setzen **keine** Analyse-, Werbe- oder Tracking-Cookies ein und binden keine Inhalte fremder Anbieter ein (keine externen Schriftarten, Karten oder Videos). Rechtsgrundlage ist § 25 Abs. 2 TDDDG in Verbindung mit Art. 6 Abs. 1 lit. f DSGVO.

## 4. Anmeldung als Verkäufer/in

Für die Anmeldung erheben wir Vorname, Nachname, E-Mail-Adresse, optional Telefonnummer sowie die Angabe, ob ein Bezug zum Kinderhaus besteht. Damit vergeben wir deine Verkäufernummer, informieren dich über Termine und rechnen deine Verkäufe ab (Art. 6 Abs. 1 lit. b DSGVO).

Zu jeder Teilnahme speichern wir Nummer, verkaufte Artikel, Umsatz, Spende und Auszahlung. Freiwillig im Portal erfasste Artikel (Beschreibung, Größe, Preis) nutzen wir für Etiketten und die Abrechnung.

## 5. Info-Mails zu künftigen Börsen

Wenn du zugestimmt hast, informieren wir dich per Mail, sobald die Anmeldung für eine neue Börse startet (Art. 6 Abs. 1 lit. a DSGVO). Du kannst das jederzeit über den Link in jeder Mail abbestellen.

## 6. Helfer/innen

Wenn du dich für eine Schicht einträgst, speichern wir Name, E-Mail-Adresse, optional Telefonnummer und die gewählten Schichten, um den Einsatz zu planen und dich zu erinnern (Art. 6 Abs. 1 lit. b DSGVO).

## 7. Kontakt per E-Mail

Schreibst du uns eine Mail, speichern wir deine Nachricht und ordnen sie gegebenenfalls deinem Eintrag zu, um sie zu beantworten (Art. 6 Abs. 1 lit. b bzw. f DSGVO).

## 8. Feedback

Nach einer Börse bitten wir per Mail um eine kurze, freiwillige Rückmeldung. Die Antworten werten wir nur zusammengefasst aus.

## 9. Wer Zugriff hat

Zugriff auf deine Daten hat nur das ehrenamtliche Orga-Team. Helfer an Annahme, Kasse und Ausgabe sehen nur Name und Verkäufernummer. Wir geben keine Daten an Dritte weiter; ausgenommen ist unser Hosting-Anbieter, der in unserem Auftrag tätig ist (Art. 28 DSGVO).

## 10. Wie lange wir speichern

Hast du 24 Monate an keiner Börse teilgenommen, schreiben wir dich an und löschen deine Daten danach, wenn du nicht widersprichst. Abrechnungsdaten bewahren wir so lange auf, wie es gesetzliche Pflichten verlangen – dann ohne Personenbezug, soweit möglich.

## 11. Deine Rechte

Du hast das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch sowie das Recht, eine erteilte Einwilligung jederzeit zu widerrufen. Schreib uns einfach an {kontakt_email}.

Außerdem kannst du dich bei einer Datenschutz-Aufsichtsbehörde beschweren, zum Beispiel bei der Sächsischen Datenschutz- und Transparenzbeauftragten.
MD],
    ];

    /** Fotos aus public/images, die als Bilder der Startseite übernommen werden (Datei => Bildbeschreibung). */
    public const FOTOS = [
        'DSC_7821-768x513.jpg' => 'Besucherinnen und Besucher stöbern an den Tischen',
        'DSC_7750-1-300x200.jpg' => 'Sortierte Kinderkleidung auf Tischen und an Kleiderständern',
        'DSC_7755-1-300x200.jpg' => 'Kleider und Röcke an der Kleiderstange',
        'DSC_7757-1-300x200.jpg' => 'Bunte Kinderjacken auf Bügeln',
        'DSC_7760-2-300x200.jpg' => 'Tische mit Hosen und Pullovern, nach Größen sortiert',
        'DSC_7770-300x200.jpg' => 'Blick in den Saal mit langen Verkaufstischen',
        'DSC_7785-300x200.jpg' => 'Spielzeugtiere auf dem Spielzeugtisch',
        'DSC_7786-300x200.jpg' => 'Brettspiele und Puzzles zum Verkauf',
        'DSC_7805-300x200.jpg' => 'Blick durch den Saal mit Sortiertischen',
    ];

    /** @param  list<int>  $galerie  Bild-IDs der Seite */
    public static function startBloecke(array $galerie = [], ?int $regenbild = null): array
    {
        return [
            ['typ' => 'kopf', 'titel' => 'Sortierter Kindersachenflohmarkt in Radebeul', 'logo' => true,
                'text' => 'Zweimal im Jahr zugunsten des Ev. Kinderhauses der Friedenskirchgemeinde – organisiert von ehrenamtlichen Eltern.'],
            ['typ' => 'termin', 'titel' => ''],
            ['typ' => 'zwei_spalten',
                'links_titel' => 'So läuft es ab',
                'links' => "1. Online anmelden und Verkäufernummer erhalten.\n2. Artikel beschriften – von Hand oder mit Etiketten aus deinem Portal.\n3. Kiste zum Annahme-Termin abgeben.\n4. Wir sortieren und verkaufen für dich.\n5. Restware und Erlös am Abend abholen.",
                'rechts_titel' => 'Kosten',
                'rechts' => 'Es gibt keine Standgebühr. Dafür spenden die Verkäufer {provision} % des Erlöses an das Kinderhaus – direkt bei der Abrechnung.'],
            ['typ' => 'bild', 'media_id' => $regenbild, 'pfad' => $regenbild ? '' : 'images/DSC_7746-1-1024x684.jpg',
                'alt' => 'Bunte Regenjacken und Matschhosen an einem Kleiderständer mit Schild „Regensachen“', 'beschriftung' => '', 'breite' => 'voll'],
            ['typ' => 'zwei_spalten',
                'links_titel' => 'Das wird verkauft',
                'links' => "- saisonabhängige Kinderbekleidung, Matschkleidung\n- Schuhe, Gummistiefel\n- Kinderwagen, Auto- und Fahrradsitze, Tragehilfen\n- Laufgitter, Kinderbetten\n- Spielzeug, Bücher, Fahrzeuge",
                'rechts_titel' => 'Das nehmen wir nicht an',
                'rechts' => "- Babykleidung unter Größe 74\n- Erwachsenenkleidung, Erstausstattung\n- Plüschtiere, abgetragene Schuhe\n\nSchwer verkäufliche Artikel behalten wir uns vor, in der Kiste zu lassen."],
            ['typ' => 'galerie', 'titel' => 'Eindrücke', 'bilder' => $galerie],
            ['typ' => 'schichten', 'titel' => 'Du willst helfen?', 'text' => ''],
        ];
    }

    public const VERKAEUFER_INFO = [
        ['typ' => 'termin', 'titel' => 'Termine der nächsten Börse'],
        ['typ' => 'text', 'titel' => 'Annahme und Abholung',
            'text' => "Die Kisten nehmen wir am Tag vor dem Verkauf an ({anlieferung}). Restware und Erlös gibt es am Verkaufstag ({abholung}).\n\nOrt und Uhrzeiten stehen auch in der Mail mit deiner Verkäufernummer."],
        ['typ' => 'text', 'titel' => 'Etiketten',
            'text' => "Jedes Teil braucht ein Etikett mit **Verkäufernummer, Artikelnummer und Preis**, z. B. *215-7 · 4,50 €*.\n\nDu kannst handschriftlich beschriften oder deine Artikel im Portal erfassen und Etiketten mit Barcode drucken – das geht an der Kasse schneller. Vor Ort können wir nichts ausdrucken."],
        ['typ' => 'hinweis', 'titel' => 'Maximal {max_teile} Teile', 'farbe' => 'orange',
            'text' => 'Bitte bring nur saubere, gut erhaltene Sachen mit. Ein Kistenzettel mit deiner Nummer hilft uns beim Sortieren – du findest ihn in deinem Portal.'],
        ['typ' => 'knopf', 'text' => 'Jetzt anmelden', 'ziel' => 'anmeldung'],
    ];

    public const FAQ = [
        ['typ' => 'faq', 'titel' => 'Häufige Fragen', 'eintraege' => [
            ['frage' => 'Wie bekomme ich eine Verkäufernummer?', 'antwort' => 'Melde dich ab dem Anmeldestart online an und bestätige die Mail. Die Nummern werden in der Reihenfolge der Bestätigung vergeben.'],
            ['frage' => 'Bekomme ich meine Nummer vom letzten Mal wieder?', 'antwort' => 'Wenn sie noch frei ist, bieten wir sie dir automatisch wieder an.'],
            ['frage' => 'Was passiert, wenn alle Nummern vergeben sind?', 'antwort' => 'Du kommst auf die Warteliste. Wird ein Platz frei, bekommst du automatisch ein Angebot per Mail.'],
            ['frage' => 'Ich kann doch nicht teilnehmen – was nun?', 'antwort' => 'Bitte sag über den Link in deiner Mail oder im Portal ab. Dann rückt jemand von der Warteliste nach.'],
            ['frage' => 'Wie viel geht an das Kinderhaus?', 'antwort' => '{provision} % deines Erlöses spendest du an das Kinderhaus. Der Betrag wird direkt bei der Abrechnung abgezogen.'],
            ['frage' => 'Wo sehe ich, was verkauft wurde?', 'antwort' => 'Nach der Börse in deinem Portal – dort steht auch deine Abrechnung.'],
        ]],
        ['typ' => 'knopf', 'text' => 'Zu meinem Portal', 'ziel' => 'portal'],
    ];

    public function run(): void
    {
        foreach (self::SEITEN as $slug => [$titel, $inhalt]) {
            Seite::firstOrCreate(['slug' => $slug], compact('titel', 'inhalt') + ['veroeffentlicht_at' => now()]);
        }

        foreach ([
            'verkaeufer-info' => ['Verkäufer-Info', self::VERKAEUFER_INFO, 1],
            'faq' => ['Fragen & Antworten', self::FAQ, 2],
        ] as $slug => [$titel, $bloecke, $reihenfolge]) {
            Seite::firstOrCreate(['slug' => $slug], [
                'titel' => $titel, 'bloecke' => $bloecke, 'im_menue' => true,
                'menue_reihenfolge' => $reihenfolge, 'veroeffentlicht_at' => now(),
            ]);
        }

        if (Seite::query()->where('slug', 'start')->doesntExist()) {
            $start = Seite::create(['slug' => 'start', 'titel' => 'Startseite', 'bloecke' => self::startBloecke(), 'veroeffentlicht_at' => now()]);
            if (app()->runningUnitTests()) {
                return; // Tests brauchen keine Fotos
            }
            $galerie = [];
            foreach (self::FOTOS as $datei => $alt) {
                if (is_file(public_path('images/'.$datei))) {
                    $galerie[] = $start->addMedia(public_path('images/'.$datei))->preservingOriginal()
                        ->withCustomProperties(['alt' => $alt])->toMediaCollection('bilder')->id;
                }
            }
            $regen = is_file(public_path('images/DSC_7746-1-1024x684.jpg'))
                ? $start->addMedia(public_path('images/DSC_7746-1-1024x684.jpg'))->preservingOriginal()
                    ->withCustomProperties(['alt' => 'Bunte Regenjacken und Matschhosen an einem Kleiderständer'])->toMediaCollection('bilder')->id
                : null;
            $start->update(['bloecke' => self::startBloecke($galerie, $regen)]);
        }
    }
}
