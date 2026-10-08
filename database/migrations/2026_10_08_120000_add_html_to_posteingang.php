<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** HTML-Teil eingehender Mails, damit Formatierungen (Absätze, Listen, Tabellen, Links) erhalten bleiben. */
    public function up(): void
    {
        Schema::table('posteingang', function (Blueprint $table) {
            $table->longText('html')->nullable()->after('text');
        });
    }

    public function down(): void
    {
        Schema::table('posteingang', fn (Blueprint $table) => $table->dropColumn('html'));
    }
};
