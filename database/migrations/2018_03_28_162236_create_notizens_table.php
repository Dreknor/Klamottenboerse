<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotizensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notizen', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('interessenten_id')->unsigned();
            $table->longText('notiz')->nullable();
            $table->timestamps();

            $table->foreign('interessenten_id')
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
        Schema::dropIfExists('notizens');
    }
}
