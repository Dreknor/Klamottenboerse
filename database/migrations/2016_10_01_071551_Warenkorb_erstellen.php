<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WarenkorbErstellen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('warenkorb')){
            Schema::create('warenkorb', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->index()->unsigned();
                $table->integer('vknummer');
                $table->integer('artikelnummer');
                $table->float('betrag');
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



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('warenkorb');

    }
}
