<?php

namespace Database\Seeders;

use App\Support\Demo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([GrunddatenSeeder::class, MailvorlagenSeeder::class, SeitenSeeder::class]);

        if (app()->environment('local') || Demo::aktiv()) {
            $this->call(DemoSeeder::class);
        }
    }
}
