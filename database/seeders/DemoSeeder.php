<?php

namespace Database\Seeders;

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Domain\Boersen\Actions\BoerseAnlegen;
use App\Domain\Boersen\Actions\BoerseKopieren;
use App\Domain\Kasse\BonErfassen;
use App\Domain\Schichten\HelferEintragen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\BoerseStatus;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Feedback;
use App\Models\FeedbackFrage;
use App\Models\Ort;
use App\Models\Person;
use App\Models\Posteingang;
use App\Models\Protokoll;
use App\Models\Schicht;
use App\Models\Termin;
use App\Support\Demo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Beispieldaten für die lokale Entwicklung und die Demo-Installation (DEMO_MODUS=true).
 * Alle Personen sind erfunden, alle Adressen enden auf example.org/.com/.net (gehen nirgends hin).
 * Login lokal: admin@klamottenboerse.test / klamotten-demo-2026 – in der Demo per Klick.
 */
class DemoSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@klamottenboerse.test';

    public const ADMIN_PASSWORT = 'klamotten-demo-2026';

    public function run(): void
    {
        $admin = Person::firstOrCreate(['email' => self::ADMIN_EMAIL], [
            'vorname' => 'Anna', 'nachname' => 'Admin', 'password' => self::ADMIN_PASSWORT, 'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin', 'orga']);

        $kasse = Person::firstOrCreate(['email' => 'kasse@klamottenboerse.test'], [
            'vorname' => 'Karl', 'nachname' => 'Kasse', 'password' => self::ADMIN_PASSWORT,
        ]);
        $kasse->syncRoles(['kasse', 'annahme']);

        // Je Rolle ein Demo-Zugang (Anmeldung in der Demo per Klick)
        $demo = [];
        foreach (Demo::ZUGAENGE as $rolle => [$email]) {
            [$vorname, $nachname] = match ($rolle) {
                'admin' => ['Antje', 'Admin'], 'orga' => ['Olaf', 'Orga'], 'kasse' => ['Kim', 'Kasse'],
                'annahme' => ['Anja', 'Annahme'], default => ['Vera', 'Verkäuferin'],
            };
            $demo[$rolle] = Person::firstOrCreate(['email' => $email], [
                'vorname' => $vorname, 'nachname' => $nachname, 'password' => Str::random(40),
                'email_verified_at' => now(), 'telefon' => '0351 000000',
            ]);
            if ($rolle !== 'verkaeufer') {
                $demo[$rolle]->syncRoles($rolle === 'admin' ? ['admin', 'orga'] : [$rolle]);
            }
        }

        $ort = Ort::firstOrCreate(['name' => 'Luthersaal der Friedenskirche'], ['adresse' => 'Altkötzschenbroda 40, 01445 Radebeul']);
        $personen = Person::factory()->count(80)->create();
        $personen->take(15)->each->update(['kinderhaus_bezug' => 'familie']);

        // Vergangene Börse mit Verkäufen
        $tag = today()->subMonths(6)->next('Saturday');
        $alt = app(BoerseAnlegen::class)([
            'titel' => 'Klamottenbörse '.$tag->format('d.m.Y'), 'verkaufstag' => $tag, 'ort_id' => $ort->id,
            'anmeldung_kinderhaus_ab' => $tag->copy()->subDays(35)->setTime(18, 0), 'anmeldung_ab' => $tag->copy()->subDays(28)->setTime(18, 0),
            'anlieferung_beginn' => $tag->copy()->subDay()->setTime(14, 30), 'anlieferung_ende' => $tag->copy()->subDay()->setTime(17, 30),
            'verkauf_beginn' => $tag->copy()->setTime(9, 0), 'verkauf_ende' => $tag->copy()->setTime(12, 0),
            'abholung_beginn' => $tag->copy()->setTime(17, 0), 'abholung_ende' => $tag->copy()->setTime(18, 30),
            'kapazitaet' => 60, 'max_teile' => 60,
        ]);
        foreach (['Annahme' => [-1, 14, 18, 6], 'Kasse' => [0, 8, 12, 4], 'Café' => [0, 8, 12, 2], 'Ausgabe' => [0, 17, 19, 3]] as $bereich => [$tagVersatz, $von, $bis, $soll]) {
            $alt->schichten()->create(['bereich' => $bereich, 'beginn' => $tag->copy()->addDays($tagVersatz)->setTime($von, 0),
                'ende' => $tag->copy()->addDays($tagVersatz)->setTime($bis, 0), 'soll' => $soll]);
        }

        $this->travelTo($alt->anmeldung_ab->copy()->addHour(), function () use ($alt, $personen) {
            $personen->take(55)->each(fn ($p) => app(Anmelden::class)($alt, $p, mailSenden: false));
        });

        $nummern = $alt->teilnahmen()->mitNummer()->pluck('nummer')->all();
        foreach (range(1, 250) as $i) {
            $positionen = collect(range(1, random_int(1, 6)))->map(fn () => [
                'nummer' => $nummern[array_rand($nummern)], 'artikel' => random_int(1, 60), 'preis_cent' => random_int(5, 120) * 10,
            ])->unique(fn ($p) => $p['nummer'].'-'.$p['artikel'])->values()->all();
            app(BonErfassen::class)($alt, null, [
                'uuid' => (string) Str::uuid(),
                'erstellt_am' => $tag->copy()->setTime(9, 0)->addMinutes(random_int(0, 179))->toIso8601String(),
                'positionen' => $positionen,
            ]);
        }
        app(AbrechnungBerechnen::class)($alt);
        $alt->teilnahmen()->where('status', TeilnahmeStatus::Abgerechnet)->update(['status' => TeilnahmeStatus::Ausgezahlt]);
        $alt->sonstigeEinnahmen()->create(['art' => 'kuchen', 'betrag_cent' => 31250]);
        $alt->update(['status' => BoerseStatus::Abgeschlossen, 'ergebnis_freigegeben' => true]);
        $alt->aufgaben()->update(['erledigt_at' => $tag, 'erledigt_von' => $admin->id]);

        // Aktuelle Börse als Kopie – Anmeldung läuft
        $neu = app(BoerseKopieren::class)($alt, today()->addWeeks(4)->next('Saturday'));
        $neu->update(['anmeldung_kinderhaus_ab' => now()->subDays(8), 'anmeldung_ab' => now()->subDay(), 'kapazitaet' => 60]);
        $personen->shuffle()->take(45)->each(fn ($p) => app(Anmelden::class)($neu, $p, mailSenden: false));

        // Demo-Verkäuferin: angemeldet, mit Artikeln – zum Ausprobieren von Portal, Etiketten und Kasse
        $vera = app(Anmelden::class)($neu, $demo['verkaeufer'], mailSenden: false);
        foreach (['Matschhose Gr. 98' => 500, 'Winterjacke Gr. 104' => 1200, 'Puzzle 48 Teile' => 200, 'Laufrad' => 2500] as $was => $preis) {
            $vera->artikel()->create(['laufnummer' => $vera->artikel()->count() + 1, 'beschreibung' => $was, 'preis_cent' => $preis]);
        }
        $this->beispielinhalte($alt, $neu, $demo, $personen);

        $erster = $neu->teilnahmen()->mitNummer()->where('ist_kinderhaus', false)->with('person')->first();
        foreach (['Regenjacke blau' => [450, '110'], 'Gummistiefel' => [600, '28'], 'Bilderbuch' => [150, null]] as $was => [$preis, $groesse]) {
            $erster->artikel()->create(['laufnummer' => $erster->artikel()->count() + 1, 'beschreibung' => $was, 'groesse' => $groesse, 'preis_cent' => $preis]);
        }

        $neu->schichten()->get()->each(function (Schicht $schicht) use ($personen) {
            $personen->random(max(0, $schicht->soll - 1))->each(fn ($p) => app(HelferEintragen::class)($schicht, $p, 'online', false));
        });

        // Was laut Checkliste schon vorbei ist, gilt als erledigt; die nächsten Aufgaben hat das Orga-Team
        $neu->aufgaben()->where('faellig_am', '<', today())->update(['erledigt_at' => now()->subDay(), 'erledigt_von' => $demo['orga']->id]);
        $neu->aufgaben()->whereNull('erledigt_at')->orderBy('faellig_am')->limit(3)
            ->update(['zustaendig_id' => Demo::aktiv() ? $demo['orga']->id : $admin->id]);
    }

    /** Posteingang, Protokoll, Termine und Feedback, damit jeder Bereich etwas zum Anschauen hat. */
    private function beispielinhalte(Boerse $alt, Boerse $neu, array $demo, Collection $personen): void
    {
        $fragen = [
            ['Frage zur Anmeldung', 'Hallo, ich habe die Bestätigungsmail nicht bekommen. Können Sie mich trotzdem anmelden? Viele Grüße'],
            ['Kann ich zwei Kisten abgeben?', 'Liebes Team, ich habe viele Babysachen. Darf ich zwei Kisten bringen?'],
            ['Absage', 'Leider bin ich am Wochenende krank. Bitte geben Sie meine Nummer weiter. Danke!'],
        ];
        foreach ($fragen as $i => [$betreff, $text]) {
            $von = $personen->get($i);
            Posteingang::create([
                'ordner' => 'demo', 'uid' => $i + 1, 'message_id' => '<demo-'.$i.'@example.org>',
                'von_email' => $von->email, 'von_name' => $von->name, 'betreff' => $betreff, 'text' => $text,
                'empfangen_at' => now()->subHours(3 * ($i + 1)), 'person_id' => $von->id,
            ]);
        }

        Protokoll::create([
            'boerse_id' => $neu->id, 'titel' => 'Planungstreffen', 'datum' => today()->subWeek(), 'autor_id' => $demo['orga']->id,
            'teilnehmende' => 'Antje, Olaf, Kim, Anja', 'herkunft' => 'erstellt',
            'inhalt' => '## Termine
- Verkaufstag steht, Saal ist reserviert

**Beschluss:** Kuchenbasar wieder mit Waffeln

## Offene Punkte
- [ ] Plakate drucken und verteilen
- [ ] Wechselgeld bei der Bank holen
- [x] Helferliste verschicken',
        ]);

        foreach ([['Plakate aufhängen', 10], ['Aufbau-Besprechung', 3]] as [$titel, $tageVorher]) {
            Termin::create([
                'boerse_id' => $neu->id, 'titel' => $titel, 'ort' => 'Kinderhaus',
                'beginn' => $neu->verkaufstag->copy()->subDays($tageVorher)->setTime(19, 0),
                'ende' => $neu->verkaufstag->copy()->subDays($tageVorher)->setTime(20, 30),
            ]);
        }

        [$bewertung, $gut, $besser] = FeedbackFrage::query()->sortiert()->limit(3)->pluck('id')->all();
        foreach ($alt->teilnahmen()->with('person')->whereNotNull('person_id')->limit(12)->get() as $i => $t) {
            $feedback = Feedback::create([
                'boerse_id' => $alt->id, 'person_id' => $t->person_id, 'rolle' => 'verkaeufer', 'token' => Str::random(40),
                'beantwortet_at' => now()->subMonths(5),
            ]);
            $feedback->antworten()->create(['feedback_frage_id' => $bewertung, 'zahl' => [5, 4, 5, 3, 5, 4][$i % 6]]);
            foreach ([$gut => ['Super organisiert!', 'Schnelle Abrechnung', 'Nette Helfer', null][$i % 4],
                $besser => [null, 'Mehr Platz für Schuhe', 'Früher Einlass für Schwangere', null][$i % 4]] as $frage => $text) {
                if ($text) {
                    $feedback->antworten()->create(['feedback_frage_id' => $frage, 'text' => $text]);
                }
            }
        }
    }

    private function travelTo($zeitpunkt, callable $funktion): void
    {
        Carbon::setTestNow($zeitpunkt);
        try {
            $funktion();
        } finally {
            Carbon::setTestNow();
        }
    }
}
