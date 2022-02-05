<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDateienTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dateien', function(Blueprint $table)
		{
			$table->increments('datei_id');
			$table->text('dateiname');
			$table->text('dateibeschreibung');
			$table->string('pfad');
			$table->string('mime');
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
		Schema::drop('dateien');
	}

}
