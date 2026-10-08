<?php

namespace App\Support;

use App\Models\Kategorie;
use App\Models\Seite;
use Database\Seeders\GrunddatenSeeder;
use Database\Seeders\MailvorlagenSeeder;
use Database\Seeders\SeitenSeeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Sorgt dafür, dass Rollen, Standard-Mailvorlagen und eine Grundausstattung vorhanden sind –
 * egal ob per install.sh, von Hand oder per Online-Update installiert. Läuft nach jedem „migrate“.
 * Was das Team gelöscht hat (Kategorien, Seiten), kommt nicht ungefragt zurück:
 * das wird nur in einer leeren Installation angelegt.
 */
class Grunddaten
{
    public static function sicherstellen(): void
    {
        if (! Schema::hasTable('mailvorlagen') || ! Schema::hasTable('roles') || ! Schema::hasTable('seiten')) {
            return; // Migrationen noch nicht vollständig
        }

        try {
            // Rollen und Standardvorlagen braucht das System immer (beides lässt sich nicht löschen)
            foreach (array_keys(GrunddatenSeeder::ROLLEN) as $rolle) {
                Role::findOrCreate($rolle, 'web');
            }
            (new MailvorlagenSeeder)->run();

            if (Kategorie::query()->doesntExist()) {
                (new GrunddatenSeeder)->run();
            }
            if (Seite::query()->doesntExist()) {
                (new SeitenSeeder)->run();
            }
        } catch (Throwable $e) {
            report($e); // ein Problem hier darf Update oder Installation nicht abbrechen
        }
    }
}
