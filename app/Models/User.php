<?php

namespace App\Models;

use App\Model\User as LegacyUser;

class User extends LegacyUser
{
    public const ACCESS_KASSE = 'access-kasse';
    public const ACCESS_VERWALTUNG = 'access-verwaltung';
}
