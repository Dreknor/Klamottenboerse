<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInteressentenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interessenten', function (Blueprint $table) {
            $table->increments('id');
            $table->string('anrede');
            $table->string('vorname')->index();
            $table->string('nachname')->index();
            $table->string('straße');
            $table->string('hausnummer');
            $table->char('plz');
            $table->string('ort');
            $table->char('telefon', 30);
            $table->string('mail');
            $table->boolean('mitarbeiter');
            $table->boolean('kinderhaus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('interessenten');
    }   
}
