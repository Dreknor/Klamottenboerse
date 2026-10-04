<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eine dauerhafte Reservierung kann für einzelne Börsen freigegeben werden (z. B. nach Absage).
        Schema::create('nummernreservierung_freigaben', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nummernreservierung_id')->constrained('nummernreservierungen')->cascadeOnDelete();
            $table->foreignId('boerse_id')->constrained('boersen')->cascadeOnDelete();
            $table->string('grund')->nullable();
            $table->timestamps();

            $table->unique(['nummernreservierung_id', 'boerse_id'], 'freigabe_eindeutig');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nummernreservierung_freigaben');
    }
};
