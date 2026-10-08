<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vom Orga-Team pflegbare Einstellungen für die Verkäufer-Reputation
 * (Vermerk-Arten mit Punkten, Schwellen) und die Angebotskategorien.
 * Wird mit sinnvollen Startwerten befüllt.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('einstellungen', function (Blueprint $table) {
            $table->id();
            $table->string('schluessel')->unique();
            $table->string('wert')->nullable();
            $table->timestamps();
        });

        Schema::create('vermerk_typen', function (Blueprint $table) {
            $table->id();
            $table->string('schluessel', 50)->unique();
            $table->string('label');
            $table->unsignedTinyInteger('punkte')->default(1);
            $table->unsignedInteger('sortierung')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        Schema::create('angebotskategorien', function (Blueprint $table) {
            $table->id();
            $table->string('schluessel', 50)->unique();
            $table->string('label');
            $table->string('gruppe')->default('Weiteres');
            $table->unsignedSmallInteger('groesse_von')->nullable();
            $table->unsignedSmallInteger('groesse_bis')->nullable();
            $table->unsignedInteger('sortierung')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        $jetzt = now();

        DB::table('einstellungen')->insert([
            ['schluessel' => 'reputation_sperre_ab', 'wert' => '5', 'created_at' => $jetzt, 'updated_at' => $jetzt],
            ['schluessel' => 'reputation_warnung_ab', 'wert' => '2', 'created_at' => $jetzt, 'updated_at' => $jetzt],
            ['schluessel' => 'reputation_zeitraum_monate', 'wert' => '24', 'created_at' => $jetzt, 'updated_at' => $jetzt],
        ]);

        $typen = [
            ['kiste_nicht_gebracht', 'Kiste(n) nicht gebracht', 3],
            ['abgabe_verpasst', 'Abgabetermin nicht eingehalten', 2],
            ['abholung_verpasst', 'Abholtermin nicht eingehalten', 2],
            ['defekte_ware', 'Defekte / verschmutzte Ware', 2],
            ['etiketten_fehlerhaft', 'Etiketten fehlen / fehlerhaft', 1],
            ['zu_viele_teile', 'Mehr Teile als erlaubt', 1],
            ['unfreundlich', 'Unangemessenes Verhalten', 2],
            ['sonstiges', 'Sonstiges', 1],
        ];

        foreach ($typen as $i => [$schluessel, $label, $punkte]) {
            DB::table('vermerk_typen')->insert([
                'schluessel' => $schluessel,
                'label' => $label,
                'punkte' => $punkte,
                'sortierung' => ($i + 1) * 10,
                'created_at' => $jetzt,
                'updated_at' => $jetzt,
            ]);
        }

        $kategorien = [
            ['groesse_50_68', 'Kleidung Gr. 50–68', 'Kleidung', 0, 68],
            ['groesse_74_92', 'Kleidung Gr. 74–92', 'Kleidung', 69, 92],
            ['groesse_98_116', 'Kleidung Gr. 98–116', 'Kleidung', 93, 116],
            ['groesse_122_140', 'Kleidung Gr. 122–140', 'Kleidung', 117, 140],
            ['groesse_146_176', 'Kleidung Gr. 146–176', 'Kleidung', 141, 999],
            ['umstandsmode', 'Umstandsmode', 'Kleidung', null, null],
            ['schuhe', 'Schuhe', 'Weiteres', null, null],
            ['spielzeug', 'Spielzeug & Spiele', 'Weiteres', null, null],
            ['buecher', 'Bücher & Medien', 'Weiteres', null, null],
            ['babyausstattung', 'Babyausstattung', 'Weiteres', null, null],
            ['fahrzeuge', 'Kinderwagen, Fahrzeuge & Großteile', 'Weiteres', null, null],
            ['sport', 'Sport & Outdoor', 'Weiteres', null, null],
            ['sonstiges', 'Sonstiges', 'Weiteres', null, null],
        ];

        foreach ($kategorien as $i => [$schluessel, $label, $gruppe, $von, $bis]) {
            DB::table('angebotskategorien')->insert([
                'schluessel' => $schluessel,
                'label' => $label,
                'gruppe' => $gruppe,
                'groesse_von' => $von,
                'groesse_bis' => $bis,
                'sortierung' => ($i + 1) * 10,
                'created_at' => $jetzt,
                'updated_at' => $jetzt,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('angebotskategorien');
        Schema::dropIfExists('vermerk_typen');
        Schema::dropIfExists('einstellungen');
    }
};
