<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personen', function (Blueprint $table) {
            $table->dateTime('inaktiv_angeschrieben_at')->nullable()->after('loeschung_angefragt_at');
        });
    }

    public function down(): void
    {
        Schema::table('personen', function (Blueprint $table) {
            $table->dropColumn('inaktiv_angeschrieben_at');
        });
    }
};
