<?php

namespace Tests\Feature;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\User;
use App\Model\VKnummer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InaktiveVerkaeuferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 3)->startOfDay());
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 1,
        ]);
    }

    private function boerse(string $datum): Klamottenboerse
    {
        return Klamottenboerse::create([
            'datum' => $datum,
            'anmeldung' => $datum,
            'anmeldungKinderhaus' => $datum,
            'anlieferung_von' => '08:00:00',
            'anlieferung_bis' => '10:00:00',
            'abholung_von' => '18:00:00',
            'abholung_bis' => '19:00:00',
            'maxTeile' => 100,
        ]);
    }

    private function person(string $name): Interessenten
    {
        return Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => $name,
            'mail' => Str::random(8).'@example.test',
        ]);
    }

    private function teilnahme(Interessenten $person, Klamottenboerse $boerse, int $nummer): VKnummer
    {
        return VKnummer::create([
            'vknummer' => $nummer,
            'klamottenboersen_id' => $boerse->id,
            'vergeben_an' => $person->id,
        ]);
    }

    public function test_overview_uses_last_past_participation_and_shows_all_current_reservations(): void
    {
        $alt = $this->boerse('2023-04-01');
        $grenze = $this->boerse('2024-10-03');
        $neu = $this->boerse('2025-04-01');
        $aktuell = $this->boerse('2026-11-01');
        $inaktiv = $this->person('LangePause');
        $grenzfall = $this->person('Grenzfall');
        $aktiv = $this->person('Aktiv');
        $nie = $this->person('NieTeilgenommen');
        $geloescht = $this->person('Geloescht');
        $this->teilnahme($inaktiv, $alt, 201);
        $this->teilnahme($grenzfall, $grenze, 202);
        $this->teilnahme($aktiv, $alt, 203);
        $this->teilnahme($aktiv, $neu, 203);
        $this->teilnahme($geloescht, $alt, 204);
        $geloescht->delete();
        $this->teilnahme($inaktiv, $aktuell, 201);
        foreach ([301, 302] as $nummer) {
            VKnummer::create([
                'vknummer' => $nummer,
                'klamottenboersen_id' => $aktuell->id,
                'reserviert_fuer' => $inaktiv->id,
            ]);
        }
        VKnummer::create([
            'vknummer' => 399,
            'klamottenboersen_id' => $alt->id,
            'reserviert_fuer' => $grenzfall->id,
        ]);
        VKnummer::create([
            'vknummer' => 400,
            'klamottenboersen_id' => $aktuell->id,
            'reserviert_fuer' => $nie->id,
        ]);

        $response = $this->actingAs($this->admin())->get(route('interessenten.inaktive-verkaeufer'));

        $response->assertOk()
            ->assertSee('LangePause')->assertSee('Grenzfall')
            ->assertDontSee('NieTeilgenommen')->assertDontSee('Geloescht')
            ->assertSee('01.04.2023')->assertSee('03.10.2024')
            ->assertSee('301')->assertSee('302')->assertDontSee('399');
        $response->assertViewHas('verkaeufer', function ($personen) use ($inaktiv, $grenzfall) {
            return $personen->total() === 2
                && $personen->pluck('id')->all() === [$inaktiv->id, $grenzfall->id]
                && $personen[0]->reservierteNummern->count() === 2
                && $personen[1]->reservierteNummern->isEmpty();
        });
    }

    public function test_month_threshold_and_reservation_filters_are_adjustable(): void
    {
        $alt = $this->boerse('2025-01-01');
        $aktuell = $this->boerse('2026-11-01');
        $mit = $this->person('MitReservierung');
        $ohne = $this->person('OhneReservierung');
        $this->teilnahme($mit, $alt, 201);
        $this->teilnahme($ohne, $alt, 202);
        VKnummer::create([
            'vknummer' => 301,
            'klamottenboersen_id' => $aktuell->id,
            'reserviert_fuer' => $mit->id,
        ]);
        VKnummer::create([
            'vknummer' => 302,
            'klamottenboersen_id' => $alt->id,
            'reserviert_fuer' => $ohne->id,
        ]);
        $this->actingAs($this->admin());

        $this->get(route('interessenten.inaktive-verkaeufer'))
            ->assertOk()->assertViewHas('verkaeufer', fn ($personen) => $personen->total() === 0);
        $this->get(route('interessenten.inaktive-verkaeufer', ['monate' => 12, 'reservierung' => 'ja']))
            ->assertOk()->assertSee('MitReservierung')->assertDontSee('OhneReservierung');
        $this->get(route('interessenten.inaktive-verkaeufer', ['monate' => 12, 'reservierung' => 'nein']))
            ->assertOk()->assertSee('OhneReservierung')->assertDontSee('MitReservierung');
    }

    public function test_last_participation_uses_entire_history_regardless_of_selected_period(): void
    {
        $person = $this->person('HistorischeTeilnahme');
        $this->teilnahme($person, $this->boerse('2020-04-01'), 201);
        $this->teilnahme($person, $this->boerse('2022-10-01'), 201);
        $this->actingAs($this->admin());

        foreach ([12, 24, 36] as $monate) {
            $this->get(route('interessenten.inaktive-verkaeufer', ['monate' => $monate]))
                ->assertOk()->assertSee('HistorischeTeilnahme')
                ->assertSee('01.10.2022')->assertDontSee('01.04.2020')
                ->assertViewHas('verkaeufer', fn ($personen) => $personen->total() === 1
                    && $personen[0]->letzte_teilnahme->toDateString() === '2022-10-01');
        }
    }

    public function test_no_events_produces_an_empty_overview(): void
    {
        $this->actingAs($this->admin())->get(route('interessenten.inaktive-verkaeufer'))
            ->assertOk()->assertViewHas('aktuelleBoerse', null)
            ->assertViewHas('verkaeufer', fn ($personen) => $personen->total() === 0);
    }

    public function test_people_without_participation_are_included_only_if_already_created_at_cutoff(): void
    {
        $aktuell = $this->boerse('2026-11-01');
        $alt = $this->person('NieAlt');
        $alt->forceFill(['created_at' => '2023-04-01 12:00:00'])->save();
        $grenze = $this->person('NieGrenze');
        $grenze->forceFill(['created_at' => '2024-10-03 23:59:59'])->save();
        $neuer = $this->person('NieNeuer');
        $neuer->forceFill(['created_at' => '2024-10-04 00:00:00'])->save();
        $unbekannt = $this->person('NieUnbekannt');
        $unbekannt->forceFill(['created_at' => null])->save();
        $geloescht = $this->person('NieGeloescht');
        $geloescht->forceFill(['created_at' => '2023-01-01'])->save();
        $geloescht->delete();
        $this->teilnahme($alt, $aktuell, 201);
        VKnummer::create([
            'vknummer' => 301,
            'klamottenboersen_id' => $aktuell->id,
            'reserviert_fuer' => $alt->id,
        ]);
        $this->actingAs($this->admin());

        $this->get(route('interessenten.inaktive-verkaeufer'))
            ->assertOk()->assertSee('NieAlt')->assertSee('NieGrenze')
            ->assertDontSee('NieNeuer')->assertDontSee('NieUnbekannt')->assertDontSee('NieGeloescht')
            ->assertSee('Noch nie teilgenommen')->assertSee('Angelegt am')
            ->assertSee('01.04.2023')->assertSee('301')
            ->assertViewHas('verkaeufer', fn ($personen) => $personen->total() === 2
                && $personen->every(fn ($person) => $person->letzte_teilnahme === null));
        $this->get(route('interessenten.inaktive-verkaeufer', ['reservierung' => 'ja']))
            ->assertOk()->assertSee('NieAlt')->assertDontSee('NieGrenze');
        $this->get(route('interessenten.inaktive-verkaeufer', ['reservierung' => 'nein']))
            ->assertOk()->assertSee('NieGrenze')->assertDontSee('NieAlt');
        $this->get(route('interessenten.inaktive-verkaeufer', ['monate' => 12]))
            ->assertOk()->assertSee('NieNeuer');
    }

    public function test_old_people_without_participation_are_shown_even_without_events(): void
    {
        $person = $this->person('OhneBoerse');
        $person->forceFill(['created_at' => '2023-01-01'])->save();

        $this->actingAs($this->admin())->get(route('interessenten.inaktive-verkaeufer'))
            ->assertOk()->assertSee('OhneBoerse')->assertSee('Noch nie teilgenommen')
            ->assertViewHas('aktuelleBoerse', null);
    }

    public function test_deleted_events_do_not_count_as_participation(): void
    {
        $alt = $this->boerse('2023-01-01');
        $neu = $this->boerse('2025-01-01');
        $person = $this->person('Historie');
        $this->teilnahme($person, $alt, 201);
        $this->teilnahme($person, $neu, 201);
        $neu->delete();

        $this->actingAs($this->admin())->get(route('interessenten.inaktive-verkaeufer'))
            ->assertOk()->assertSee('Historie')->assertSee('01.01.2023');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->actingAs($this->admin());
        foreach ([0, -1, 601, 'abc', '1.5', ''] as $monate) {
            $this->get(route('interessenten.inaktive-verkaeufer', ['monate' => $monate]))
                ->assertSessionHasErrors('monate');
        }
        $this->get(route('interessenten.inaktive-verkaeufer', ['reservierung' => 'ungueltig']))
            ->assertSessionHasErrors('reservierung');
    }

    public function test_pagination_preserves_filters(): void
    {
        $alt = $this->boerse('2023-01-01');
        $this->boerse('2026-11-01');
        for ($i = 1; $i <= 51; $i++) {
            $this->teilnahme($this->person(sprintf('Person%03d', $i)), $alt, 200 + $i);
        }
        $this->actingAs($this->admin());
        $params = ['monate' => 12, 'reservierung' => 'nein'];

        $this->get(route('interessenten.inaktive-verkaeufer', $params))
            ->assertOk()->assertViewHas('verkaeufer', function ($personen) {
                return $personen->total() === 51 && $personen->count() === 50
                    && str_contains($personen->nextPageUrl(), 'monate=12')
                    && str_contains($personen->nextPageUrl(), 'reservierung=nein');
            });
        $this->get(route('interessenten.inaktive-verkaeufer', $params + ['page' => 2]))
            ->assertOk()->assertSee('Person051')->assertDontSee('Person001');
    }

    public function test_overview_requires_administration_access(): void
    {
        $this->get(route('interessenten.inaktive-verkaeufer'))->assertRedirect('/login');
        $user = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 0,
            'kasse' => 1,
        ]);
        $this->actingAs($user)->get(route('interessenten.inaktive-verkaeufer'))
            ->assertRedirect(url('/'))->assertSessionHas('danger');
    }
}
