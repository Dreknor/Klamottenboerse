<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Zuletzt gewählter Etikettenbogen, damit Verkäufer ihn nicht jedes Mal neu auswählen müssen. */
    public function up(): void
    {
        Schema::table('personen', function (Blueprint $table) {
            $table->string('etikett_vorlage', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('personen', fn (Blueprint $table) => $table->dropColumn('etikett_vorlage'));
    }
};
