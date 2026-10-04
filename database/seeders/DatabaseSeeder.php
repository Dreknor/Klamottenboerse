<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([GrunddatenSeeder::class, MailvorlagenSeeder::class]);

        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
