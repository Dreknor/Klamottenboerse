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
        Schema::create('kisten', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('klamottenboerse_id');
            $table->unsignedInteger('vknummer_id');
            $table->unsignedInteger('kistennummer');
            $table->string('qr_token', 64)->unique();
            $table->string('status')->default('abgegeben');
            $table->timestamp('abgegeben_at')->nullable();
            $table->unsignedInteger('abgegeben_von')->nullable();
            $table->timestamp('abgeholt_at')->nullable();
            $table->unsignedInteger('abgeholt_von')->nullable();
            $table->text('bemerkung')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['vknummer_id', 'kistennummer']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kisten');
    }
};
