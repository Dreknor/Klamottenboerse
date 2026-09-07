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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('bereich')->nullable()->after('beschreibung');
            $table->timestamp('erinnerung_versendet_at')->nullable()->after('helfer_id');
        });

        Schema::table('mail_logs', function (Blueprint $table) {
            $table->unsignedInteger('helfer_id')->nullable()->after('interessent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['bereich', 'erinnerung_versendet_at']);
        });

        Schema::table('mail_logs', function (Blueprint $table) {
            $table->dropColumn('helfer_id');
        });
    }
};
