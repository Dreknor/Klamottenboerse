<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerkaufsartikelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verkaufsartikel', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vknummer_id');
            $table->integer('artikelnummer');
            $table->string('beschreibung');
            $table->string('kategorie')->nullable();
            $table->string('groesse')->nullable();
            $table->decimal('preis', 8, 2);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('vknummer_id')->references('id')->on('vknummern')->onDelete('cascade');
            $table->unique(['vknummer_id', 'artikelnummer']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verkaufsartikel');
    }
}
