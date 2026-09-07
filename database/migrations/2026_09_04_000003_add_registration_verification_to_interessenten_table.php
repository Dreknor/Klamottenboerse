<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegistrationVerificationToInteressentenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interessenten', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('mail');
            $table->string('registration_source')->nullable()->after('email_verified_at');
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
            $table->dropColumn(['email_verified_at', 'registration_source']);
        });
    }
}
