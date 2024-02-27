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
        if (Schema::hasTable('helfer') && !Schema::hasColumn('helfer', 'appointment_id')) {
            Schema::table('helfer', function (Blueprint $table) {
                $table->foreignId('appointment_id')->nullable()->constrained();
                $table->softDeletes();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('helfer', function (Blueprint $table) {
            //
        });
    }
};
