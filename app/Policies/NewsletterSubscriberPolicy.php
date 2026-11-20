<?php

namespace App\Policies;

use App\Support\Permissions;

class NewsletterSubscriberPolicy extends PermissionPolicy
{
    protected string $permission = Permissions::MANAGE_NEWSLETTER;
}
