<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reputation der Verkäufer: Vermerke mit Punkten (Kiste nicht gebracht, Termin verpasst, defekte Ware …).
     * Ab einer Punkteschwelle gibt es keine automatische Nummernvergabe mehr – nur noch eine Anfrage.
     */
    public function up(): void
    {
        Schema::create('vermerk_arten', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('punkte')->default(1);
            $table->unsignedSmallInteger('sortierung')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        Schema::create('vermerke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->nullOnDelete();
            $table->foreignId('vermerk_art_id')->nullable()->constrained('vermerk_arten')->nullOnDelete();
            $table->string('art'); // Name zum Zeitpunkt der Erfassung – bleibt lesbar, auch wenn die Art später umbenannt wird
            $table->unsignedTinyInteger('punkte');
            $table->text('bemerkung')->nullable();
            $table->string('quelle', 20)->default('backend'); // backend, kasse, annahme, rueckpacken, ausgabe
            $table->foreignId('erfasst_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->timestamps();

            $table->index(['person_id', 'created_at']);
        });

        Schema::table('personen', function (Blueprint $table) {
            // null = nach Punkten, 'haendisch' = immer nur händische Vergabe, 'frei' = trotz Punkten automatisch
            $table->string('nummernvergabe', 20)->nullable();
        });

        $jetzt = now();
        foreach ([
            ['Kiste(n) nicht gebracht', 3],
            ['Abgabetermin nicht eingehalten', 2],
            ['Abholtermin nicht eingehalten', 2],
            ['Defekte oder verschmutzte Ware', 2],
            ['Etiketten fehlen oder fehlerhaft', 1],
            ['Mehr Teile als erlaubt', 1],
            ['Unangemessenes Verhalten', 2],
            ['Sonstiges', 1],
        ] as $i => [$name, $punkte]) {
            DB::table('vermerk_arten')->insert([
                'name' => $name, 'punkte' => $punkte, 'sortierung' => ($i + 1) * 10, 'aktiv' => true,
                'created_at' => $jetzt, 'updated_at' => $jetzt,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('personen', fn (Blueprint $table) => $table->dropColumn('nummernvergabe'));
        Schema::dropIfExists('vermerke');
        Schema::dropIfExists('vermerk_arten');
    }
};
