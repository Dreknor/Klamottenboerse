<?php

use Database\Seeders\GrunddatenSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Feedback-Fragen sind nicht mehr fest eingebaut, sondern werden vom Orga-Team gepflegt.
     * Die drei bisherigen Fragen werden als Standard angelegt und vorhandene Antworten übernommen.
     */
    public function up(): void
    {
        Schema::create('feedback_fragen', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('typ', 20); // sterne, text, auswahl
            $table->json('optionen')->nullable();
            $table->string('rolle', 20)->nullable(); // null = alle, sonst verkaeufer/helfer
            $table->boolean('pflicht')->default(false);
            $table->unsignedSmallInteger('sortierung')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        Schema::create('feedback_antworten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('feedback')->cascadeOnDelete();
            $table->foreignId('feedback_frage_id')->constrained('feedback_fragen')->cascadeOnDelete();
            $table->unsignedTinyInteger('zahl')->nullable();
            $table->text('text')->nullable();
            $table->timestamps();

            $table->unique(['feedback_id', 'feedback_frage_id']);
        });

        $jetzt = now();
        $ids = [];
        foreach (GrunddatenSeeder::FEEDBACK_FRAGEN as $i => [$text, $typ]) {
            $ids[] = DB::table('feedback_fragen')->insertGetId([
                'text' => $text, 'typ' => $typ, 'sortierung' => ($i + 1) * 10, 'aktiv' => true,
                'created_at' => $jetzt, 'updated_at' => $jetzt,
            ]);
        }
        [$bewertung, $gut, $besser] = $ids;

        DB::table('feedback')->whereNotNull('beantwortet_at')->orderBy('id')->each(function ($f) use ($bewertung, $gut, $besser) {
            $zeilen = array_filter([
                $f->bewertung !== null ? ['feedback_frage_id' => $bewertung, 'zahl' => $f->bewertung, 'text' => null] : null,
                filled($f->gut) ? ['feedback_frage_id' => $gut, 'zahl' => null, 'text' => $f->gut] : null,
                filled($f->besser) ? ['feedback_frage_id' => $besser, 'zahl' => null, 'text' => $f->besser] : null,
            ]);
            foreach ($zeilen as $zeile) {
                DB::table('feedback_antworten')->insert($zeile + [
                    'feedback_id' => $f->id, 'created_at' => $f->beantwortet_at, 'updated_at' => $f->beantwortet_at,
                ]);
            }
        });

        Schema::table('feedback', fn (Blueprint $table) => $table->dropColumn(['bewertung', 'gut', 'besser']));
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->unsignedTinyInteger('bewertung')->nullable();
            $table->text('gut')->nullable();
            $table->text('besser')->nullable();
        });

        // Die drei Standardfragen zurückschreiben, alles andere geht verloren
        $standard = DB::table('feedback_fragen')->orderBy('id')->limit(3)->pluck('id')->all();
        foreach (array_combine(['bewertung', 'gut', 'besser'], array_pad($standard, 3, 0)) as $spalte => $frageId) {
            DB::table('feedback_antworten')->where('feedback_frage_id', $frageId)->orderBy('id')->each(function ($a) use ($spalte) {
                DB::table('feedback')->where('id', $a->feedback_id)->update([$spalte => $spalte === 'bewertung' ? $a->zahl : $a->text]);
            });
        }

        Schema::dropIfExists('feedback_antworten');
        Schema::dropIfExists('feedback_fragen');
    }
};
