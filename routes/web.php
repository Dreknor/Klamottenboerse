<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InteressentenController;
use App\Http\Controllers\Kasse\ExportController;
use App\Http\Controllers\Kasse\KasseController;
use App\Http\Controllers\Kasse\OfflineSyncController;
use App\Http\Controllers\Kasse\SettingsController;
use App\Http\Controllers\Kasse\VerlaufController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\SelfServiceDeletionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerkaeuferPortalController;
use App\Http\Controllers\WartelistenAngebotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/helfer', [\App\Http\Controllers\AppointmentController::class, 'index'])->name('helfer');
Route::post('/helfer', [\App\Http\Controllers\AppointmentController::class, 'storeHelfer'])->name('helfer.store');

Route::get('ergebnis', 'ErgebnisController@index')->name('ergebnis');
Route::post('ergebnis', 'ErgebnisController@mail')->name('ergebnis.mail');
Route::get('ergebnis/{uuid}', 'ErgebnisController@show')->name('ergebnis.show');

Auth::routes(['register' => false]);

//Öffentliche Selbstregistrierung für Interessenten (mit E-Mail-Bestätigung & Spamschutz)
Route::group(['middleware' => ['throttle:6,1']], function () {
    Route::get('/registrieren', [PublicRegistrationController::class, 'create'])->name('registrierung.create');
    Route::post('/registrieren', [PublicRegistrationController::class, 'store'])->name('registrierung.store');
});
Route::get('/registrieren/{interessent}/bestaetigen', [PublicRegistrationController::class, 'confirm'])
    ->name('registrierung.bestaetigen')
    ->middleware('signed');

//Selfservice zur Löschung der eigenen Registrierung (Softdelete-Schutz + zeitversetzte endgültige Löschung)
Route::group(['middleware' => ['throttle:6,1']], function () {
    Route::get('/registrieren/loeschen', [SelfServiceDeletionController::class, 'create'])->name('registrierung.loeschen');
    Route::post('/registrieren/loeschen', [SelfServiceDeletionController::class, 'store'])->name('registrierung.loeschen.store');
});
Route::get('/registrieren/{interessent}/loeschung-bestaetigen', [SelfServiceDeletionController::class, 'confirm'])
    ->name('registrierung.loeschung.bestaetigen')
    ->middleware('signed');

//Verkäufer-Self-Service-Portal (Artikel-Erfassung + Etiketten-Druck), Zugriff per UUID wie beim Ergebnis-Link
Route::group(['middleware' => ['throttle:30,1']], function () {
    Route::get('/verkaeufer/{uuid}', [VerkaeuferPortalController::class, 'index'])->name('verkaeuferPortal.index');
    Route::post('/verkaeufer/{uuid}/artikel', [VerkaeuferPortalController::class, 'store'])->name('verkaeuferPortal.store');
    Route::delete('/verkaeufer/{uuid}/artikel/{artikel}', [VerkaeuferPortalController::class, 'destroy'])->name('verkaeuferPortal.destroy');
    Route::get('/verkaeufer/{uuid}/etiketten', [VerkaeuferPortalController::class, 'etiketten'])->name('verkaeuferPortal.etiketten');
});

//Automatisches Wartelisten-Nachrücken: Bestätigungslink per Token (kein Login nötig)
Route::get('/warteliste/{token}/bestaetigen', [WartelistenAngebotController::class, 'confirm'])
    ->name('warteliste.bestaetigen')
    ->middleware('throttle:20,1');


Route::group(['middleware' => ['auth']], function (){
    Route::get('/', 'HomeController@index')->name('home');

} );

Route::group(['middleware' => ['auth', 'isVerwaltung']], function () {

    //Helfer
    Route::get('/helfertermine', [\App\Http\Controllers\AppointmentController::class, 'create'])->name('helfertermine');
    Route::post('/helfertermine', [\App\Http\Controllers\AppointmentController::class, 'store'])->name('appointment.store');
    Route::delete('appointment/{appointment}', [\App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointment.destroy');

    //Kisten Check-in / Check-out
    Route::get('/kisten', [\App\Http\Controllers\KistenController::class, 'index'])->name('kisten.index');
    Route::post('/kisten', [\App\Http\Controllers\KistenController::class, 'store'])->name('kisten.store');
    Route::post('/kisten/{kiste}/checkout', [\App\Http\Controllers\KistenController::class, 'checkout'])->name('kisten.checkout');
    Route::get('/kisten/scan/{qrToken}', [\App\Http\Controllers\KistenController::class, 'scan'])->name('kisten.scan');

    //Create User
    Route::get('/interessenten/{interessenten}/addUserAccount', [UserController::class, 'create']);
    Route::get('/interessenten/{interessenten}/deleteUserAccount', [UserController::class, 'delete']);
    Route::get('/interessenten/{interessenten}/removeKassenZugang', [UserController::class, 'removeKassenZugang']);
    Route::get('/interessenten/{interessenten}/createKassenZugang', [UserController::class, 'createKasseZugang']);

    Route::get('/home', [HomeController::class,'index'])->name('home');
    Route::get('/grunddaten', 'KlamottenboersenController@show');
    //Anmeldung moeglich
    Route::get('/checkAnmeldung', 'MailController@anmeldungMoeglich');

    //Route::get('/unreadMail', 'MailController@unreadCount');

    //Listen
    Route::get('/listen/verkaeuferinfos', 'ListenController@verkaeuferinfos');
    Route::get('/listen/vknummern', 'ListenController@vknummern');
    Route::get('/listen/belehrung/{vknummer?}', 'ListenController@belehrung');
    Route::get('/listen/abstreichliste', 'ListenController@abstreichliste');

    //Mail
    Route::get('/mail/{interessenten}/{mailvorlagen?}', 'MailController@composeNewMail');
    Route::get('reply/{uid}/', 'MailController@replyMail');
    Route::put('mail/reply/send', 'MailController@sendReply');
    Route::put('mail/{interessent}/send', 'MailController@sendMail');
    Route::get('/getMail/{uid}', 'MailController@getUidMail');
    Route::post('/getMail/{uid}', 'MailController@getUidMail');

    Route::get('/getMails/', 'MailController@getMails');
    Route::post('/deleteMail/{uid}', 'MailController@deleteMessage');
    Route::get('/deleteMail/{uid}', 'MailController@deleteMessage');
    Route::get('/getUsermail/{id}', 'MailController@getInteressentenMail');
    Route::get('/spamMail/{uid}', 'MailController@markSpamMail');
    Route::post('/spamMail/{uid}', 'MailController@markSpamMail');

    //Mail-Protokoll (Versandstatus / Logs "Anmeldung möglich")
    Route::get('/mail-protokoll/anmeldung-moeglich', 'MailLogController@anmeldungMoeglich')->name('mailLog.anmeldungMoeglich');
    Route::post('/mail-protokoll/anmeldung-moeglich/resend-all', 'MailLogController@resendAll')->name('mailLog.resendAll');
    Route::post('/mail-protokoll/anmeldung-moeglich/{mailLog}/resend', 'MailLogController@resend')->name('mailLog.resend');

    //Audit-Log
    Route::get('/audit-log', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('auditLog.index');

    //Verkäufernummern
    Route::get('/vknummer/{id}/reservierungAufheben', 'NummernController@reservierungAufheben');
    Route::get('/vknummer/{interessenten}/reservierung', 'NummernController@reserviereNummer');
    Route::post('/Nummern/reservieren', 'NummernController@nummerReservieren');
    Route::get('/vknummer/{vknummer}/vergeben', 'NummernController@reservierungVergeben');
    Route::get('/vknummer/{interessenten}/Nummervergeben', 'NummernController@vergebeNummer');

    Route::get('/vknummern/{vknummer}/freiVergeben', 'NummernController@freiVergeben');

    //Wartelise
    Route::get('/warteliste/{interessentenID}/set', 'WartelistenController@set');
    Route::delete('/warteliste/{interessentenID}/', 'WartelistenController@drop');
    //Import
    Route::get('/import', 'KlamottenboersenController@import');
    Route::put('/import/', 'KlamottenboersenController@saveImport');

    Route::put('/vknummer/{vknummer}/remove', 'NummernController@removeVergabe');
    Route::put('/vknummern/vergeben', 'NummernController@newVKnummerVergeben');

    //Notitz
    Route::put('notiz/{InteressentenID}', 'NotizenController@store');
    //newInteressent from Mail
    Route::post('newInteressent', 'InteressentenController@create');


    Route::resources([
        'interessenten' => 'InteressentenController',
        'interessent' => 'InteressentenController',
        'vknummern' => 'NummernController',
        'mailvorlagen'  => 'MailvorlagenController',
    ]);
    Route::get('interessent/{interessent}/{mailbox?}', [InteressentenController::class,'show']);


    //Klamottenbörse
    Route::resource('klamottenboerse', 'KlamottenboersenController');

    Route::get('/mailable', function () {
        $Interessent = \App\Model\Interessenten::find(73);
        $VKnummer = \App\Model\VKnummer::find(567);

        return new \App\Mail\Verkaeuferinfos($VKnummer);
    });
});

Route::group(['middleware' => ['auth', 'isKasse']], function () {

    Route::prefix('kasse')->group(function () {;
        Route::get('/', [KasseController::class, 'index']);

        Route::get('/verlauf', [VerlaufController::class, 'index']);
        Route::get('/verlauf/verkaeufer', [VerlaufController::class, 'verkaeufer']);
        Route::get('/verlauf/edit', [VerlaufController::class, 'activEdit'])->name('verlauf.activate.edit');
        Route::get('verlauf/{VerkaufsID}/edit', [VerlaufController::class, 'editVerkauf']);
        Route::post('/artikelBuchen', [KasseController::class, 'ArtikelInWarenkorb']);
        Route::post('/sync', [OfflineSyncController::class, 'sync']);

        Route::get('/kasse/{ArticleID}/edit', [KasseController::class, 'editArticle']);
        Route::get('/bezahlen', [KasseController::class, 'bezahlen']);
        Route::post('/wechselgeld', [KasseController::class, 'wechselgeld']);



        Route::get('/settings', [SettingsController::class, 'index']);
        Route::post('/settings', [SettingsController::class, 'save']);

        Route::get('/Auswertung', [\App\Http\Controllers\Kasse\AbrechnungsController::class, 'perform']);

        Route::get('import', [\App\Http\Controllers\Kasse\ImportController::class, 'index']);
        Route::post('import', [\App\Http\Controllers\Kasse\ImportController::class, 'import']);
        Route::get('export', [ExportController::class, 'downloadExcel']);
    });


});
