<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarteliste extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('warteliste', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('interessenten_id')->index()->unsigned();
            $table->timestamps();

            $table->foreign('interessenten_id')
                ->references('id')
                ->on('interessenten')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
