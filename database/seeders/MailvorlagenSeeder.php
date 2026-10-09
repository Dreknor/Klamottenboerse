<?php

namespace Database\Seeders;

use App\Models\Mailvorlage;
use Illuminate\Database\Seeder;

/** Standardtexte. Bestehende Vorlagen werden nicht überschrieben (das Team darf sie anpassen). */
class MailvorlagenSeeder extends Seeder
{
    public const VORLAGEN = [
        'anmeldung_bestaetigen' => ['Anmeldung bestätigen', 'Bitte bestätige deine Anmeldung zur Klamottenbörse', <<<'TXT'
Hallo {vorname},

bitte bestätige deine Anmeldung als Verkäufer/in zur Klamottenbörse am {datum} mit einem Klick:

[Anmeldung bestätigen]({bestaetigen_link})

Erst nach der Bestätigung wird dir eine Verkäufernummer zugeteilt. Wer zuerst bestätigt, bekommt zuerst eine Nummer.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'anmeldung_moeglich' => ['Anmeldung möglich', 'Die Anmeldung zur Klamottenbörse am {datum} ist geöffnet', <<<'TXT'
Hallo {vorname},

die Anmeldung für die nächste Klamottenbörse am {datum} ist ab sofort möglich:

[Jetzt anmelden]({anmelde_link})

Die Nummern werden in der Reihenfolge der Anmeldung vergeben. Ort: {ort}.

Viele Grüße
dein Klamottenbörsen-Team

Du möchtest keine Infos mehr zu künftigen Börsen? [Hier abbestellen]({abbestellen_link})
TXT],
        'anmeldung_vorankuendigung' => ['Vorankündigung', 'Save the date: Klamottenbörse am {datum}', <<<'TXT'
Hallo {vorname},

die nächste Klamottenbörse steht fest – merk dir den Termin schon mal vor:

- Verkaufstag: {datum}
- Verkauf: {verkauf}
- Ort: {ort}
- Anmeldung für Kinderhaus-Familien ab: {anmeldung_kinderhaus_ab}
- Anmeldung für alle ab: {anmeldung_ab}

Die Verkäufernummern werden in der Reihenfolge der Anmeldung vergeben. Zum Start schicken wir dir den Link zur Anmeldung.

Jetzt ist die beste Zeit, die Kleiderschränke durchzusehen: Gesucht ist gut erhaltene Kinderkleidung, Spielzeug und Zubehör.
Pro Verkäufer sind maximal {max_teile} Teile möglich.

Viele Grüße
dein Klamottenbörsen-Team

Du möchtest keine Infos mehr zu künftigen Börsen? [Hier abbestellen]({abbestellen_link})
TXT],
        'helfer_gesucht' => ['Helfer gesucht', 'Hilfst du mit? Für die Klamottenbörse am {datum} suchen wir noch Helfer', <<<'TXT'
Hallo {vorname},

schön, dass du als Verkäufer/in dabei bist! Damit die Börse klappt, brauchen wir viele helfende Hände – beim Aufbau, an der Annahme, beim Sortieren, an der Kasse und beim Abbau.

Schon eine Schicht von ein, zwei Stunden hilft uns sehr. Hier siehst du alle Schichten und kannst dich direkt eintragen:

[Zur Helferliste]({helfer_link})

Bist du schon eingetragen? Dann vielen Dank – diese Mail kannst du ignorieren.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'warteliste_stand' => ['Warteliste: Stand kurz vor der Börse', 'Deine Warteliste für die Klamottenbörse am {datum}', <<<'TXT'
Hallo {vorname},

kurzer Zwischenstand: Bisher ist für die Klamottenbörse am {datum} leider kein Platz für dich frei geworden.

Bis kurz vor der Annahme ({anlieferung}) kann sich noch etwas tun – dann bekommst du sofort ein Angebot per Mail. Danach rückt niemand mehr nach.

Wir würden uns freuen, wenn du trotzdem vorbeischaust – zum Stöbern beim Verkauf ({verkauf}) oder als Helfer/in: [Zur Helferliste]({helfer_link})

Bei der nächsten Börse informieren wir dich wieder rechtzeitig zum Anmeldestart.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'annahme_morgen' => ['Annahme morgen', 'Morgen ist Annahme – deine Nummer {nummer}', <<<'TXT'
Hallo {vorname},

morgen geht es los! Hier alles Wichtige auf einen Blick:

- Annahme deiner Kiste: {anlieferung}
- Ort: {ort}
- Deine Nummer: **{nummer}**

Kurze Checkliste:

- Alle Artikel mit Etikett und deiner Nummer versehen
- Kiste außen gut sichtbar mit deiner Nummer beschriftet
- Kistenzettel ausgedruckt (falls du das [Verkäuferportal]({portal_link}) nutzt)
- Maximal {max_teile} Teile

Abholung von Restware und Erlös: {abholung}

Falls du doch nicht kommen kannst, sag bitte jetzt noch ab: [Teilnahme absagen]({absage_link})

Bis morgen!
dein Klamottenbörsen-Team
TXT],
        'abholung_heute' => ['Abholung heute', 'Heute ist Klamottenbörse – denk an die Abholung', <<<'TXT'
Hallo {vorname},

heute ist Klamottenbörse! Wir drücken deinen Sachen die Daumen.

Bitte denk an die Abholung von Restware und Erlös: **{abholung}** ({ort}). Bring zur Abholung deine Nummer **{nummer}** mit.

Bitte sei pünktlich – nach der Abholzeit bauen wir ab.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'nummer_zugeteilt' => ['Nummer zugeteilt', 'Deine Verkäufernummer {nummer} für die Klamottenbörse', <<<'TXT'
Hallo {vorname},

du bist dabei! Deine Verkäufernummer für die Klamottenbörse am {datum} lautet: **{nummer}**

- Annahme deiner Kiste: {anlieferung}
- Verkauf: {verkauf}
- Abholung von Restware und Erlös: {abholung}
- Ort: {ort}
- Maximal {max_teile} Teile

Wenn du möchtest, kannst du deine Artikel im [Verkäuferportal]({portal_link}) erfassen und Etiketten mit QR-Code drucken. Handschriftliche Etiketten sind genauso in Ordnung.

{provision} % des Erlöses gehen als Spende an das Kinderhaus.

Falls du doch nicht teilnehmen kannst, sag bitte hier ab, damit jemand von der Warteliste nachrücken kann: [Teilnahme absagen]({absage_link})

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'warteliste' => ['Warteliste', 'Du stehst auf der Warteliste der Klamottenbörse', <<<'TXT'
Hallo {vorname},

leider sind für die Klamottenbörse am {datum} gerade alle Nummern vergeben. Du stehst auf der Warteliste.

Sobald ein Platz frei wird, schicken wir dir automatisch ein Angebot per Mail.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'nummer_angefragt' => ['Nummer angefragt', 'Deine Anfrage für die Klamottenbörse am {datum}', <<<'TXT'
Hallo {vorname},

danke für deine Anmeldung zur Klamottenbörse am {datum}. Deine Verkäufernummer vergibt das Orga-Team diesmal persönlich.

Wir melden uns per Mail bei dir, sobald wir über deine Anfrage entschieden haben.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'anfrage_abgelehnt' => ['Anfrage abgelehnt', 'Deine Anfrage für die Klamottenbörse am {datum}', <<<'TXT'
Hallo {vorname},

leider können wir dir für die Klamottenbörse am {datum} keine Verkäufernummer geben.

Bei Fragen antworte gern einfach auf diese Mail.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'warteliste_angebot' => ['Angebot von der Warteliste', 'Ein Platz ist frei geworden – Nummer {nummer}', <<<'TXT'
Hallo {vorname},

für die Klamottenbörse am {datum} ist ein Platz frei geworden. Wir haben die Nummer **{nummer}** für dich reserviert.

Bitte bestätige bis **{angebot_bis}**:

[Nummer annehmen]({angebot_link})

Danach geht das Angebot an die nächste Person auf der Warteliste.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'angebot_abgelaufen' => ['Angebot abgelaufen', 'Dein Angebot für die Klamottenbörse ist abgelaufen', <<<'TXT'
Hallo {vorname},

leider hast du das Angebot für die Klamottenbörse am {datum} nicht rechtzeitig bestätigt. Der Platz geht an die nächste Person.

Bei Interesse kannst du dich gern erneut anmelden: [Zur Anmeldung]({anmelde_link})

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'absage_bestaetigt' => ['Absage bestätigt', 'Deine Absage für die Klamottenbörse', <<<'TXT'
Hallo {vorname},

schade, dass es diesmal nicht klappt. Wir haben deine Absage für die Klamottenbörse am {datum} erhalten; die Nummer {nummer} ist wieder frei.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'nummer_geaendert' => ['Nummer geändert', 'Deine Verkäufernummer hat sich geändert: {nummer}', <<<'TXT'
Hallo {vorname},

damit die Nummern am Verkaufstag gleichmäßig verteilt sind, haben wir deine Verkäufernummer von {alte_nummer} auf **{nummer}** geändert. Bitte verwende die neue Nummer auf deinen Etiketten.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'erinnerung_verkaeufer' => ['Erinnerung Verkäufer', 'In einer Woche ist Klamottenbörse – deine Nummer {nummer}', <<<'TXT'
Hallo {vorname},

kurze Erinnerung: Die Annahme der Kisten ist am {anlieferung} ({ort}). Deine Nummer ist **{nummer}**.

Im [Verkäuferportal]({portal_link}) kannst du Artikel erfassen und Etiketten oder einen Kistenzettel drucken. Vor Ort können wir nichts ausdrucken.

Falls du nicht kommen kannst: [Teilnahme absagen]({absage_link})

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'erinnerung_helfer' => ['Erinnerung Helfer', 'Danke, dass du hilfst – deine Schicht bei der Klamottenbörse', <<<'TXT'
Hallo {vorname},

danke, dass du bei der Klamottenbörse am {datum} hilfst! Du bist eingetragen für:

{schichten}

Ort: {ort}

Bitte sei ein paar Minuten vor Schichtbeginn da. Alle Infos findest du auch in deiner [Übersicht]({portal_link}).

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'helfer_eingetragen' => ['Helfer eingetragen', 'Danke! Du bist für eine Schicht eingetragen', <<<'TXT'
Hallo {vorname},

danke für deine Hilfe! Du bist eingetragen für: **{schicht}**

Falls etwas dazwischenkommt: [Schicht absagen]({helfer_absage_link})

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'helfer_abgesagt' => ['Helfer hat abgesagt (an das Team)', 'Absage: {helfer} – {schicht}', <<<'TXT'
Hallo {vorname},

**{helfer}** hat die Schicht für die Klamottenbörse am {datum} abgesagt:

- Schicht: {schicht}
- Jetzt besetzt: {besetzung}

Bitte schaut, ob ihr Ersatz findet: [Zu den Schichten]({schichten_link})

Diese Mail geht automatisch an das Orga-Team.
TXT],
        'ergebnis' => ['Ergebnis', 'Dein Ergebnis der Klamottenbörse', <<<'TXT'
Hallo {vorname},

danke fürs Mitmachen! Dein Ergebnis der Klamottenbörse am {datum}:

- Verkaufte Artikel: {verkaufte_artikel}
- Umsatz: {umsatz}
- Spende an das Kinderhaus: {spende}
- Auszahlung: **{auszahlung}**

Die Details findest du im [Verkäuferportal]({portal_link}).

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'feedback' => ['Feedback', 'Wie war die Klamottenbörse? Ein paar kurze Fragen', <<<'TXT'
Hallo {vorname},

danke, dass du bei der Klamottenbörse am {datum} dabei warst! Hilf uns, noch besser zu werden – es dauert nur eine Minute:

[Feedback geben]({feedback_link})

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'aufgabe_erinnerung' => ['Erinnerung Aufgaben', 'Deine Aufgaben für die Klamottenbörse', <<<'TXT'
Hallo {vorname},

diese Aufgaben sind bald fällig:

{aufgaben}

[Zu meinen Aufgaben]({aufgaben_link})
TXT],
        'inaktiv_loeschung' => ['Löschung wegen Inaktivität', 'Möchtest du weiter bei der Klamottenbörse dabei sein?', <<<'TXT'
Hallo {vorname},

du warst seit über zwei Jahren nicht mehr bei der Klamottenbörse dabei. Damit wir keine Daten unnötig aufbewahren, löschen wir deinen Eintrag am **{frist}**.

Du möchtest weiter informiert werden? Dann klick einfach hier – das genügt:

[Ja, ich bleibe dabei]({portal_link})

Wenn du nichts tust, löschen wir deine Daten automatisch.

Viele Grüße
dein Klamottenbörsen-Team
TXT],
        'login_link' => ['Login-Link', 'Dein Link zum Verkäuferportal', <<<'TXT'
Hallo {vorname},

hier ist dein persönlicher Link zum Verkäuferportal:

[Zum Portal]({portal_link})

Viele Grüße
dein Klamottenbörsen-Team
TXT],
    ];

    /** Diese Mails gehen zusätzlich als Push an Personen, die Push-Nachrichten eingeschaltet haben. */
    public const MIT_PUSH = ['erinnerung_verkaeufer', 'erinnerung_helfer', 'warteliste_angebot', 'aufgabe_erinnerung', 'nummer_zugeteilt',
        'annahme_morgen', 'abholung_heute', 'helfer_abgesagt'];

    public function run(): void
    {
        foreach (self::VORLAGEN as $schluessel => [$name, $betreff, $inhalt]) {
            $push = in_array($schluessel, self::MIT_PUSH, true);
            Mailvorlage::firstOrCreate(['schluessel' => $schluessel], compact('name', 'betreff', 'inhalt', 'push'));
        }
    }
}
