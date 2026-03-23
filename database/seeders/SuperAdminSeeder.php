<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Creates a super admin user.
     *
     * Seed credentials (for local/dev):
     * email/username: admin
     * password: admin
     *
     * Issue an API token for admin web:
     * User::where('email', 'admin')->first()->createToken('admin')->plainTextToken
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
                'phone' => null,
                'role' => UserRole::SuperAdmin->value,
                'is_active' => true,
            ]
        );
    }
}
