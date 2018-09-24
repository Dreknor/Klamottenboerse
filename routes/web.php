<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => ['web']], function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/grunddaten', 'KlamottenboersenController@show');

    Route::get('/getUsermail/{id}', 'MailController@getInteressentenMail');
    Route::get('/unreadMail', 'MailController@unreadCount');


    //Verkäufernummern
    Route::get('/vknummer/{id}/reservierungAufheben', 'NummernController@reservierungAufheben');
    Route::get('/vknummer/{interessenten}/reservierung', 'NummernController@reserviereNummer');
    Route::post('/Nummern/reservieren', 'NummernController@nummerReservieren');
    Route::get('/vknummer/{vknummer}/vergeben', 'NummernController@reservierungVergeben');
    Route::put('/vknummer/{vknummer}/remove', 'NummernController@removeVergabe');


    Route::resources([
        'interessenten' => 'InteressentenController',
        'interessent' => 'InteressentenController',
        'vknummern' => 'NummernController',
    ]);

    Route::resource('klamottenboerse', "KlamottenboersenController");


    Route::get('/mailable', function () {
        $Interessent = \App\Model\Interessenten::find(73);

        return new \App\Mail\Nummerentzogen($Interessent);
    });
});



