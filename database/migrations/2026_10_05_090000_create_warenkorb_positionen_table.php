<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Offener Einkauf an der Kasse – je Konto. Alle Geräte desselben Kontos (Handy, PC) sehen denselben Warenkorb.
        Schema::create('warenkorb_positionen', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->unsignedSmallInteger('nummer');
            $table->unsignedSmallInteger('artikel');
            $table->unsignedInteger('preis_cent');
            $table->timestamps();

            $table->index(['person_id', 'boerse_id']);
        });

        Schema::table('bonpositionen', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('bonpositionen', fn (Blueprint $table) => $table->dropColumn('uuid'));
        Schema::dropIfExists('warenkorb_positionen');
    }
};
