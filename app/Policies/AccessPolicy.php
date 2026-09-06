<?php

namespace App\Policies;

class AccessPolicy
{
    public function accessKasse($user): bool
    {
        if (! $user) {
            return false;
        }

        return method_exists($user, 'canAccessKasse') ? $user->canAccessKasse() : (bool) ($user->kasse ?? false);
    }

    public function accessVerwaltung($user): bool
    {
        if (! $user) {
            return false;
        }

        return method_exists($user, 'canAccessVerwaltung') ? $user->canAccessVerwaltung() : (bool) ($user->verwaltung ?? false);
    }
}
