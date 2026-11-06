<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Initial users from the requirement sample (§3.1). Change these passwords after first login.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Manoj K', 'username' => 'manoj', 'email' => 'manoj@example.com', 'role' => User::ROLE_ADMIN, 'is_active' => true],
            ['name' => 'Priya R', 'username' => 'priya', 'email' => null, 'role' => User::ROLE_STAFF, 'is_active' => true],
        ];

        foreach ($users as $user) {
            if (! User::where('username', $user['username'])->exists()) {
                User::create($user + ['password' => '123456']);
            }
        }
    }
}
