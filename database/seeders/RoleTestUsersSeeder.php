<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Local-only users, one per role, for testing role access. Do not run in production.
 * Login with <role>@role-test.test and the password below.
 */
class RoleTestUsersSeeder extends Seeder
{
    private const PASSWORD = 'RoleTest-local-2026';

    public function run(): void
    {
        foreach (array_keys(User::USER_ROLES) as $role) {
            User::updateOrCreate(
                ['email' => "{$role}@role-test.test"],
                [
                    'name' => 'Test ' . $role,
                    'password' => Hash::make(self::PASSWORD),
                    'role' => $role,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
