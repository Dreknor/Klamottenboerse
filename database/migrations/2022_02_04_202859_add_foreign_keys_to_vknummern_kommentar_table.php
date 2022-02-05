<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVknummernKommentarTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vknummern_kommentar', function(Blueprint $table)
		{
			$table->foreign('vknummer')->references('id')->on('vknummern')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vknummern_kommentar', function(Blueprint $table)
		{
			$table->dropForeign('vknummern_kommentar_vknummer_foreign');
		});
	}

}
