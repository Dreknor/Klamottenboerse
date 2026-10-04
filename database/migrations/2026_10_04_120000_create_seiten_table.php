<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Redaktionelle Seiten der Website (Impressum, Datenschutz …), im Backend bearbeitbar
        Schema::create('seiten', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('titel');
            $table->longText('inhalt');
            $table->foreignId('bearbeitet_von')->nullable()->constrained('personen')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seiten');
    }
};
