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

danke, dass du bei der Klamottenbörse am {datum} hilfst! Deine Schicht(en) findest du in deiner [Übersicht]({portal_link}).

Ort: {ort}

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
        'feedback' => ['Feedback', 'Wie war die Klamottenbörse? 3 kurze Fragen', <<<'TXT'
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

    public function run(): void
    {
        foreach (self::VORLAGEN as $schluessel => [$name, $betreff, $inhalt]) {
            Mailvorlage::firstOrCreate(['schluessel' => $schluessel], compact('name', 'betreff', 'inhalt'));
        }
    }
}
