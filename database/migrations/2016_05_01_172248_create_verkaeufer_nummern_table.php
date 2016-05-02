<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerkaeuferNummernTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vknummern', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vknummer');
            $table->integer('reserviert_fuer');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('reserviert_fuer')
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
        Schema::drop('vknummern');

    }
}
