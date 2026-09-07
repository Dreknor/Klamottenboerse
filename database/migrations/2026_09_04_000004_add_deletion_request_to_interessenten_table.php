<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletionRequestToInteressentenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interessenten', function (Blueprint $table) {
            $table->timestamp('deletion_requested_at')->nullable()->after('registration_source');
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
            $table->dropColumn('deletion_requested_at');
        });
    }
}
