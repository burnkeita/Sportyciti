<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FootballSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Seed the reference data every feature test relies on (no demo content). */
    protected function seedSite(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, CategorySeeder::class, FootballSeeder::class, SiteSeeder::class]);
    }

    protected function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
