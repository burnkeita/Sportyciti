<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            FootballSeeder::class,
            SiteSeeder::class,
        ]);

        $this->createSuperAdmin();

        if (app()->environment('local')) {
            $this->call(DemoContentSeeder::class);
        }
    }

    private function createSuperAdmin(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@sportyciti.com');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = env('SUPER_ADMIN_PASSWORD') ?: Str::password(16);

        $user = User::create([
            'name' => env('SUPER_ADMIN_NAME', 'SportyCiti Admin'),
            'email' => $email,
            'password' => $password,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole(Permissions::SUPER_ADMIN);

        $this->command?->warn("Super Administrator created: {$email}");
        if (! env('SUPER_ADMIN_PASSWORD')) {
            $this->command?->warn("Generated password (change it after first login): {$password}");
        }
    }
}
