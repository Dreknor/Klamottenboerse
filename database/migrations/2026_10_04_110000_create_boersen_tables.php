<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orte', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('adresse')->nullable();
            $table->timestamps();
        });

        Schema::create('boersen', function (Blueprint $table) {
            $table->id();
            $table->string('titel');
            $table->date('verkaufstag');
            $table->foreignId('ort_id')->nullable()->constrained('orte')->nullOnDelete();
            $table->string('status')->default('planung');

            $table->dateTime('anmeldung_kinderhaus_ab')->nullable();
            $table->dateTime('anmeldung_ab')->nullable();
            $table->dateTime('anlieferung_beginn')->nullable();
            $table->dateTime('anlieferung_ende')->nullable();
            $table->dateTime('verkauf_beginn')->nullable();
            $table->dateTime('verkauf_ende')->nullable();
            $table->dateTime('abholung_beginn')->nullable();
            $table->dateTime('abholung_ende')->nullable();

            // Nummernvergabe
            $table->unsignedSmallInteger('nummer_von')->default(200);
            $table->unsignedSmallInteger('nummer_bis')->default(599);
            $table->unsignedSmallInteger('blockgroesse')->default(100);
            $table->unsignedSmallInteger('block_toleranz')->default(5);
            $table->unsignedSmallInteger('kapazitaet')->default(240);
            $table->unsignedSmallInteger('kinderhaus_nummer')->default(600);

            $table->unsignedSmallInteger('max_teile')->nullable();
            $table->unsignedSmallInteger('max_kisten')->nullable();
            $table->unsignedSmallInteger('provision_promille')->default(250);
            $table->unsignedSmallInteger('rundung_cent')->default(10);
            $table->unsignedSmallInteger('angebot_stunden')->default(48);

            $table->boolean('ergebnis_freigegeben')->default(false);
            $table->boolean('live_erloes_freigegeben')->default(false);
            $table->text('hinweise')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('teilnahmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            // Die Kinderhaus-Teilnahme (Nummer 600) hat keine Person.
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->unsignedSmallInteger('nummer')->nullable();
            $table->string('status');
            $table->boolean('ist_kinderhaus')->default(false);
            $table->boolean('spendenfrei')->default(false);
            $table->unsignedInteger('wartelisten_position')->nullable();
            $table->dateTime('angebot_bis')->nullable();
            $table->string('quelle')->nullable();
            $table->dateTime('angemeldet_at')->nullable();
            $table->dateTime('zugeteilt_at')->nullable();
            $table->dateTime('angeliefert_at')->nullable();
            $table->dateTime('abgesagt_at')->nullable();
            $table->timestamps();

            $table->unique(['boerse_id', 'nummer']);
            $table->unique(['boerse_id', 'person_id']);
            $table->index(['boerse_id', 'status']);
        });

        Schema::create('nummernreservierungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->unsignedSmallInteger('nummer');
            // null = dauerhaft, sonst nur für diese Börse
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->cascadeOnDelete();
            $table->string('grund')->nullable();
            $table->timestamps();

            $table->index('nummer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nummernreservierungen');
        Schema::dropIfExists('teilnahmen');
        Schema::dropIfExists('boersen');
        Schema::dropIfExists('orte');
    }
};
