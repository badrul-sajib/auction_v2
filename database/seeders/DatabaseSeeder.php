<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD');

        // No password configured → generate a strong random one and show it once.
        $generated = false;
        if (blank($password)) {
            $password = Str::password(16);
            $generated = true;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make($password),
            ]
        );

        if ($generated) {
            $this->command?->warn("Admin created: {$email}");
            $this->command?->warn("Generated admin password (shown once): {$password}");
            $this->command?->warn('Set ADMIN_PASSWORD in .env to control this.');
        }
    }
}
