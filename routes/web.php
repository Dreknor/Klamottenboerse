<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Kasse\KasseController;
use App\Http\Controllers\Portal;
use App\Http\Controllers\Public;
use App\Http\Controllers\Tablet\TabletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Öffentlich (ohne Login)
|--------------------------------------------------------------------------
*/
Route::get('/', [Public\StartController::class, 'index'])->name('start');
Route::get('/impressum', [Public\SeiteController::class, 'impressum'])->name('impressum');
Route::get('/datenschutz', [Public\SeiteController::class, 'datenschutz'])->name('datenschutz');

Route::get('/anmeldung', [Public\AnmeldungController::class, 'create'])->name('anmeldung.create');
Route::post('/anmeldung', [Public\AnmeldungController::class, 'store'])->middleware('throttle:6,1')->name('anmeldung.store');
Route::get('/anmeldung/{person:uuid}/bestaetigen', [Public\AnmeldungController::class, 'bestaetigen'])
    ->middleware('signed')->name('anmeldung.bestaetigen');

Route::middleware('signed')->group(function () {
    Route::get('/teilnahme/{teilnahme}/absage', [Public\TeilnahmeLinkController::class, 'absage'])->name('teilnahme.absage');
    Route::post('/teilnahme/{teilnahme}/absage', [Public\TeilnahmeLinkController::class, 'absagen']);
    Route::get('/teilnahme/{teilnahme}/angebot', [Public\TeilnahmeLinkController::class, 'angebot'])->name('teilnahme.angebot');
    Route::post('/teilnahme/{teilnahme}/angebot', [Public\TeilnahmeLinkController::class, 'annehmen']);
    Route::get('/helfen/absage/{einteilung}', [Public\HelferController::class, 'absage'])->name('helfer.absage');
    Route::post('/helfen/absage/{einteilung}', [Public\HelferController::class, 'absagen']);
    Route::get('/info-mails/{person:uuid}/abbestellen', [Public\InfoMailsController::class, 'abbestellen'])->name('infomails.abbestellen');
});

Route::get('/helfen', [Public\HelferController::class, 'index'])->name('helfer.index');
Route::post('/helfen/{schicht}', [Public\HelferController::class, 'eintragen'])->middleware('throttle:10,1')->name('helfer.eintragen');

Route::get('/feedback/{token}', [Public\FeedbackController::class, 'show'])->name('feedback.show');
Route::post('/feedback/{token}', [Public\FeedbackController::class, 'store'])->name('feedback.store');

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

/*
|--------------------------------------------------------------------------
| Verkäufer- und Helferportal (Magic-Link per Mail)
|--------------------------------------------------------------------------
*/
Route::get('/portal/login/{person:uuid}', [Portal\PortalLoginController::class, 'login'])->middleware('signed')->name('portal.login');
Route::get('/portal/link', [Portal\PortalLoginController::class, 'create'])->name('portal.link');
Route::post('/portal/link', [Portal\PortalLoginController::class, 'store'])->middleware('throttle:5,1');

Route::middleware('auth')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [Portal\PortalController::class, 'index'])->name('index');
    Route::post('/artikel', [Portal\ArtikelController::class, 'store'])->name('artikel.store');
    Route::delete('/artikel/{artikel}', [Portal\ArtikelController::class, 'destroy'])->name('artikel.destroy');
    Route::get('/etiketten', [Portal\DruckController::class, 'etiketten'])->name('etiketten');
    Route::get('/kistenzettel', [Portal\DruckController::class, 'kistenzettel'])->name('kistenzettel');
    Route::post('/absage', [Portal\PortalController::class, 'absagen'])->name('absage');
    Route::post('/abmelden', [Portal\PortalLoginController::class, 'destroy'])->name('abmelden');
});

/*
|--------------------------------------------------------------------------
| Kasse und Tablet-Modus (Annahme, Rückpacken, Ausgabe)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'passwort', 'role:admin|orga|kasse'])->prefix('kasse')->name('kasse.')->group(function () {
    Route::get('/', [KasseController::class, 'index'])->name('index');
    Route::get('/daten', [KasseController::class, 'daten'])->name('daten');
    Route::post('/bons', [KasseController::class, 'sync'])->name('sync');
    Route::post('/bons/{bon:uuid}/storno', [KasseController::class, 'storno'])->name('storno');
});

Route::middleware(['auth', 'passwort', 'role:admin|orga|annahme'])->prefix('tablet')->name('tablet.')->group(function () {
    Route::get('/', [TabletController::class, 'index'])->name('index');
    Route::get('/annahme', [TabletController::class, 'annahme'])->name('annahme');
    Route::post('/annahme/{teilnahme}', [TabletController::class, 'annehmen'])->name('annehmen');
    Route::get('/rueckpacken', [TabletController::class, 'rueckpacken'])->name('rueckpacken');
    Route::get('/ausgabe', [TabletController::class, 'ausgabe'])->name('ausgabe');
    Route::post('/ausgabe/{teilnahme}', [TabletController::class, 'auszahlen'])->name('auszahlen');
});

/*
|--------------------------------------------------------------------------
| Orga-Backend
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'orga'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/boerse-wechseln', [Admin\DashboardController::class, 'wechseln'])->name('boerse.wechseln');

    Route::resource('boersen', Admin\BoerseController::class)->except(['show', 'destroy'])->parameters(['boersen' => 'boerse']);
    Route::post('/boersen/{boerse}/abschliessen', [Admin\BoerseController::class, 'abschliessen'])->name('boersen.abschliessen');

    Route::get('/verkaeufer', [Admin\TeilnahmeController::class, 'index'])->name('teilnahmen.index');
    Route::post('/verkaeufer', [Admin\TeilnahmeController::class, 'store'])->name('teilnahmen.store');
    Route::post('/verkaeufer/{teilnahme}/absagen', [Admin\TeilnahmeController::class, 'absagen'])->name('teilnahmen.absagen');
    Route::post('/verkaeufer/{teilnahme}/nummer', [Admin\TeilnahmeController::class, 'nummer'])->name('teilnahmen.nummer');
    Route::post('/verkaeufer/nachruecken', [Admin\TeilnahmeController::class, 'nachruecken'])->name('teilnahmen.nachruecken');

    Route::get('/reservierungen', [Admin\ReservierungController::class, 'index'])->name('reservierungen.index');
    Route::post('/reservierungen', [Admin\ReservierungController::class, 'store'])->name('reservierungen.store');
    Route::delete('/reservierungen/{reservierung}', [Admin\ReservierungController::class, 'destroy'])->name('reservierungen.destroy');

    Route::resource('personen', Admin\PersonController::class)->except(['destroy'])->parameters(['personen' => 'person']);
    Route::post('/personen/{person}/notizen', [Admin\PersonController::class, 'notiz'])->name('personen.notiz');
    Route::post('/personen/{person}/login-link', [Admin\PersonController::class, 'loginLink'])->name('personen.login-link');

    Route::get('/schichten', [Admin\SchichtController::class, 'index'])->name('schichten.index');
    Route::post('/schichten', [Admin\SchichtController::class, 'store'])->name('schichten.store');
    Route::delete('/schichten/{schicht}', [Admin\SchichtController::class, 'destroy'])->name('schichten.destroy');
    Route::post('/schichten/{schicht}/helfer', [Admin\SchichtController::class, 'helferEintragen'])->name('schichten.helfer');
    Route::delete('/einteilungen/{einteilung}', [Admin\SchichtController::class, 'helferEntfernen'])->name('einteilungen.destroy');

    Route::get('/aufgaben', [Admin\AufgabeController::class, 'index'])->name('aufgaben.index');
    Route::post('/aufgaben', [Admin\AufgabeController::class, 'store'])->name('aufgaben.store');
    Route::put('/aufgaben/{aufgabe}', [Admin\AufgabeController::class, 'update'])->name('aufgaben.update');
    Route::post('/aufgaben/{aufgabe}/erledigt', [Admin\AufgabeController::class, 'erledigt'])->name('aufgaben.erledigt');
    Route::delete('/aufgaben/{aufgabe}', [Admin\AufgabeController::class, 'destroy'])->name('aufgaben.destroy');
    Route::get('/checklistenvorlagen', [Admin\ChecklistenvorlageController::class, 'index'])->name('checklistenvorlagen.index');
    Route::post('/checklistenvorlagen/{vorlage}/eintraege', [Admin\ChecklistenvorlageController::class, 'store'])->name('checklistenvorlagen.store');
    Route::delete('/checklistenvorlagen/eintraege/{eintrag}', [Admin\ChecklistenvorlageController::class, 'destroy'])->name('checklistenvorlagen.destroy');

    Route::get('/kalender', [Admin\KalenderController::class, 'index'])->name('kalender.index');
    Route::post('/termine', [Admin\KalenderController::class, 'store'])->name('termine.store');
    Route::delete('/termine/{termin}', [Admin\KalenderController::class, 'destroy'])->name('termine.destroy');

    Route::get('/mailvorlagen', [Admin\MailvorlageController::class, 'index'])->name('mailvorlagen.index');
    Route::get('/mailvorlagen/{mailvorlage}', [Admin\MailvorlageController::class, 'edit'])->name('mailvorlagen.edit');
    Route::put('/mailvorlagen/{mailvorlage}', [Admin\MailvorlageController::class, 'update'])->name('mailvorlagen.update');
    Route::get('/mailplan', [Admin\MailplanController::class, 'index'])->name('mailplan.index');
    Route::post('/mailplan', [Admin\MailplanController::class, 'store'])->name('mailplan.store');
    Route::post('/mailplan/{eintrag}/umschalten', [Admin\MailplanController::class, 'umschalten'])->name('mailplan.umschalten');
    Route::delete('/mailplan/{eintrag}', [Admin\MailplanController::class, 'destroy'])->name('mailplan.destroy');
    Route::get('/postausgang', [Admin\MailplanController::class, 'postausgang'])->name('postausgang.index');
    Route::post('/postausgang/{nachricht}/erneut', [Admin\MailplanController::class, 'erneut'])->name('postausgang.erneut');

    Route::get('/posteingang', [Admin\PosteingangController::class, 'index'])->name('posteingang.index');
    Route::post('/posteingang/abrufen', [Admin\PosteingangController::class, 'abrufen'])->name('posteingang.abrufen');
    Route::get('/posteingang/{mail}', [Admin\PosteingangController::class, 'show'])->name('posteingang.show');
    Route::post('/posteingang/{mail}/antworten', [Admin\PosteingangController::class, 'antworten'])->name('posteingang.antworten');
    Route::post('/posteingang/{mail}/zuordnen', [Admin\PosteingangController::class, 'zuordnen'])->name('posteingang.zuordnen');
    Route::post('/posteingang/{mail}/status', [Admin\PosteingangController::class, 'status'])->name('posteingang.status');

    Route::get('/abrechnung', [Admin\AbrechnungController::class, 'index'])->name('abrechnung.index');
    Route::post('/abrechnung/berechnen', [Admin\AbrechnungController::class, 'berechnen'])->name('abrechnung.berechnen');
    Route::post('/abrechnung/freigeben', [Admin\AbrechnungController::class, 'freigeben'])->name('abrechnung.freigeben');
    Route::get('/abrechnung/auszahlungsplan', [Admin\AbrechnungController::class, 'auszahlungsplan'])->name('abrechnung.auszahlungsplan');
    Route::post('/einnahmen', [Admin\AbrechnungController::class, 'einnahmeSpeichern'])->name('einnahmen.store');
    Route::delete('/einnahmen/{einnahme}', [Admin\AbrechnungController::class, 'einnahmeLoeschen'])->name('einnahmen.destroy');

    Route::get('/statistik', [Admin\StatistikController::class, 'index'])->name('statistik.index');
    Route::get('/feedback', [Admin\FeedbackController::class, 'index'])->name('feedback.index');

    Route::get('/seiten', [Admin\SeiteController::class, 'index'])->name('seiten.index');
    Route::get('/seiten/{seite}', [Admin\SeiteController::class, 'edit'])->name('seiten.edit');
    Route::put('/seiten/{seite}', [Admin\SeiteController::class, 'update'])->name('seiten.update');

    Route::get('/einstellungen', [Admin\EinstellungenController::class, 'edit'])->name('einstellungen.edit');
    Route::put('/einstellungen', [Admin\EinstellungenController::class, 'update'])->name('einstellungen.update');
});
