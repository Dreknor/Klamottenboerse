<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAngebotToWartelisteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warteliste', function (Blueprint $table) {
            $table->unsignedInteger('angebotene_vknummer_id')->nullable()->after('interessenten_id');
            $table->timestamp('angebot_versendet_at')->nullable()->after('angebotene_vknummer_id');
            $table->timestamp('angebot_ablauf_at')->nullable()->after('angebot_versendet_at');
            $table->timestamp('bestaetigt_at')->nullable()->after('angebot_ablauf_at');
            $table->string('token', 64)->nullable()->unique()->after('bestaetigt_at');
            $table->text('uebersprungene_vknummern')->nullable()->after('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warteliste', function (Blueprint $table) {
            $table->dropColumn([
                'angebotene_vknummer_id',
                'angebot_versendet_at',
                'angebot_ablauf_at',
                'bestaetigt_at',
                'token',
                'uebersprungene_vknummern',
            ]);
        });
    }
}
