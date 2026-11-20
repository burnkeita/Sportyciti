<?php

namespace App\Policies;

use App\Support\Permissions;

class HomepageSectionPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_HOMEPAGE;
}
