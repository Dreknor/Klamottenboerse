<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToWartelisteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('warteliste', function(Blueprint $table)
		{
			$table->foreign('interessenten_id')->references('id')->on('interessenten')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('warteliste', function(Blueprint $table)
		{
			$table->dropForeign('warteliste_interessenten_id_foreign');
		});
	}

}
