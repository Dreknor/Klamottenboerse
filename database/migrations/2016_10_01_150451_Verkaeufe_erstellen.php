<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VerkaeufeErstellen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('verkaeufe')){
            Schema::create('verkaeufe', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->index()->unsigned();
                $table->float('summe');
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users');
            });
        }

        if (!Schema::hasTable('verkaufteartikel')){
            Schema::create('verkaufteartikel', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('verkauf')->index()->unsigned();
                $table->integer('vknummer');
                $table->integer('artikelnummer');
                $table->float('betrag');

                $table->foreign('verkauf')
                    ->references('id')
                    ->on('verkaeufe');

                $table->foreign('vknummer')
                    ->references('vknummer')
                    ->on('vknummern');

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
        Schema::drop('verkaeufe');
        Schema::drop('verkaufteartikel');

    }
}
