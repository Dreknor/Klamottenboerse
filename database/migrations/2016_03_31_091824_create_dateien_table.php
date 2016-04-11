<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDateienTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dateien', function (Blueprint $table) {
            $table->increments('datei_id');
            $table->text('dateiname');
            $table->text('dateibeschreibung');
            $table->string('pfad');
            $table->string('mime');
            $table->timestamps();

        });

        Schema::table('nachrichten', function ($table) {
            $table->string('pfad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dateien');

        Schema::table('nachrichten', function ($table) {
            $table->dropColumn('pfad');
        });
    }
}
