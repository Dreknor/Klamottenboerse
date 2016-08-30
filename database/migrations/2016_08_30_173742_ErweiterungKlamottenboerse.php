<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ErweiterungKlamottenboerse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('klamottenboerse', function ($table) {
            $table->time('anlieferung_von');
            $table->time('anlieferung_bis');
            $table->time('abholung_von');
            $table->time('abholung_bis');
            $table->integer('maxTeile');

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
