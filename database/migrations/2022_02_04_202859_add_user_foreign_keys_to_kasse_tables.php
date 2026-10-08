<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Holt die Fremdschlüssel warenkorb/verkaeufe.user_id -> users.id nach.
 *
 * Die Kassen-Tabellen werden in Migrationen von 2016 angelegt, die "users"-
 * Tabelle aber erst am 2022-02-04. Auf einer frischen Datenbank konnte der
 * Fremdschlüssel dort daher nicht angelegt werden. Auf bestehenden
 * Installationen ist er ggf. schon vorhanden – dann passiert hier nichts.
 * Gibt es Datensätze mit nicht (mehr) existierendem Benutzer, wird der
 * Fremdschlüssel übersprungen statt die Migration abzubrechen.
 */
return new class extends Migration
{
    private const TABELLEN = ['warenkorb', 'verkaeufe'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SQLite kann Fremdschlüssel nicht nachträglich per ALTER TABLE anlegen.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TABELLEN as $tabelle) {
            if (! Schema::hasTable($tabelle) || ! Schema::hasTable('users') || $this->hatUserFremdschluessel($tabelle)) {
                continue;
            }

            $verwaist = DB::table($tabelle)
                ->whereNotIn('user_id', DB::table('users')->select('id'))
                ->count();

            if ($verwaist > 0) {
                Log::warning("Fremdschlüssel {$tabelle}.user_id -> users.id nicht angelegt: {$verwaist} Datensätze verweisen auf nicht vorhandene Benutzer.");

                continue;
            }

            Schema::table($tabelle, function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Bewusst leer: auf bestehenden Installationen stammt der Fremdschlüssel
        // ggf. aus der ursprünglichen Migration und soll erhalten bleiben.
    }

    private function hatUserFremdschluessel(string $tabelle): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tabelle)
            ->where('COLUMN_NAME', 'user_id')
            ->where('REFERENCED_TABLE_NAME', 'users')
            ->exists();
    }
};
