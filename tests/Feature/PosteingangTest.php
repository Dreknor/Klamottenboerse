<?php

use App\Domain\Kommunikation\Mailinhalt;
use App\Models\Person;
use App\Models\Posteingang;

function eingangsmail(array $werte = []): Posteingang
{
    static $uid = 0;

    return Posteingang::create($werte + [
        'uid' => ++$uid, 'von_email' => 'anna@example.de', 'betreff' => 'Frage', 'text' => 'Hallo', 'empfangen_at' => now(),
    ]);
}

function alsTeamAdmin($test)
{
    return $test->actingAs(tap(Person::factory()->create(['password' => 'geheim-geheim']))->assignRole(['orga', 'admin']))
        ->withSession(['login_art' => 'passwort']);
}

it('macht aus HTML-Mails lesbaren Text mit Absätzen statt CSS-Brei', function () {
    $text = Mailinhalt::textAusHtml('<html><head><style>p { color: red; }</style></head><body><p>Hallo Team,</p><p>ich habe eine Frage:</p><ul><li>Erstens</li><li>Zweitens</li></ul><p>Mehr unter <a href="https://example.org/info">dieser Seite</a>.</p></body></html>');

    expect($text)->not->toContain('color')
        ->toContain("Hallo Team,\n\nich habe eine Frage:")
        ->toContain('- Erstens')
        ->toContain('dieser Seite (https://example.org/info)');
});

it('zeigt HTML-Mails abgeschottet und ohne Skripte an', function () {
    $mail = eingangsmail(['html' => '<p>Liebe Grüße</p><script>alert(1)</script><img src="https://tracker.example/pixel.gif">', 'ordner' => 'mailpit']);

    alsTeamAdmin($this)->get(route('admin.posteingang.show', $mail))
        ->assertOk()
        ->assertSee('sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"', false)
        ->assertSee('Bilder anzeigen')
        ->assertDontSee('alert(1)');

    expect(Mailinhalt::htmlDokument($mail->html))->toContain('img-src data:;')
        ->and(Mailinhalt::htmlDokument($mail->html, bilderLaden: true))->toContain('img-src data: https:');
});

it('zeigt Text-Mails mit klickbaren Links und abgesetzten Zitaten', function () {
    $html = (string) Mailinhalt::textAlsHtml("Siehe https://example.org/a?b=1&c=2.\n> altes <b>Zitat</b>");

    expect($html)->toContain('<a href="https://example.org/a?b=1&amp;c=2" target="_blank"')
        ->toContain('</a>.')
        ->toContain('<div class="mail-zitat">altes &lt;b&gt;Zitat&lt;/b&gt;</div>');
});

it('markiert alle offenen Mails auf einmal als erledigt', function () {
    $offen = [eingangsmail(), eingangsmail(['betreff' => 'Spende'])];
    $spam = eingangsmail(['spam' => true]);

    alsTeamAdmin($this)->get(route('admin.posteingang.index'))->assertSee('Alle als erledigt markieren (2)');

    alsTeamAdmin($this)->post(route('admin.posteingang.alle-erledigt'), ['suche' => 'Spende'])
        ->assertSessionHas('erfolg', '1 Mail als erledigt markiert.');
    alsTeamAdmin($this)->post(route('admin.posteingang.alle-erledigt'))
        ->assertSessionHas('erfolg', '1 Mail als erledigt markiert.');

    expect(Posteingang::query()->offen()->count())->toBe(0)
        ->and($offen[0]->refresh()->gelesen_at)->not->toBeNull()
        ->and($spam->refresh()->erledigt_at)->toBeNull();
});
