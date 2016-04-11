<?php

/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 26.03.2016
 * Time: 22:25
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNachrichten
{
    public function up()
    {
        Schema::create('nachrichten', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('interessent_id')->index()->unsigned();
            $table->mediumText('betreff');
            $table->longText('nachricht');
            $table->timestamps();
            $table->foreign('interessent_id')
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
        Schema::drop('nachrichten');
    }
}