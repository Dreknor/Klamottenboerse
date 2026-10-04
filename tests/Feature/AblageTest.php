<?php

use App\Domain\Ablage\NextcloudImport;
use App\Models\Aufgabe;
use App\Models\Checklistenvorlage;
use App\Models\Ordner;
use App\Models\Person;
use App\Models\Protokoll;
use App\Models\Schicht;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

function ablageOrga($test)
{
    return $test->actingAs(tap(Person::factory()->create())->assignRole('orga'))->withSession(['login_art' => 'passwort']);
}

it('legt Ordner an, lädt Dateien hoch und liefert sie aus', function () {
    $als = ablageOrga($this);

    $als->post(route('admin.ablage.anlegen'), ['name' => 'Lagepläne'])->assertRedirect();
    $ordner = Ordner::sole();
    $als->post(route('admin.ablage.hochladen', $ordner), ['dateien' => [
        UploadedFile::fake()->image('saal.jpg', 1200, 800),
        UploadedFile::fake()->create('aufbau.pdf', 100, 'application/pdf'),
    ]])->assertSessionHas('erfolg');

    expect($ordner->getMedia('dateien'))->toHaveCount(2);
    $bild = $ordner->getMedia('dateien')->firstWhere('file_name', 'saal.jpg');

    $als->get(route('admin.ablage.ordner', $ordner))->assertOk()->assertSee('aufbau.pdf');
    $als->get(route('admin.ablage.datei', [$bild, 'vorschau' => 1]))->assertOk();
    $als->delete(route('admin.ablage.ordner.loeschen', $ordner))->assertSessionHas('fehler'); // nicht leer
});

it('zeigt freigegebene Ordner nur Helfern mit Schicht', function () {
    $boerse = neueBoerse();
    $ordner = Ordner::create(['name' => 'Für Helfer', 'fuer_helfer' => true]);
    $datei = $ordner->addMedia(UploadedFile::fake()->create('plan.pdf', 10, 'application/pdf'))->toMediaCollection('dateien');

    $helfer = Person::factory()->create();
    Schicht::create(['boerse_id' => $boerse->id, 'bereich' => 'Kasse', 'beginn' => now()->addDays(5), 'ende' => now()->addDays(5)->addHour()])
        ->einteilungen()->create(['person_id' => $helfer->id]);
    $verkaeufer = Person::factory()->create();

    $this->actingAs($helfer)->get(route('portal.index'))->assertSee('Unterlagen für Helfer')->assertSee('plan.pdf');
    $this->actingAs($helfer)->get(route('portal.unterlage', $datei))->assertOk();
    $this->actingAs($verkaeufer)->get(route('portal.unterlage', $datei))->assertNotFound();

    $ordner->update(['fuer_helfer' => false]);
    $this->actingAs($helfer)->get(route('portal.unterlage', $datei))->assertNotFound();
});

it('macht aus offenen Protokollpunkten Aufgaben', function () {
    $als = ablageOrga($this);
    $als->post(route('admin.protokolle.store'), [
        'titel' => 'Orga-Treffen', 'datum' => today()->toDateString(),
        'inhalt' => "## Themen\n\nBeschluss: Kuchenspende anfragen\n\n- [ ] Saal reservieren\n- [ ] Plakate drucken",
    ])->assertRedirect();
    $protokoll = Protokoll::sole();

    expect($protokoll->beschluesse())->toBe(['Kuchenspende anfragen'])
        ->and($protokoll->offenePunkte())->toHaveCount(2);

    $als->post(route('admin.protokolle.aufgabe', $protokoll), ['zeile' => array_key_first($protokoll->offenePunkte())])->assertSessionHas('erfolg');

    expect(Aufgabe::where('titel', 'Saal reservieren')->exists())->toBeTrue()
        ->and($protokoll->fresh()->offenePunkte())->toBe([5 => 'Plakate drucken']);
    $als->get(route('admin.protokolle.index', ['suche' => 'Plakate']))->assertSee('Orga-Treffen');
});

it('wendet eine Stations-Vorlage auf die aktuelle Börse an', function () {
    $boerse = neueBoerse();
    $vorlage = Checklistenvorlage::create(['name' => 'Aufbau Kasse']);
    $vorlage->eintraege()->create(['titel' => 'Scanner laden', 'versatz_tage' => -1]);

    ablageOrga($this)->withSession(['boerse_id' => $boerse->id])
        ->post(route('admin.checklistenvorlagen.anwenden', $vorlage))->assertRedirect(route('admin.aufgaben.index'));

    expect($boerse->aufgaben()->where('titel', 'Scanner laden')->value('faellig_am')->toDateString())
        ->toBe($boerse->verkaufstag->copy()->subDay()->toDateString());
});

it('übernimmt Bilder und Protokolle aus der Nextcloud – ohne Doppelungen', function () {
    $boerse = neueBoerse(['verkaufstag' => '2026-03-21']);
    $basis = 'https://cloud.example.org/remote.php/dav/files/team';
    $multistatus = fn (array $eintraege) => '<?xml version="1.0"?><d:multistatus xmlns:d="DAV:">'.collect($eintraege)->map(fn ($e) => '<d:response><d:href>/remote.php/dav/files/team/'.$e[0].'</d:href><d:propstat><d:prop>'
        .($e[1] ? '<d:resourcetype><d:collection/></d:resourcetype>' : '<d:resourcetype/>')
        .'<d:getlastmodified>Mon, 02 Mar 2026 10:00:00 GMT</d:getlastmodified></d:prop></d:propstat></d:response>')->implode('').'</d:multistatus>';

    Http::fake(function ($request) use ($basis, $multistatus) {
        $pfad = rawurldecode(substr($request->url(), strlen($basis) + 1));

        return match (true) {
            $request->method() === 'PROPFIND' && $pfad === 'Fotos' => Http::response($multistatus([['Fotos/', true], ['Fotos/Herbst%202025/', true], ['Fotos/titel.jpg', false]]), 207),
            $request->method() === 'PROPFIND' && $pfad === 'Fotos/Herbst 2025' => Http::response($multistatus([['Fotos/Herbst%202025/', true], ['Fotos/Herbst%202025/saal.png', false], ['Fotos/Herbst%202025/notiz.txt', false]]), 207),
            $request->method() === 'PROPFIND' && $pfad === 'Protokolle' => Http::response($multistatus([['Protokolle/', true], ['Protokolle/2026-02-10%20Orga.md', false], ['Protokolle/Treffen.docx', false]]), 207),
            str_ends_with($pfad, '.md') => Http::response("## Themen\n\n- [ ] Flyer verteilen"),
            str_ends_with($pfad, '.jpg'), str_ends_with($pfad, '.png') => Http::response(UploadedFile::fake()->image('x.jpg')->getContent()),
            default => Http::response('Inhalt'),
        };
    });

    $import = new NextcloudImport($basis, 'team', 'geheim');
    $import->bilder('Fotos');
    $import->protokolle('Protokolle');

    expect($import->zaehler)->toBe(['bilder' => 2, 'protokolle' => 1, 'dateien' => 1, 'uebersprungen' => 0]);
    $unterordner = Ordner::where('name', 'Herbst 2025')->sole();
    expect($unterordner->parent->name)->toBe('Bilder aus der Nextcloud')
        ->and($unterordner->getMedia('dateien')->pluck('file_name')->all())->toBe(['saal.png']);

    $md = Protokoll::where('titel', '2026 02 10 Orga')->sole();
    expect($md->datum->toDateString())->toBe('2026-02-10')
        ->and($md->boerse_id)->toBe($boerse->id)
        ->and($md->offenePunkte())->toBe([2 => 'Flyer verteilen']);

    // zweiter Lauf: nichts doppelt
    $zweiter = new NextcloudImport($basis, 'team', 'geheim');
    $zweiter->bilder('Fotos');
    $zweiter->protokolle('Protokolle');
    expect($zweiter->zaehler['uebersprungen'])->toBe(4)
        ->and(Protokoll::count())->toBe(2);
});
