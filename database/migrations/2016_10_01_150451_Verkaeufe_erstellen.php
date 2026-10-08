<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VerkaeufeErstellen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('verkaeufe')){
            Schema::create('verkaeufe', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->index()->unsigned();
                $table->float('summe');
                $table->timestamps();

                // Auf einer frischen Datenbank existiert "users" hier noch nicht
                // (wird erst 2022_02_04 angelegt); der Fremdschlüssel wird dann in
                // 2022_02_04_202859_add_user_foreign_keys_to_kasse_tables nachgeholt.
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users');
                }
            });
        }

        if (!Schema::hasTable('verkaufteartikel')){
            Schema::create('verkaufteartikel', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('verkauf')->unsigned();
                $table->integer('vknummer');
                $table->integer('artikelnummer');
                $table->float('betrag');

                $table->foreign('verkauf')
                    ->references('id')
                    ->on('verkaeufe');

                // Kein Fremdschlüssel auf vknummern.vknummer: die Nummer ist je
                // Klamottenbörse vergeben und daher dort weder eindeutig noch indiziert.

            });
        }




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('verkaeufe');
        Schema::drop('verkaufteartikel');

    }
}
