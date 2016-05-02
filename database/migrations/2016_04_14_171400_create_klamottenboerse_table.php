<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKlamottenboerseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('klamottenboerse', function (Blueprint $table) {
            $table->increments('id');
            $table->date('datum');
            $table->date('anmeldung');
            $table->date('anmeldungKinderhaus');
            $table->timestamps();

        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('klamottenboerse');
    }
}
