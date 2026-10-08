<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verkaeufer_vermerke', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('interessent_id')->index();
            $table->unsignedInteger('klamottenboerse_id')->nullable()->index();
            $table->unsignedInteger('vknummer_id')->nullable();
            $table->string('typ', 50);
            $table->unsignedTinyInteger('punkte');
            $table->text('bemerkung')->nullable();
            $table->string('quelle', 30)->nullable();
            $table->unsignedInteger('erfasst_von')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('interessenten', function (Blueprint $table) {
            // null = automatisch nach Punkten, 1 = immer nur händisch, 0 = trotz Punkten freigegeben
            $table->boolean('nur_manuelle_vergabe')->nullable();
            $table->json('angebotskategorien')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interessenten', function (Blueprint $table) {
            $table->dropColumn(['nur_manuelle_vergabe', 'angebotskategorien']);
        });

        Schema::dropIfExists('verkaeufer_vermerke');
    }
};
