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
            $table->char('telefon', 30) ->nullable();
            $table->string('mail');
            $table->boolean('mitarbeiter')->nullable();
            $table->boolean('kinderhaus')->nullable();
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
