<?php

namespace Tests\Feature;

use App\Model\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    public function test_kasse_gate_requires_kasse_flag(): void
    {
        $allowedUser = new User([
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'kasse' => 1,
            'verwaltung' => 0,
        ]);

        $blockedUser = new User([
            'name' => 'NoCash',
            'email' => 'nocash@example.com',
            'kasse' => 0,
            'verwaltung' => 0,
        ]);

        $this->assertTrue(Gate::forUser($allowedUser)->allows('access-kasse'));
        $this->assertFalse(Gate::forUser($blockedUser)->allows('access-kasse'));
    }

    public function test_verwaltung_gate_requires_verwaltung_flag(): void
    {
        $allowedUser = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'kasse' => 0,
            'verwaltung' => 1,
        ]);

        $blockedUser = new User([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'kasse' => 0,
            'verwaltung' => 0,
        ]);

        $this->assertTrue(Gate::forUser($allowedUser)->allows('access-verwaltung'));
        $this->assertFalse(Gate::forUser($blockedUser)->allows('access-verwaltung'));
    }
}
