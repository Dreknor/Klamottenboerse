<?php

use App\Domain\Boersen\Actions\BoerseAnlegen;
use App\Models\Boerse;
use App\Models\Person;
use Database\Factories\BoerseFactory;
use Database\Seeders\GrunddatenSeeder;
use Database\Seeders\MailvorlagenSeeder;
use Database\Seeders\SeitenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed([GrunddatenSeeder::class, MailvorlagenSeeder::class, SeitenSeeder::class]);
    })
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
| Gemeinsame Helfer für die Tests
*/

function neueBoerse(array $werte = []): Boerse
{
    return app(BoerseAnlegen::class)((new BoerseFactory)->anmeldungOffen()->raw($werte));
}

function kassierer(): Person
{
    return tap(Person::factory()->create())->assignRole('kasse');
}

function admin(): Person
{
    return tap(Person::factory()->create(['password' => 'geheim-geheim']))->assignRole('admin');
}

function alsAdmin($test, ?Person $person = null)
{
    return $test->actingAs($person ?? admin())->withSession(['login_art' => 'passwort']);
}
