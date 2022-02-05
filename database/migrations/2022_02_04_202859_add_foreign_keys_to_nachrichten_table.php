<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNachrichtenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('nachrichten', function(Blueprint $table)
		{
			$table->foreign('interessent_id')->references('id')->on('interessenten')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('nachrichten', function(Blueprint $table)
		{
			$table->dropForeign('nachrichten_interessent_id_foreign');
		});
	}

}
