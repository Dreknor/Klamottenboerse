<?php

use Database\Seeders\GrunddatenSeeder;
use Database\Seeders\MailvorlagenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed([GrunddatenSeeder::class, MailvorlagenSeeder::class]);
    })
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');
