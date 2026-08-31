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
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('interessent_id')->nullable();
            $table->unsignedInteger('klamottenboerse_id')->nullable();
            $table->string('typ');
            $table->string('email');
            $table->string('betreff')->nullable();
            $table->string('status')->default('queued'); // queued, sent, failed
            $table->text('fehler')->nullable();
            $table->timestamp('versendet_at')->nullable();
            $table->timestamps();

            $table->foreign('interessent_id')->references('id')->on('interessenten')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('klamottenboerse_id')->references('id')->on('klamottenboerse')->onUpdate('RESTRICT')->onDelete('SET NULL');

            $table->index(['typ', 'klamottenboerse_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_logs');
    }
};
