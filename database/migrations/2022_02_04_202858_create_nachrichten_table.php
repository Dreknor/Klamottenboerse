<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNachrichtenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nachrichten', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('interessent_id')->unsigned()->index();
			$table->text('betreff');
			$table->text('nachricht');
			$table->timestamps();
			$table->string('pfad');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('nachrichten');
	}

}
