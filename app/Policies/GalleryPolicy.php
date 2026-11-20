<?php

namespace App\Policies;

use App\Support\Permissions;

class GalleryPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_MULTIMEDIA;
}
