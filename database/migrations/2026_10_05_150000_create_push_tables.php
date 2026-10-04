<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ein Gerät/Browser, das Push-Nachrichten empfangen möchte
        Schema::create('push_abos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('p256dh');
            $table->string('auth');
            $table->string('geraet')->nullable();
            $table->timestamp('zuletzt_genutzt_at')->nullable();
            $table->timestamps();
        });

        // Warteschlange: wird jede Minute von push:versenden abgearbeitet
        Schema::create('push_nachrichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('personen')->cascadeOnDelete();
            $table->string('titel');
            $table->string('text', 500);
            $table->string('url', 500)->nullable();
            $table->string('status', 20)->default('wartend')->index(); // wartend, versendet, fehler
            $table->timestamp('versendet_at')->nullable();
            $table->timestamps();
        });

        Schema::table('mailvorlagen', function (Blueprint $table) {
            $table->boolean('push')->default(false)->after('inhalt');
        });

        // Erinnerungen und eilige Mails zusätzlich als Push
        DB::table('mailvorlagen')
            ->whereIn('schluessel', ['erinnerung_verkaeufer', 'erinnerung_helfer', 'warteliste_angebot', 'aufgabe_erinnerung', 'nummer_zugeteilt'])
            ->update(['push' => true]);
    }

    public function down(): void
    {
        Schema::table('mailvorlagen', fn (Blueprint $table) => $table->dropColumn('push'));
        Schema::dropIfExists('push_nachrichten');
        Schema::dropIfExists('push_abos');
    }
};
