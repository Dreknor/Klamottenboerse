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
        Schema::table('verkaeufe', function (Blueprint $table) {
            $table->unsignedInteger('klamottenboerse_id')->nullable()->after('user_id');
            $table->foreign('klamottenboerse_id')->references('id')->on('klamottenboerse')->onUpdate('RESTRICT')->onDelete('CASCADE');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verkaeufe', function (Blueprint $table) {
            $table->dropForeign('verkaeufe_klamottenboerse_id_foreign');
            $table->dropColumn('klamottenboerse_id');
        });

    }
};
