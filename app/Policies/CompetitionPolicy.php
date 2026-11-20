<?php

namespace App\Policies;

use App\Support\Permissions;

class CompetitionPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_SPORTS_DATA;
}
