<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WarenkorbErstellen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('warenkorb')){
            Schema::create('warenkorb', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->index()->unsigned();
                $table->integer('vknummer');
                $table->integer('artikelnummer');
                $table->float('betrag');
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users');
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
        //
        Schema::drop('warenkorb');

    }
}
