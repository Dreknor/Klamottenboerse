<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToHelferTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('helfer', function(Blueprint $table)
		{
			$table->foreign('klamottenboerse_id')->references('id')->on('klamottenboerse')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('helfer', function(Blueprint $table)
		{
			$table->dropForeign('helfer_klamottenboerse_id_foreign');
		});
	}

}
