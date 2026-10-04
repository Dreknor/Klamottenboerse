<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategorien', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('sortierung')->default(0);
            $table->timestamps();
        });

        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teilnahme_id')->constrained('teilnahmen')->cascadeOnDelete();
            $table->unsignedSmallInteger('laufnummer');
            $table->string('beschreibung');
            $table->foreignId('kategorie_id')->nullable()->constrained('kategorien')->nullOnDelete();
            $table->string('groesse')->nullable();
            $table->unsignedInteger('preis_cent');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['teilnahme_id', 'laufnummer']);
        });

        Schema::create('kisten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teilnahme_id')->constrained('teilnahmen')->cascadeOnDelete();
            $table->unsignedSmallInteger('kistennummer');
            $table->string('qr_token', 64)->unique();
            $table->dateTime('angenommen_at')->nullable();
            $table->foreignId('angenommen_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->dateTime('ausgegeben_at')->nullable();
            $table->foreignId('ausgegeben_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->text('bemerkung')->nullable();
            $table->timestamps();

            $table->unique(['teilnahme_id', 'kistennummer']);
        });

        Schema::create('kassen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('geraete_token', 64)->unique();
            $table->dateTime('zuletzt_gesehen_at')->nullable();
            $table->dateTime('gesperrt_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kassenschichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasse_id')->constrained('kassen')->cascadeOnDelete();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->dateTime('beginn');
            $table->dateTime('ende')->nullable();
            $table->integer('anfangsbestand_cent')->default(0);
            $table->integer('gezaehlt_cent')->nullable();
            $table->timestamps();
        });

        Schema::create('bons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->foreignId('kassenschicht_id')->nullable()->constrained('kassenschichten')->nullOnDelete();
            $table->integer('summe_cent');
            $table->string('zahlart')->default('bar');
            $table->dateTime('erstellt_am_geraet');
            $table->dateTime('storniert_at')->nullable();
            $table->string('storno_grund')->nullable();
            $table->timestamps();
        });

        Schema::create('bonpositionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_id')->constrained('bons')->cascadeOnDelete();
            $table->foreignId('teilnahme_id')->constrained('teilnahmen')->restrictOnDelete();
            $table->foreignId('artikel_id')->nullable()->constrained('artikel')->nullOnDelete();
            $table->unsignedSmallInteger('artikelnummer');
            $table->integer('preis_cent');
            $table->dateTime('storniert_at')->nullable();
            $table->timestamps();

            $table->index(['teilnahme_id', 'artikelnummer']);
        });

        Schema::create('abrechnungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teilnahme_id')->unique()->constrained('teilnahmen')->cascadeOnDelete();
            $table->integer('umsatz_cent');
            $table->integer('spende_cent');
            $table->integer('auszahlung_cent');
            $table->unsignedInteger('verkaufte_artikel');
            $table->dateTime('berechnet_at');
            $table->dateTime('ausgezahlt_at')->nullable();
            $table->foreignId('ausgezahlt_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sonstige_einnahmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->string('art');
            $table->integer('betrag_cent');
            $table->string('notiz')->nullable();
            $table->foreignId('erfasst_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sonstige_einnahmen');
        Schema::dropIfExists('abrechnungen');
        Schema::dropIfExists('bonpositionen');
        Schema::dropIfExists('bons');
        Schema::dropIfExists('kassenschichten');
        Schema::dropIfExists('kassen');
        Schema::dropIfExists('kisten');
        Schema::dropIfExists('artikel');
        Schema::dropIfExists('kategorien');
    }
};
