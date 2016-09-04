<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/




/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['auth']], function () {
    //
});

Route::group(['middleware' => 'web'], function () {
    Route::auth();
    Route::get('logout', function(){
        Auth::logout(); // logout user
        return Redirect::to('/');
    });
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/{id}/abmelden/{token}', 'InteressentenController@abmelden');
    Route::delete('/{id}/abmelden/{token}', 'InteressentenController@doAbmelden');

    //Route::get('/abmelden/{id}/{token}', 'InteressentenController@abmelden');





    /*
     * Routen für die Imteressenten-Übersicht
     *
     */
    Route::get('/Ueberblick', 'InteressentenController@index');
    Route::get('/Ueberblick/{Gruppe?}', 'InteressentenController@index');
    Route::post('/Ueberblick', 'InteressentenController@search');
    Route::get('/Ueberblick/{string}/export', 'InteressentenController@export');
    

    /*
     * Routen für einzelne, bestimmte Interessenten
     */
    Route::get('/Interessent/{InteressentenID}', 'InteressentenController@show');
    Route::put('/edit-Interessent', 'InteressentenController@update');
    Route::get('/deleteInteressent/{InteressentenID}', 'InteressentenController@warningDelete');
    Route::delete('/Interessent/{InteressentenID}', 'InteressentenController@destroy');
    

    /*
     * Routen zum anlegen neuer Interessenten
     */
    Route::get('/Anlegen', function() {
        return view('InteressentAnlegen');
    });
    Route::post('/Anlegen', 'InteressentenController@store');

    /*
    * Routen für Nachrichten
    */
    Route::post('/Nachricht/{InteressentenID}', 'NachrichtenController@send');
    Route::post('/mail/{string}', 'NachrichtenController@mailGruppe');
    Route::post('/mail', 'NachrichtenController@mailGruppe');

    /*
     * Routen für die Klamottenboerse
    */

    Route::get('Grunddaten', 'KlamottenboersenController@index');
    Route::get('Grunddaten/abschliessen', 'KlamottenboersenController@neueKlamottenboerse');

    Route::put('/edit-Klamottenboerse', 'KlamottenboersenController@update');

    Route::post('Grunddaten/Anlegen', 'KlamottenboersenController@store');
    Route::post('Grunddaten/Helfer/store', 'KlamottenboersenController@store_Helfer');


    Route::delete('Grunddaten/{HelferID}/delete', 'KlamottenboersenController@destroy');


    /*
     * Routen zur Dateienverwaltung
     */
    Route::get('/Dateien', 'DateienController@index');
    Route::get('/Dateien/{DateiID}', 'DateienController@get');
    Route::put('Dateien/add', 'DateienController@uploadFiles');
    Route::delete('Dateien/{DateiID}', 'DateienController@destroy');

    /*
     * Routen zur Nummernvergabe
     */
    Route::get('/Nummern', 'NummernController@index');
    Route::get('/Nummern/new', 'NummernController@newNummer');
    Route::post('/Nummern/new', 'NummernController@store');
    Route::get('/Nummern/{InteressentenID}/aufheben', 'NummernController@deleteReservierung');
    Route::get('/Nummern/{InteressentenID}/reservieren', 'NummernController@createReservierung');
    Route::post('/Nummern/{InteressentenID}/reservieren', 'NummernController@storeReservierung');
    Route::post('Nummern/vergeben', 'NummernController@storeVergabe');
    Route::post('Nummern/vergabeLoeschen', 'NummernController@vergabeLoeschen');
    Route::delete('Nummern/NummerLoeschen', 'NummernController@NummerLoeschen');
    Route::get('/Nummern/{InteressentenID}/vergeben', 'NummernController@Nummernvergabe');
    Route::post('Nummern/Kommentar/store', 'NummernController@storeKommentar');
    Route::delete('Nummern/Kommentar/Loeschen', 'NummernController@KommentarLoeschen');
    Route::get('Nummern/{NummernID}/Vergabe', 'NummernController@Vergabe');





    /*
     * Routen zur Listenerstellung
     */

    Route::get('/Listen', 'ListenController@index');
    Route::get('Listen/vknummern', 'ListenController@vknummern');
    Route::get('Listen/belehrung', 'ListenController@belehrung');
    Route::get('Listen/helfer', 'ListenController@helfer');
    Route::get('Listen/nummern', 'ListenController@nummern');
    Route::get('Listen/Infos', 'ListenController@Infos');


    /*
     * Routen für die Mailvorlagen
     */
    Route::get('/Mailvorlagen', 'VorlagenController@index');
    Route::delete('Mailvorlagen/loeschen', 'VorlagenController@deleteVorlage');
    Route::post('Mailvorlagen/new', 'VorlagenController@storeVorlage');
    Route::get('Mailvorlagen/new', function () {
        return view('mailvorlagen.neueMailvorlagen');
    });
    Route::post('Mailvorlagen/edit', 'VorlagenController@edit');

	
    //Warteliste
    Route::get('/Warteliste/{InteressentenID}', 'WartelistenController@set');
    Route::delete('/Warteliste', 'WartelistenController@drop');


    //Import
    Route::get('/Import', 'ImportController@Import');
    Route::get('/Import_csv', 'ImportController@Import_csv');








});
