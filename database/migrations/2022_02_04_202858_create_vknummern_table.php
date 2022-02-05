<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVknummernTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vknummern', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('vknummer');
			$table->integer('klamottenboersen_id')->unsigned()->index();
			$table->integer('reserviert_fuer')->unsigned()->nullable()->index();
			$table->integer('vergeben_an')->unsigned()->nullable()->index();
			$table->decimal('umsatz')->nullable();
			$table->softDeletes();
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
		Schema::drop('vknummern');
	}

}
