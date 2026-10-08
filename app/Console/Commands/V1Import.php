<?php

namespace App\Console\Commands;

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Enums\BoerseStatus;
use App\Enums\EinteilungStatus;
use App\Enums\KinderhausBezug;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Nummernreservierung;
use App\Models\Ort;
use App\Models\Person;
use App\Models\Schicht;
use App\Models\Teilnahme;
use App\Support\Belehrung;
use App\Support\Demo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Übernimmt die Daten aus V1 (Verbindung "v1", nur lesend).
 * Der Import ist wiederholbar: mit --frisch werden alle Fachdaten in V2 vorher geleert.
 * Am Ende werden je Börse Anzahl verkaufter Artikel und Umsatz auf den Cent verglichen;
 * bei Abweichungen wird alles zurückgerollt.
 */
class V1Import extends Command
{
    protected $signature = 'v1:import
        {--frisch : Vorhandene Fachdaten in V2 vorher löschen}
        {--probe : Alles prüfen und anzeigen, aber nichts speichern}';

    protected $description = 'Übernimmt Personen, Börsen, Nummern, Verkäufe, Artikel, Kisten, Schichten und Notizen aus V1';

    private Connection $v1;

    /** @var array<string, array<int, int>> */
    private array $mapping = [];

    /** @var list<string> */
    private array $hinweise = [];

    public function handle(): int
    {
        if (Demo::aktiv()) {
            $this->error('In der Demo werden keine echten Daten importiert.');

            return self::FAILURE;
        }

        $this->v1 = DB::connection('v1');

        try {
            $this->v1->getPdo();
        } catch (\Throwable $e) {
            $this->error('Keine Verbindung zur V1-Datenbank: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('frisch') && Person::query()->exists()) {
            $this->error('V2 enthält bereits Daten. Mit --frisch werden sie vorher gelöscht.');

            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            if ($this->option('frisch')) {
                $this->leeren();
            }

            $this->schritt('Personen', fn () => $this->personen());
            $this->schritt('Börsen', fn () => $this->boersen());
            $this->schritt('Teilnahmen und Nummern', fn () => $this->teilnahmen());
            $this->schritt('Verkäufe', fn () => $this->verkaeufe());
            $this->schritt('Artikel und Kisten', fn () => $this->artikelUndKisten());
            $this->schritt('Schichten und Helfer', fn () => $this->schichten());
            $this->schritt('Notizen', fn () => $this->notizen());
            $this->schritt('Abrechnungen', fn () => $this->abrechnungen());
            $this->mappingSpeichern();

            $ok = $this->pruefsummen();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import abgebrochen: '.$e->getMessage());
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }

        foreach (array_unique($this->hinweise) as $hinweis) {
            $this->warn('Hinweis: '.$hinweis);
        }

        if (! $ok) {
            DB::rollBack();
            $this->error('Prüfsummen stimmen nicht – nichts wurde gespeichert.');

            return self::FAILURE;
        }

        if ($this->option('probe')) {
            DB::rollBack();
            $this->info('Probelauf erfolgreich – es wurde nichts gespeichert.');

            return self::SUCCESS;
        }

        DB::commit();
        $this->info('Import abgeschlossen.');

        return self::SUCCESS;
    }

    private function schritt(string $name, callable $funktion): void
    {
        $this->output->write(str_pad($name, 28, '.').' ');
        $anzahl = $funktion();
        $this->line("<info>{$anzahl}</info>");
    }

    private function leeren(): void
    {
        $tabellen = ['v1_mapping', 'feedback', 'aufgaben', 'termine', 'notizen', 'nachrichten', 'posteingang', 'mailplan_eintraege',
            'einteilungen', 'schichten', 'abrechnungen', 'bonpositionen', 'bons', 'kassenschichten', 'kassen', 'kisten', 'artikel',
            'nummernreservierungen', 'teilnahmen', 'sonstige_einnahmen', 'boersen', 'orte', 'model_has_roles', 'personen'];

        DB::statement(DB::getDriverName() === 'sqlite' ? 'PRAGMA foreign_keys = OFF' : 'SET FOREIGN_KEY_CHECKS=0');
        foreach ($tabellen as $tabelle) {
            DB::table($tabelle)->delete();
        }
        DB::statement(DB::getDriverName() === 'sqlite' ? 'PRAGMA foreign_keys = ON' : 'SET FOREIGN_KEY_CHECKS=1');
    }

    private function map(string $tabelle, int $v1, int $v2): void
    {
        $this->mapping[$tabelle][$v1] = $v2;
    }

    private function ziel(string $tabelle, ?int $v1): ?int
    {
        return $v1 === null ? null : ($this->mapping[$tabelle][$v1] ?? null);
    }

    /** Interessenten und Benutzer → Personen (Dubletten über die E-Mail zusammengeführt). */
    private function personen(): int
    {
        $nachEmail = [];
        $benutzer = $this->v1->table('users')->get()->keyBy('id');
        $rollen = Role::query()->pluck('name')->flip();

        // Wer seine Löschung beantragt hat, wird nicht übernommen.
        $interessenten = $this->v1->table('interessenten')->whereNull('deleted_at')
            ->when($this->v1->getSchemaBuilder()->hasColumn('interessenten', 'deletion_requested_at'), fn ($q) => $q->whereNull('deletion_requested_at'))
            ->orderBy('id')->get();

        foreach ($interessenten as $i) {
            $email = Str::lower(trim((string) $i->mail)) ?: null;
            if ($email && (! filter_var($email, FILTER_VALIDATE_EMAIL) || str_ends_with($email, '@klamottenboerse.de'))) {
                $this->hinweise[] = "Interessent V1-ID {$i->id}: ungültige oder börseneigene E-Mail – ohne E-Mail übernommen.";
                $email = null;
            }

            if ($email && isset($nachEmail[$email])) {
                $this->map('interessenten', $i->id, $nachEmail[$email]);
                $this->hinweise[] = "Interessent V1-ID {$i->id} hatte dieselbe E-Mail wie eine andere Person und wurde mit ihr zusammengeführt.";

                continue;
            }

            $user = $i->user_id ? $benutzer->get($i->user_id) : null;

            $person = Person::create([
                'uuid' => ($i->uuid ?? null) ?: (string) Str::uuid(),
                'vorname' => trim((string) $i->vorname) ?: '–',
                'nachname' => trim((string) $i->nachname) ?: '–',
                'email' => $email,
                'telefon' => trim((string) ($i->telefon ?: $i->handy)) ?: null,
                'kinderhaus_bezug' => match (true) {
                    (int) $i->mitarbeiter === 1 => KinderhausBezug::Mitarbeiter,
                    (int) $i->kinderhaus === 1 => KinderhausBezug::Familie,
                    default => KinderhausBezug::Keiner,
                },
                'email_verified_at' => ($i->email_verified_at ?? null) ?? $i->created_at,
                // Wer in V1 auf der Interessentenliste stand, wird über neue Anmeldestarts informiert.
                'info_mails_erlaubt_at' => $email ? now() : null,
            ]);
            $person->forceFill(['created_at' => $i->created_at, 'password' => null])->saveQuietly();

            if ($user) {
                $this->benutzerUebernehmen($person, $user, $rollen);
                $this->map('users', $user->id, $person->id);
            }

            $this->map('interessenten', $i->id, $person->id);
            if ($email) {
                $nachEmail[$email] = $person->id;
            }
        }

        // Benutzer ohne Interessent (z. B. reine Kassen-Zugänge)
        foreach ($benutzer as $user) {
            if (isset($this->mapping['users'][$user->id]) || ($user->deleted_at ?? null)) {
                continue;
            }
            $email = Str::lower(trim((string) $user->email));
            $personId = $nachEmail[$email] ?? null;
            $person = $personId ? Person::find($personId) : Person::create([
                'vorname' => Str::of($user->name)->before(' ')->toString() ?: $user->name,
                'nachname' => Str::of($user->name)->after(' ')->toString() ?: '–',
                'email' => $email ?: null,
            ]);
            $this->benutzerUebernehmen($person, $user, $rollen);
            $this->map('users', $user->id, $person->id);
        }

        return Person::count();
    }

    private function benutzerUebernehmen(Person $person, object $user, Collection $rollen): void
    {
        // Passwort-Hash (bcrypt) übernehmen: Das Team meldet sich mit dem bisherigen Passwort an.
        $person->forceFill(['password' => $user->password])->saveQuietly();

        if (($user->verwaltung ?? 0) && $rollen->has('orga')) {
            $person->assignRole('orga');
        }
        if (($user->kasse ?? 0) && $rollen->has('kasse')) {
            $person->assignRole('kasse');
        }
    }

    /** V1 enthält "Null-Datumswerte" (0000-00-00) – die werden zu null. */
    private function datum(?string $wert): ?Carbon
    {
        if (blank($wert) || str_starts_with($wert, '0000') || str_starts_with($wert, '-0001')) {
            return null;
        }

        return Carbon::parse($wert);
    }

    private function zeit(Carbon $tag, ?string $uhrzeit): ?Carbon
    {
        return blank($uhrzeit) || str_starts_with($uhrzeit, '00:00') ? null : $tag->copy()->setTimeFromTimeString($uhrzeit);
    }

    private function boersen(): int
    {
        $einstellungen = $this->v1->table('settings')->orderBy('id')->get();

        foreach ($this->v1->table('klamottenboerse')->whereNull('deleted_at')->orderBy('datum')->get() as $k) {
            $tag = Carbon::parse($k->datum)->startOfDay();
            $vortag = $tag->copy()->subDay();
            $ort = filled($k->ort ?? null) ? Ort::firstOrCreate(['name' => $k->ort], ['adresse' => $k->adresse ?? null]) : null;
            $pool = $this->v1->table('vknummern')->where('klamottenboersen_id', $k->id)->whereNull('deleted_at');
            $provision = $einstellungen->firstWhere('datum', $tag->toDateString())?->provision ?? $einstellungen->last()?->provision ?? 25;

            $boerse = Boerse::create([
                'titel' => 'Klamottenbörse '.$tag->format('d.m.Y'),
                'verkaufstag' => $tag->toDateString(),
                'ort_id' => $ort?->id,
                'status' => $tag->lt(today()) ? BoerseStatus::Abgeschlossen : BoerseStatus::Planung,
                'anmeldung_kinderhaus_ab' => $this->datum($k->anmeldungKinderhaus)?->startOfDay(),
                'anmeldung_ab' => $this->datum($k->anmeldung)?->startOfDay(),
                'anlieferung_beginn' => $this->zeit($vortag, $k->anlieferung_von),
                'anlieferung_ende' => $this->zeit($vortag, $k->anlieferung_bis),
                'abholung_beginn' => $this->zeit($tag, $k->abholung_von),
                'abholung_ende' => $this->zeit($tag, $k->abholung_bis),
                'max_teile' => $k->maxTeile ?: null,
                'kapazitaet' => max(1, (clone $pool)->count()),
                'nummer_von' => (int) ((clone $pool)->where('vknummer', '>', 0)->min('vknummer') ?: 200),
                'nummer_bis' => (int) ((clone $pool)->where('vknummer', '<', 600)->max('vknummer') ?: 599),
                'provision_promille' => (int) round(((float) $provision) * 10),
                'rundung_cent' => 1, // V1 hat nicht gerundet – alte Abrechnungen bleiben exakt
                'ergebnis_freigegeben' => (bool) ($k->ergebnis_freigabe ?? false),
                'live_erloes_freigegeben' => (bool) ($k->live_verkaufsansicht_freigabe ?? false),
                'belehrung' => Belehrung::ausV1Html($k->belehrung ?? null),
            ])->refresh(); // Standardwerte (z. B. Kinderhaus-Nummer 600) laden
            $boerse->forceFill(['created_at' => $k->created_at])->saveQuietly();
            $this->map('klamottenboerse', $k->id, $boerse->id);

            Teilnahme::create([
                'boerse_id' => $boerse->id, 'nummer' => $boerse->kinderhaus_nummer, 'status' => TeilnahmeStatus::Zugeteilt,
                'ist_kinderhaus' => true, 'spendenfrei' => true, 'quelle' => 'automatisch',
            ]);
        }

        return Boerse::count();
    }

    private function teilnahmen(): int
    {
        $mitVerkauf = $this->v1->table('verkaufteartikel')->whereNotNull('klamottenboerse_id')
            ->selectRaw('klamottenboerse_id, vknummer')->distinct()->get()
            ->map(fn ($z) => $z->klamottenboerse_id.'-'.$z->vknummer)->flip();
        // Für ältere Börsen sind in V1 keine Verkäufe mehr gespeichert – dort gilt jede Vergabe als Teilnahme.
        $mitVerkaufsdaten = $this->v1->table('verkaufteartikel')->whereNotNull('klamottenboerse_id')->distinct()->pluck('klamottenboerse_id')->flip();

        $nummern = $this->v1->table('vknummern')->whereNull('deleted_at')->orderBy('klamottenboersen_id')->orderBy('vknummer')->get();

        foreach ($nummern as $n) {
            $boerseId = $this->ziel('klamottenboerse', $n->klamottenboersen_id);
            if (! $boerseId) {
                continue;
            }
            $boerse = Boerse::find($boerseId);

            if ((int) $n->vknummer === $boerse->kinderhaus_nummer) {
                $this->map('vknummern', $n->id, $boerse->kinderhausTeilnahme->id);

                continue;
            }

            $doppelt = Teilnahme::query()->where('boerse_id', $boerseId)->where('nummer', (int) $n->vknummer)->first();
            if ($n->vergeben_an && $doppelt) {
                $this->hinweise[] = "Nummer {$n->vknummer} war bei der Börse {$boerse->titel} in V1 doppelt vergeben – nur die erste Vergabe übernommen.";
                $this->map('vknummern', $n->id, $doppelt->id);

                continue;
            }

            if ($n->vergeben_an) {
                $personId = $this->ziel('interessenten', $n->vergeben_an);
                $verkauft = $mitVerkauf->has($n->klamottenboersen_id.'-'.$n->vknummer);
                $status = match (true) {
                    $verkauft => $boerse->status === BoerseStatus::Abgeschlossen ? TeilnahmeStatus::Ausgezahlt : TeilnahmeStatus::Abgerechnet,
                    $boerse->verkaufstag->isPast() && ! $mitVerkaufsdaten->has($n->klamottenboersen_id) => TeilnahmeStatus::Ausgezahlt,
                    $boerse->verkaufstag->isPast() => TeilnahmeStatus::Abgesagt, // nichts verkauft bzw. nicht erschienen
                    default => TeilnahmeStatus::Zugeteilt,
                };

                if ($personId && Teilnahme::query()->where('boerse_id', $boerseId)->where('person_id', $personId)->exists()) {
                    $this->hinweise[] = "Person mit mehreren Nummern bei Börse {$boerse->titel} – weitere Nummer {$n->vknummer} ohne Personenbezug übernommen.";
                    $personId = null;
                }

                $teilnahme = Teilnahme::create([
                    'boerse_id' => $boerseId,
                    'person_id' => $personId,
                    'nummer' => $status === TeilnahmeStatus::Abgesagt ? null : (int) $n->vknummer,
                    'status' => $status,
                    'quelle' => 'v1',
                    'zugeteilt_at' => $n->updated_at ?? $n->created_at,
                ]);
                $this->map('vknummern', $n->id, $teilnahme->id);
            }
        }

        $this->reservierungen();

        // Warteliste (in V1 börsenübergreifend) → Warteliste der nächsten offenen Börse
        $offen = Boerse::query()->where('status', '!=', BoerseStatus::Abgeschlossen->value)->orderBy('verkaufstag')->first();
        if ($offen) {
            $position = 0;
            foreach ($this->v1->table('warteliste')->orderBy('created_at')->get() as $w) {
                $personId = $this->ziel('interessenten', $w->interessenten_id);
                if (! $personId || Teilnahme::query()->where('boerse_id', $offen->id)->where('person_id', $personId)->exists()) {
                    continue;
                }
                Teilnahme::create([
                    'boerse_id' => $offen->id, 'person_id' => $personId, 'status' => TeilnahmeStatus::Warteliste,
                    'wartelisten_position' => ++$position, 'quelle' => 'v1', 'angemeldet_at' => $w->created_at,
                ]);
            }
        }

        return Teilnahme::count();
    }

    /**
     * In V1 sind je Börse rund 30 Nummern fest für bestimmte Verkäufer reserviert.
     * Die Reservierungen der letzten Börse werden dauerhaft übernommen; das Team prüft sie danach.
     */
    private function reservierungen(): void
    {
        $letzte = $this->v1->table('klamottenboerse')->whereNull('deleted_at')->orderByDesc('datum')->value('id');

        $this->v1->table('vknummern')->where('klamottenboersen_id', $letzte)->whereNull('deleted_at')
            ->whereNotNull('reserviert_fuer')->orderBy('vknummer')->get()
            ->unique('vknummer')
            ->each(function ($n) {
                $personId = $this->ziel('interessenten', $n->reserviert_fuer);
                if ($personId && (int) $n->vknummer !== 600) {
                    Nummernreservierung::create([
                        'person_id' => $personId, 'nummer' => (int) $n->vknummer,
                        'grund' => 'aus V1 übernommen – bitte prüfen',
                    ]);
                }
            });
    }

    /** Ordnet alte Verkäufe ohne Börsen-ID über das Datum der passenden Börse zu. */
    private function boerseFuerVerkauf(object $verkauf): ?int
    {
        if ($verkauf->klamottenboerse_id) {
            return $this->ziel('klamottenboerse', $verkauf->klamottenboerse_id);
        }

        $tag = Carbon::parse($verkauf->created_at);

        return Boerse::query()->whereBetween('verkaufstag', [$tag->copy()->subDays(2)->toDateString(), $tag->copy()->addDay()->toDateString()])->value('id');
    }

    private function verkaeufe(): int
    {
        $teilnahmen = Teilnahme::query()->whereNotNull('nummer')->get(['id', 'boerse_id', 'nummer'])
            ->keyBy(fn ($t) => $t->boerse_id.'-'.$t->nummer);
        $jetzt = now();
        $anzahl = 0;

        $this->v1->table('verkaeufe')->orderBy('id')->chunk(500, function ($verkaeufe) use (&$teilnahmen, $jetzt, &$anzahl) {
            $positionen = $this->v1->table('verkaufteartikel')->whereIn('verkauf', $verkaeufe->pluck('id'))->get()->groupBy('verkauf');

            foreach ($verkaeufe as $v) {
                $boerseId = $this->boerseFuerVerkauf($v);
                if (! $boerseId) {
                    $this->hinweise[] = "Verkauf {$v->id} vom {$v->created_at} ließ sich keiner Börse zuordnen und wurde nicht übernommen.";

                    continue;
                }

                $bonId = DB::table('bons')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'boerse_id' => $boerseId,
                    'summe_cent' => (int) round($v->summe * 100),
                    'zahlart' => 'bar',
                    'erstellt_am_geraet' => $v->created_at,
                    'created_at' => $v->created_at ?? $jetzt,
                    'updated_at' => $jetzt,
                ]);

                $zeilen = [];
                foreach ($positionen->get($v->id, collect()) as $p) {
                    $schluessel = $boerseId.'-'.$p->vknummer;
                    if (! $teilnahmen->has($schluessel)) {
                        // In V1 verkauft, aber Nummer nicht (mehr) vergeben – Umsatz trotzdem erhalten.
                        $teilnahmen->put($schluessel, Teilnahme::create([
                            'boerse_id' => $boerseId, 'nummer' => (int) $p->vknummer,
                            'status' => TeilnahmeStatus::Ausgezahlt, 'quelle' => 'v1-ohne-vergabe',
                        ]));
                        $this->hinweise[] = 'Verkäufe zu nicht vergebenen Nummern wurden ohne Personenbezug übernommen.';
                    }
                    $zeilen[] = [
                        'bon_id' => $bonId,
                        'teilnahme_id' => $teilnahmen->get($schluessel)->id,
                        'artikelnummer' => (int) $p->artikelnummer,
                        'preis_cent' => (int) round($p->betrag * 100),
                        'created_at' => $v->created_at ?? $jetzt,
                        'updated_at' => $jetzt,
                    ];
                }
                if ($zeilen) {
                    DB::table('bonpositionen')->insert($zeilen);
                }
                $anzahl++;
            }
        });

        return $anzahl;
    }

    private function artikelUndKisten(): int
    {
        $anzahl = 0;

        if ($this->v1->getSchemaBuilder()->hasTable('verkaufsartikel')) {
            foreach ($this->v1->table('verkaufsartikel')->whereNull('deleted_at')->get() as $a) {
                $teilnahmeId = $this->ziel('vknummern', $a->vknummer_id);
                if (! $teilnahmeId) {
                    continue;
                }
                DB::table('artikel')->insert([
                    'teilnahme_id' => $teilnahmeId, 'laufnummer' => $a->artikelnummer,
                    'beschreibung' => $a->beschreibung, 'groesse' => $a->groesse,
                    'preis_cent' => (int) round($a->preis * 100),
                    'created_at' => $a->created_at, 'updated_at' => $a->updated_at,
                ]);
                $anzahl++;
            }
        }

        if ($this->v1->getSchemaBuilder()->hasTable('kisten')) {
            foreach ($this->v1->table('kisten')->whereNull('deleted_at')->get() as $k) {
                $teilnahmeId = $this->ziel('vknummern', $k->vknummer_id);
                if (! $teilnahmeId) {
                    continue;
                }
                DB::table('kisten')->insert([
                    'teilnahme_id' => $teilnahmeId, 'kistennummer' => $k->kistennummer, 'qr_token' => $k->qr_token,
                    'angenommen_at' => $k->abgegeben_at, 'ausgegeben_at' => $k->abgeholt_at, 'bemerkung' => $k->bemerkung,
                    'created_at' => $k->created_at, 'updated_at' => $k->updated_at,
                ]);
                $anzahl++;
            }
        }

        return $anzahl;
    }

    /** V1 hat je Helfer-Platz einen Termin – gleiche Zeiten werden zu einer Schicht mit Soll-Anzahl. */
    private function schichten(): int
    {
        $helfer = $this->v1->table('helfer')->get()->keyBy('id');
        $termine = $this->v1->table('appointments')->whereNull('deleted_at')->get()
            ->groupBy(fn ($t) => implode('|', [$t->klamottenboerse_id, $t->bereich ?? '', $t->beschreibung, $t->date_start, $t->date_end]));

        foreach ($termine as $gruppe) {
            $erster = $gruppe->first();
            $boerseId = $this->ziel('klamottenboerse', $erster->klamottenboerse_id);
            if (! $boerseId) {
                continue;
            }

            $schicht = Schicht::create([
                'boerse_id' => $boerseId,
                'bereich' => $erster->bereich ?: Str::limit($erster->beschreibung, 60, ''),
                'beschreibung' => $erster->bereich ? $erster->beschreibung : null,
                'beginn' => $erster->date_start,
                'ende' => $erster->date_end,
                'soll' => $gruppe->count(),
            ]);

            foreach ($gruppe->whereNotNull('helfer_id') as $termin) {
                $h = $helfer->get($termin->helfer_id);
                if (! $h) {
                    continue;
                }
                $person = $this->helferPerson($h);
                $schicht->einteilungen()->firstOrCreate(['person_id' => $person->id], [
                    'status' => EinteilungStatus::Zugesagt, 'quelle' => 'v1', 'erinnert_at' => $termin->erinnerung_versendet_at ?? null,
                ]);
            }
        }

        return Schicht::count();
    }

    private function helferPerson(object $helfer): Person
    {
        $email = Str::lower(trim((string) $helfer->mail)) ?: null;
        if ($email && ($person = Person::query()->where('email', $email)->first())) {
            return $person;
        }

        $teile = preg_split('/\s+/', trim((string) $helfer->name)) ?: ['–'];
        $nachname = count($teile) > 1 ? array_pop($teile) : '–';

        return Person::create([
            'vorname' => implode(' ', $teile) ?: '–',
            'nachname' => $nachname,
            'email' => $email && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null,
            'telefon' => trim((string) $helfer->telefon) ?: null,
        ]);
    }

    private function notizen(): int
    {
        $anzahl = 0;
        foreach ($this->v1->table('notizen')->whereNotNull('notiz')->where('notiz', '!=', '')->get() as $n) {
            $personId = $this->ziel('interessenten', $n->interessenten_id);
            if ($personId) {
                DB::table('notizen')->insert([
                    'notizbar_type' => (new Person)->getMorphClass(), 'notizbar_id' => $personId,
                    'text' => $n->notiz, 'created_at' => $n->created_at, 'updated_at' => $n->updated_at,
                ]);
                $anzahl++;
            }
        }
        foreach ($this->v1->table('vknummern_kommentar')->get() as $k) {
            $teilnahmeId = $this->ziel('vknummern', $k->vknummer);
            if ($teilnahmeId) {
                DB::table('notizen')->insert([
                    'notizbar_type' => (new Teilnahme)->getMorphClass(), 'notizbar_id' => $teilnahmeId,
                    'text' => $k->kommentar, 'created_at' => $k->created_at, 'updated_at' => $k->updated_at,
                ]);
                $anzahl++;
            }
        }

        return $anzahl;
    }

    /** Abrechnung je Teilnahme aus den übernommenen Verkäufen; abgeschlossene Börsen gelten als ausgezahlt. */
    private function abrechnungen(): int
    {
        $anzahl = 0;

        foreach (Boerse::query()->whereHas('bons')->get() as $boerse) {
            $umsaetze = DB::table('bonpositionen')->join('bons', 'bons.id', '=', 'bonpositionen.bon_id')
                ->where('bons.boerse_id', $boerse->id)
                ->groupBy('bonpositionen.teilnahme_id')
                ->selectRaw('bonpositionen.teilnahme_id, sum(preis_cent) as umsatz, count(*) as anzahl')
                ->get();
            $teilnahmen = Teilnahme::query()->whereKey($umsaetze->pluck('teilnahme_id'))->get()->keyBy('id');
            $ausgezahlt = $boerse->status === BoerseStatus::Abgeschlossen
                ? ($boerse->abholung_ende ?? $boerse->verkaufstag->copy()->setTime(18, 0))
                : null;

            foreach ($umsaetze as $zeile) {
                $teilnahme = $teilnahmen->get($zeile->teilnahme_id);
                $betraege = AbrechnungBerechnen::rechnen((int) $zeile->umsatz, $boerse, $teilnahme->spendenfrei);
                DB::table('abrechnungen')->insert([
                    'teilnahme_id' => $teilnahme->id,
                    'umsatz_cent' => $betraege['umsatz'],
                    'spende_cent' => $betraege['spende'],
                    'auszahlung_cent' => $betraege['auszahlung'],
                    'verkaufte_artikel' => (int) $zeile->anzahl,
                    'berechnet_at' => now(),
                    'ausgezahlt_at' => $ausgezahlt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $anzahl++;
            }

            $boerse->teilnahmen()->whereKey($teilnahmen->keys())->where('status', '!=', TeilnahmeStatus::Abgesagt->value)
                ->update(['status' => $ausgezahlt ? TeilnahmeStatus::Ausgezahlt : TeilnahmeStatus::Abgerechnet]);
        }

        $this->hinweise[] = 'Abrechnungen wurden neu berechnet. Verkäufe der Kinderhaus-Nummer sind jetzt spendenfrei; ältere Börsen bleiben ungerundet.';

        return $anzahl;
    }

    private function mappingSpeichern(): void
    {
        foreach ($this->mapping as $tabelle => $zuordnung) {
            foreach (array_chunk($zuordnung, 500, true) as $teil) {
                DB::table('v1_mapping')->insert(collect($teil)->map(fn ($v2, $v1) => ['tabelle' => $tabelle, 'v1_id' => $v1, 'v2_id' => $v2])->values()->all());
            }
        }
    }

    /** Vergleicht je Börse verkaufte Artikel und Umsatz zwischen V1 und V2 – auf den Cent. */
    private function pruefsummen(): bool
    {
        $v1 = [];
        foreach ($this->v1->table('verkaeufe')->get(['id', 'klamottenboerse_id', 'created_at']) as $verkauf) {
            $boerseId = $this->boerseFuerVerkauf($verkauf);
            if ($boerseId) {
                $v1[$verkauf->id] = $boerseId;
            }
        }

        $summenV1 = [];
        $this->v1->table('verkaufteartikel')->orderBy('id')->chunk(5000, function ($zeilen) use ($v1, &$summenV1) {
            foreach ($zeilen as $z) {
                $boerseId = $v1[$z->verkauf] ?? null;
                if (! $boerseId) {
                    continue;
                }
                $summenV1[$boerseId]['artikel'] = ($summenV1[$boerseId]['artikel'] ?? 0) + 1;
                $summenV1[$boerseId]['cent'] = ($summenV1[$boerseId]['cent'] ?? 0) + (int) round($z->betrag * 100);
            }
        });

        $summenV2 = DB::table('bonpositionen')->join('bons', 'bons.id', '=', 'bonpositionen.bon_id')
            ->groupBy('bons.boerse_id')->selectRaw('bons.boerse_id, count(*) as artikel, sum(preis_cent) as cent')
            ->get()->keyBy('boerse_id');

        $ok = true;
        $zeilen = [];
        foreach (Boerse::query()->orderBy('verkaufstag')->get() as $boerse) {
            $a1 = $summenV1[$boerse->id]['artikel'] ?? 0;
            $c1 = $summenV1[$boerse->id]['cent'] ?? 0;
            $a2 = (int) ($summenV2[$boerse->id]->artikel ?? 0);
            $c2 = (int) ($summenV2[$boerse->id]->cent ?? 0);
            $gleich = $a1 === $a2 && $c1 === $c2;
            $ok = $ok && $gleich;
            $zeilen[] = [$boerse->verkaufstag->format('d.m.Y'), $a1, $a2, number_format($c1 / 100, 2, ',', '.'), number_format($c2 / 100, 2, ',', '.'), $gleich ? 'OK' : 'ABWEICHUNG'];
        }

        $this->newLine();
        $this->table(['Börse', 'Artikel V1', 'Artikel V2', 'Umsatz V1 €', 'Umsatz V2 €', 'Prüfung'], $zeilen);

        return $ok;
    }
}
