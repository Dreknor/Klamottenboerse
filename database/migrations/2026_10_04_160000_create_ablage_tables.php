<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Team-Ablage: Ordner mit Dateien (über medialibrary) – ersetzt die Nextcloud
        Schema::create('ordner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('ordner')->cascadeOnDelete();
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->nullOnDelete();
            $table->string('name');
            $table->boolean('fuer_helfer')->default(false);
            $table->string('herkunft')->nullable(); // z. B. Pfad in der Nextcloud
            $table->timestamps();

            $table->unique(['parent_id', 'name']);
        });

        Schema::create('protokolle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boerse_id')->nullable()->constrained('boersen')->nullOnDelete();
            $table->string('titel');
            $table->date('datum');
            $table->string('teilnehmende')->nullable();
            $table->longText('inhalt')->nullable();
            $table->foreignId('autor_id')->nullable()->constrained('personen')->nullOnDelete();
            $table->string('herkunft')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('datum');
        });

        Schema::table('checklistenvorlagen', function (Blueprint $table) {
            $table->text('beschreibung')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('checklistenvorlagen', fn (Blueprint $table) => $table->dropColumn('beschreibung'));
        Schema::dropIfExists('protokolle');
        Schema::dropIfExists('ordner');
    }
};
