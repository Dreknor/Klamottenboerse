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
            $table->integer('klamottenboersen_id')->index()->unsigned();
            $table->integer('reserviert_fuer')->index()->unsigned()->nullable();
            $table->integer('vergeben_an')->index()->unsigned()->nullable();

            $table->softDeletes();
            $table->timestamps();


            $table->foreign('vergeben_an')
                ->references('id')
                ->on('interessenten')
                ->onDelete('set null');

            $table->foreign('reserviert_fuer')
                ->references('id')
                ->on('interessenten')
                ->onDelete('set null');
            
            $table->foreign('klamottenboersen_id')
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
        Schema::drop('vknummern');

    }
}
