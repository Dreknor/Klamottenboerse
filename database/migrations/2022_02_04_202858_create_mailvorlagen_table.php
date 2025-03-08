<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailvorlagenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

        if (!Schema::hasTable('mailvorlagen')) {

            Schema::create('mailvorlagen', function(Blueprint $table)
            {
                $table->increments('id');
                $table->text('name');
                $table->text('betreff');
                $table->text('text');
                $table->text('html')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->boolean('deleteable')->nullable();
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
		Schema::drop('mailvorlagen');
	}

}
