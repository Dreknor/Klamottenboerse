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
        Schema::table('klamottenboerse', function (Blueprint $table) {
            $table->boolean('ergebnis_freigabe')->default(false)->after('abholung_bis');
        });

        Schema::table('interessenten', function (Blueprint $table) {
            $table->uuid();
        });

        $interessenten = \App\Model\Interessenten::all();

        foreach ($interessenten as $interessent) {
            $interessent->uuid = \Illuminate\Support\Str::uuid();
            $interessent->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('klamottenboerse', function (Blueprint $table) {
            $table->dropColumn('ergebnis_freigabe');
        });

        Schema::table('interessenten', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
