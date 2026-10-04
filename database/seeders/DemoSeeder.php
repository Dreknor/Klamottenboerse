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
use App\Models\Ort;
use App\Models\Person;
use App\Models\Schicht;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Beispieldaten für die lokale Entwicklung (nur APP_ENV=local).
 * Login Orga-Team: admin@klamottenboerse.test / klamotten-demo-2026
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

        $erster = $neu->teilnahmen()->mitNummer()->where('ist_kinderhaus', false)->with('person')->first();
        foreach (['Regenjacke blau' => [450, '110'], 'Gummistiefel' => [600, '28'], 'Bilderbuch' => [150, null]] as $was => [$preis, $groesse]) {
            $erster->artikel()->create(['laufnummer' => $erster->artikel()->count() + 1, 'beschreibung' => $was, 'groesse' => $groesse, 'preis_cent' => $preis]);
        }

        $neu->schichten()->get()->each(function (Schicht $schicht) use ($personen) {
            $personen->random(max(0, $schicht->soll - 1))->each(fn ($p) => app(HelferEintragen::class)($schicht, $p, 'online', false));
        });

        $neu->aufgaben()->orderBy('faellig_am')->limit(3)->update(['zustaendig_id' => $admin->id]);
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
