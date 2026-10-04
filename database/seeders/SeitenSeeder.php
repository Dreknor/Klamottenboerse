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

    public function run(): void
    {
        foreach (self::SEITEN as $slug => [$titel, $inhalt]) {
            Seite::firstOrCreate(['slug' => $slug], compact('titel', 'inhalt'));
        }
    }
}
