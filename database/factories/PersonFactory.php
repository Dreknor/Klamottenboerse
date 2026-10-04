<?php

namespace Database\Factories;

use App\Enums\KinderhausBezug;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Person> */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'vorname' => fake()->firstName(),
            'nachname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'telefon' => fake()->optional()->phoneNumber(),
            'kinderhaus_bezug' => KinderhausBezug::Keiner,
            'email_verified_at' => now(),
            'info_mails_erlaubt_at' => now(),
            'remember_token' => null,
        ];
    }

    public function kinderhaus(): static
    {
        return $this->state(['kinderhaus_bezug' => KinderhausBezug::Familie]);
    }

    public function ohneMail(): static
    {
        return $this->state(['email' => null, 'email_verified_at' => null, 'info_mails_erlaubt_at' => null]);
    }
}
