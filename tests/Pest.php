<?php

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
