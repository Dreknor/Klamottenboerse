<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helfer und Schichten
        Schema::create('schichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->string('bereich');
            $table->string('beschreibung')->nullable();
            $table->dateTime('beginn');
            $table->dateTime('ende');
            $table->unsignedSmallInteger('soll')->default(1);
            $table->timestamps();
        });

        Schema::create('einteilungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schicht_id')->constrained('schichten')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->string('status')->default('zugesagt');
            $table->string('quelle')->default('online');
            $table->dateTime('erinnert_at')->nullable();
            $table->timestamps();

            $table->unique(['schicht_id', 'person_id']);
        });

        // Kommunikation
        Schema::create('mailvorlagen', function (Blueprint $table) {
            $table->id();
            $table->string('schluessel')->unique();
            $table->string('name');
            $table->string('betreff');
            $table->text('inhalt');
            $table->timestamps();
        });

        Schema::create('mailplan_eintraege', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->foreignId('mailvorlage_id')->constrained('mailvorlagen')->cascadeOnDelete();
            $table->string('zielgruppe');
            $table->string('bezugsdatum');
            $table->smallInteger('versatz_tage')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->dateTime('eingeplant_at')->nullable();
            $table->timestamps();
        });

        Schema::create('nachrichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->nullOnDelete();
            $table->foreignId('mailplan_eintrag_id')->nullable()->constrained('mailplan_eintraege')->nullOnDelete();
            $table->string('typ');
            $table->string('email');
            $table->string('betreff');
            $table->text('inhalt');
            $table->string('status')->default('wartend');
            $table->text('fehler')->nullable();
            $table->dateTime('versendet_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['typ', 'boerse_id']);
        });

        Schema::create('notizen', function (Blueprint $table) {
            $table->id();
            $table->morphs('notizbar');
            $table->text('text');
            $table->foreignId('autor_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->timestamps();
        });

        // Checklisten, Aufgaben, Teamkalender
        Schema::create('checklistenvorlagen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('fuer_neue_boersen')->default(false);
            $table->timestamps();
        });

        Schema::create('checklistenvorlage_eintraege', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklistenvorlage_id')->constrained('checklistenvorlagen')->cascadeOnDelete();
            $table->string('titel');
            $table->text('beschreibung')->nullable();
            $table->string('phase')->nullable();
            $table->smallInteger('versatz_tage')->default(0);
            $table->unsignedSmallInteger('sortierung')->default(0);
            $table->timestamps();
        });

        Schema::create('aufgaben', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->cascadeOnDelete();
            $table->string('titel');
            $table->text('beschreibung')->nullable();
            $table->string('phase')->nullable();
            $table->date('faellig_am')->nullable();
            $table->foreignId('zustaendig_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->dateTime('erledigt_at')->nullable();
            $table->foreignId('erledigt_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->dateTime('erinnert_at')->nullable();
            $table->unsignedSmallInteger('sortierung')->default(0);
            $table->timestamps();
        });

        Schema::create('termine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->cascadeOnDelete();
            $table->string('titel');
            $table->dateTime('beginn');
            $table->dateTime('ende')->nullable();
            $table->string('ort')->nullable();
            $table->text('beschreibung')->nullable();
            $table->timestamps();
        });

        // Feedback nach der Börse
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->string('rolle');
            $table->string('token', 64)->unique();
            $table->unsignedTinyInteger('bewertung')->nullable();
            $table->text('gut')->nullable();
            $table->text('besser')->nullable();
            $table->dateTime('beantwortet_at')->nullable();
            $table->timestamps();

            $table->unique(['boerse_id', 'person_id', 'rolle']);
        });

        Schema::create('einstellungen', function (Blueprint $table) {
            $table->string('schluessel')->primary();
            $table->json('wert')->nullable();
            $table->timestamps();
        });

        Schema::create('v1_mapping', function (Blueprint $table) {
            $table->string('tabelle');
            $table->unsignedBigInteger('v1_id');
            $table->unsignedBigInteger('v2_id');

            $table->primary(['tabelle', 'v1_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('v1_mapping');
        Schema::dropIfExists('einstellungen');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('termine');
        Schema::dropIfExists('aufgaben');
        Schema::dropIfExists('checklistenvorlage_eintraege');
        Schema::dropIfExists('checklistenvorlagen');
        Schema::dropIfExists('notizen');
        Schema::dropIfExists('nachrichten');
        Schema::dropIfExists('mailplan_eintraege');
        Schema::dropIfExists('mailvorlagen');
        Schema::dropIfExists('einteilungen');
        Schema::dropIfExists('schichten');
    }
};
