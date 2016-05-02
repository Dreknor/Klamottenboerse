<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('helfer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('klamottenboerse_id')->index()->unsigned();
            $table->text('name');
            $table->text('telefon');
            $table->text('mail');
            $table->text('bereich');
            $table->timestamps();
            $table->foreign('klamottenboerse_id')
                ->references('id')
                ->on('klamottenboerse')
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
        Schema::drop('helfer');
    }
}
