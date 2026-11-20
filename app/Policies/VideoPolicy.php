<?php

namespace App\Policies;

use App\Support\Permissions;

class VideoPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_MULTIMEDIA;
}
