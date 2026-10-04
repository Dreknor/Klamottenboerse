<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personen', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('vorname');
            $table->string('nachname');
            // E-Mail ist optional: manuell erfasste Helfer brauchen nur einen Namen.
            $table->string('email')->nullable()->unique();
            $table->string('telefon')->nullable();
            $table->string('kinderhaus_bezug')->default('keiner');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('info_mails_erlaubt_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamp('letzte_aktivitaet_at')->nullable();
            $table->timestamp('loeschung_angefragt_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['nachname', 'vorname']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('personen');
    }
};
