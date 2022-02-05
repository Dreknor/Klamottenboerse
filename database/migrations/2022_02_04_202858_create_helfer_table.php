<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelferTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('helfer', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('klamottenboerse_id')->unsigned()->index();
			$table->text('name');
			$table->text('telefon');
			$table->text('mail');
			$table->text('bereich');
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
		Schema::drop('helfer');
	}

}
