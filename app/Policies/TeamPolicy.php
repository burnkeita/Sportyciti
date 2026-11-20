<?php

namespace App\Policies;

use App\Support\Permissions;

class TeamPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_SPORTS_DATA;
}
