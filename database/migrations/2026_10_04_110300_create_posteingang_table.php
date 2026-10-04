<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Abgerufene Mails aus dem IMAP-Postfach (z. B. anmeldung@). Der Abruf läuft per Scheduler.
        Schema::create('posteingang', function (Blueprint $table) {
            $table->id();
            $table->string('ordner')->default('INBOX');
            $table->unsignedBigInteger('uid');
            $table->string('message_id')->nullable();
            $table->string('von_email');
            $table->string('von_name')->nullable();
            $table->string('betreff')->nullable();
            $table->longText('text')->nullable();
            $table->json('anhaenge')->nullable();
            $table->dateTime('empfangen_at');
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->dateTime('gelesen_at')->nullable();
            $table->dateTime('beantwortet_at')->nullable();
            $table->dateTime('erledigt_at')->nullable();
            $table->boolean('spam')->default(false);
            $table->timestamps();

            $table->unique(['ordner', 'uid']);
            $table->index('von_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posteingang');
    }
};
