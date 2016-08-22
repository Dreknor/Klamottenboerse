<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VknummernKommentar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vknummern_kommentar', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('vknummer')->index()->unsigned();
        $table->longText('kommentar');
        $table->timestamps();

        $table->foreign('vknummer')
            ->references('id')
            ->on('vknummern')
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
