<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVknummernKommentarTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        if (!Schema::hasTable('vknummern_kommentar')) {

            Schema::create('vknummern_kommentar', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('vknummer')->unsigned();
                $table->text('kommentar');
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
		Schema::drop('vknummern_kommentar');
	}

}
