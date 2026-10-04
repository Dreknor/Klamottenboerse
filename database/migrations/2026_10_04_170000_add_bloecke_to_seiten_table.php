<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seiten', function (Blueprint $table) {
            // Seiten aus Bausteinen; Impressum/Datenschutz nutzen weiterhin reinen Text (inhalt)
            $table->longText('inhalt')->nullable()->change();
            $table->json('bloecke')->nullable()->after('inhalt');
            $table->json('entwurf')->nullable()->after('bloecke');
            $table->string('beschreibung')->nullable()->after('titel');
            $table->boolean('im_menue')->default(false)->after('entwurf');
            $table->unsignedSmallInteger('menue_reihenfolge')->default(0)->after('im_menue');
            $table->dateTime('veroeffentlicht_at')->nullable()->after('menue_reihenfolge');
        });

        DB::table('seiten')->update(['veroeffentlicht_at' => now()]);

        Schema::create('seiten_versionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seite_id')->constrained('seiten')->cascadeOnDelete();
            $table->string('titel');
            $table->longText('inhalt')->nullable();
            $table->json('bloecke')->nullable();
            $table->foreignId('erstellt_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seiten_versionen');
        Schema::table('seiten', function (Blueprint $table) {
            $table->dropColumn(['bloecke', 'entwurf', 'beschreibung', 'im_menue', 'menue_reihenfolge', 'veroeffentlicht_at']);
        });
    }
};
