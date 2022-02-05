<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVknummernTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vknummern', function(Blueprint $table)
		{
			$table->foreign('klamottenboersen_id')->references('id')->on('klamottenboerse')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('reserviert_fuer')->references('id')->on('interessenten')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('vergeben_an')->references('id')->on('interessenten')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vknummern', function(Blueprint $table)
		{
			$table->dropForeign('vknummern_klamottenboersen_id_foreign');
			$table->dropForeign('vknummern_reserviert_fuer_foreign');
			$table->dropForeign('vknummern_vergeben_an_foreign');
		});
	}

}
