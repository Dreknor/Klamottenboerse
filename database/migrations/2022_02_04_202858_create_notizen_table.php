<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotizenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        if (!Schema::hasTable('notizen')) {

            Schema::create('notizen', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('interessenten_id')->unsigned()->index('notizen_interessenten_id_foreign');
                $table->text('notiz')->nullable();
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
		Schema::drop('notizen');
	}

}
