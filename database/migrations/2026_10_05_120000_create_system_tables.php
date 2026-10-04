<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fehler und Warnungen der Anwendung – im Backend lesbar, ohne SSH
        Schema::create('fehlerprotokoll', function (Blueprint $table) {
            $table->id();
            $table->string('stufe', 20)->index();
            $table->text('nachricht');
            $table->string('klasse')->nullable();
            $table->string('datei')->nullable();
            $table->unsignedInteger('zeile')->nullable();
            $table->longText('trace')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('methode', 10)->nullable();
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->json('kontext')->nullable();
            $table->char('hash', 40)->index();
            $table->unsignedInteger('anzahl')->default(1);
            $table->timestamp('zuletzt_at')->nullable()->index();
            $table->timestamp('erledigt_at')->nullable();
            $table->timestamps();
        });

        // Updates aus der Weboberfläche
        Schema::create('updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->string('von_version', 40)->nullable();
            $table->string('auf_version', 40)->nullable();
            $table->string('status', 20); // laeuft, erfolgreich, fehlgeschlagen
            $table->longText('ausgabe')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('updates');
        Schema::dropIfExists('fehlerprotokoll');
    }
};
