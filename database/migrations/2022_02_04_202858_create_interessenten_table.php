<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInteressentenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        if (!Schema::hasTable('interessenten')) {

            Schema::create('interessenten', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('anrede');
                $table->string('vorname')->index();
                $table->string('nachname')->index();
                $table->string('straße')->nullable();
                $table->string('hausnummer')->nullable();
                $table->char('plz')->nullable();
                $table->string('ort')->nullable();
                $table->char('telefon', 30)->nullable();
                $table->string('handy')->nullable();
                $table->string('mail');
                $table->boolean('mitarbeiter')->nullable();
                $table->boolean('kinderhaus')->nullable();
                $table->timestamps();
                $table->softDeletes();
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
		Schema::drop('interessenten');
	}

}
