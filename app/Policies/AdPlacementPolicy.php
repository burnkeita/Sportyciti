<?php

namespace App\Policies;

use App\Support\Permissions;

class AdPlacementPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_ADS;
}
