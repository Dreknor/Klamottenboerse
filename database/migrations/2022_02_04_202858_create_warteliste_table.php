<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWartelisteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        if (!Schema::hasTable('warteliste')) {

            Schema::create('warteliste', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('interessenten_id')->unsigned()->index();
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
		Schema::drop('warteliste');
	}

}
