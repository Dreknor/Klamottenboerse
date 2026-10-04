<?php

namespace Database\Factories;

use App\Enums\BoerseStatus;
use App\Models\Boerse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Boerse> */
class BoerseFactory extends Factory
{
    protected $model = Boerse::class;

    public function definition(): array
    {
        $tag = now()->addDays(40)->startOfDay();

        return [
            'titel' => 'Klamottenbörse '.$tag->format('d.m.Y'),
            'verkaufstag' => $tag->toDateString(),
            'status' => BoerseStatus::Planung,
            'anmeldung_kinderhaus_ab' => $tag->copy()->subDays(35)->setTime(18, 0),
            'anmeldung_ab' => $tag->copy()->subDays(28)->setTime(18, 0),
            'anlieferung_beginn' => $tag->copy()->subDay()->setTime(14, 30),
            'anlieferung_ende' => $tag->copy()->subDay()->setTime(17, 30),
            'verkauf_beginn' => $tag->copy()->setTime(9, 0),
            'verkauf_ende' => $tag->copy()->setTime(12, 0),
            'abholung_beginn' => $tag->copy()->setTime(17, 0),
            'abholung_ende' => $tag->copy()->setTime(18, 30),
            'nummer_von' => 200,
            'nummer_bis' => 599,
            'blockgroesse' => 100,
            'block_toleranz' => 5,
            'kapazitaet' => 240,
            'kinderhaus_nummer' => 600,
            'max_teile' => 60,
            'provision_promille' => 250,
            'rundung_cent' => 10,
            'angebot_stunden' => 48,
        ];
    }

    public function anmeldungOffen(): static
    {
        return $this->state(fn () => [
            'anmeldung_kinderhaus_ab' => now()->subDays(8),
            'anmeldung_ab' => now()->subDay(),
        ]);
    }
}
