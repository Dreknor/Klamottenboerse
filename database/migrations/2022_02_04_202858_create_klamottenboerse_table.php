<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKlamottenboerseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        if (!Schema::hasTable('klamottenboerse')) {
            Schema::create('klamottenboerse', function(Blueprint $table)
            {
                $table->increments('id');
                $table->date('datum');
                $table->date('anmeldung');
                $table->date('anmeldungKinderhaus');
                $table->time('anlieferung_von');
                $table->time('anlieferung_bis');
                $table->time('abholung_von');
                $table->time('abholung_bis');
                $table->integer('maxTeile');
                $table->timestamps();
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
		Schema::drop('klamottenboerse');
	}

}
