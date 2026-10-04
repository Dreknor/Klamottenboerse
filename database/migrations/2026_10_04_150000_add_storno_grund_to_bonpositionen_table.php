<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bonpositionen', function (Blueprint $table) {
            $table->string('storno_grund')->nullable()->after('storniert_at');
        });
    }

    public function down(): void
    {
        Schema::table('bonpositionen', function (Blueprint $table) {
            $table->dropColumn('storno_grund');
        });
    }
};
