<?php

use Database\Seeders\GrunddatenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategorien wie in V1: Verkäufer geben bei der Anmeldung an, was sie überwiegend mitbringen.
     * Das Orga-Team pflegt die Liste (Gruppe, Größenbereich für die automatische Zuordnung von Artikeln, aktiv).
     */
    public function up(): void
    {
        Schema::table('kategorien', function (Blueprint $table) {
            $table->string('gruppe')->default('Weiteres')->after('name');
            $table->unsignedSmallInteger('groesse_von')->nullable()->after('gruppe');
            $table->unsignedSmallInteger('groesse_bis')->nullable()->after('groesse_von');
            $table->boolean('aktiv')->default(true)->after('sortierung');
        });

        Schema::create('kategorie_person', function (Blueprint $table) {
            $table->foreignId('kategorie_id')->constrained('kategorien')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->primary(['kategorie_id', 'person_id']);
        });

        // Die bisherige Standardliste (nur Warenarten) durch die V1-Liste ersetzen – aber nur,
        // solange noch kein Artikel eine Kategorie benutzt und niemand die Liste angepasst hat.
        // (Neue Installationen füllt der GrunddatenSeeder.)
        $alteListe = ['Oberteile', 'Hosen und Röcke', 'Kleider', 'Jacken und Matschkleidung', 'Schuhe',
            'Spielzeug', 'Bücher', 'Fahrzeuge', 'Kinderwagen und Sitze', 'Möbel und Betten', 'Sonstiges'];
        $vorhanden = DB::table('kategorien')->orderBy('id')->pluck('name')->all();
        $unveraendert = $vorhanden !== [] && (count($vorhanden) === count($alteListe) && array_diff($vorhanden, $alteListe) === []);

        if ($unveraendert && DB::table('artikel')->whereNotNull('kategorie_id')->doesntExist()) {
            DB::table('kategorien')->delete();
            $jetzt = now();
            foreach (GrunddatenSeeder::KATEGORIEN as $i => [$name, $gruppe, $von, $bis]) {
                DB::table('kategorien')->insert([
                    'name' => $name, 'gruppe' => $gruppe, 'groesse_von' => $von, 'groesse_bis' => $bis,
                    'sortierung' => ($i + 1) * 10, 'aktiv' => true, 'created_at' => $jetzt, 'updated_at' => $jetzt,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kategorie_person');
        Schema::table('kategorien', fn (Blueprint $table) => $table->dropColumn(['gruppe', 'groesse_von', 'groesse_bis', 'aktiv']));
    }
};
