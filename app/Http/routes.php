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
     * Routen für die Klamottenbpoerse
    */

    Route::get('/Grunddaten', 'KlamottenboersenController@index');

    /*
     * Routen zur Dateienverwaltung
     */
    Route::get('/Dateien', 'DateienController@index');
    Route::get('/Dateien/{DateiID}', 'DateienController@get');
    Route::put('Dateien/add', 'DateienController@uploadFiles');
    Route::delete('Dateien/{DateiID}', 'DateienController@destroy');

});
