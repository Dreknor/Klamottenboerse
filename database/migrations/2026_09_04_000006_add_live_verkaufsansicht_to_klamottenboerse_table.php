<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLiveVerkaufsansichtToKlamottenboerseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('klamottenboerse', function (Blueprint $table) {
            $table->boolean('live_verkaufsansicht_freigabe')->default(false)->after('ergebnis_freigabe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('klamottenboerse', function (Blueprint $table) {
            $table->dropColumn('live_verkaufsansicht_freigabe');
        });
    }
}
